<?php
// Arrancamos la sesión de forma segura para poder verificar los roles e IDs de usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// CAPTURAMOS LOS DATOS DE LA SESIÓN ACTUAL
$id_usuario_sesion = $_SESSION['id'] ?? null;
$rol_sesion = $_SESSION['rol'] ?? '';

// =======================
// SALDOS
// =======================
if($accion == 'saldos'){
    header('Content-Type: application/json');

    // Restricción base por rol: Admin/Contador ven todo, Operador ve solo su caja asignada
    $whereCaja = "WHERE c.activa = 1";
    if (strcasecmp($rol_sesion, 'admin') !== 0 && strcasecmp($rol_sesion, 'contador') !== 0) {
        $whereCaja .= " AND c.usuario_id = $id_usuario_sesion";
    }

    $sql = "
        SELECT
            c.id,
            c.nombre,
            IFNULL(
                SUM(
                    CASE
                        WHEN m.tipo='INGRESO' THEN m.importe
                        WHEN m.tipo='TRANSFERENCIA' THEN m.importe
                        ELSE -m.importe
                    END
                )
            ,0) saldo
        FROM cajas c
        LEFT JOIN movimientos_caja m ON m.caja_id = c.id
        $whereCaja
        GROUP BY c.id 
        ORDER BY c.nombre
    ";

    $res = $conn->query($sql);
    $data = [];
    if ($res) {
        while($row = $res->fetch_assoc()){
            $data[] = $row;
        }
    }

    echo json_encode($data);
    exit;
}


// =======================
// LISTAR MOVIMIENTOS
// =======================
if($accion == 'listar'){
    header('Content-Type: application/json');

    $sql = "
        SELECT
            m.*,
            c.nombre caja,
            g.archivo AS gasto_archivo
        FROM movimientos_caja m
        LEFT JOIN cajas c ON c.id = m.caja_id
        LEFT JOIN gastos g ON g.id = m.referencia_id AND m.origen = 'GASTO'
        WHERE 1=1
    ";

    // SEGURIDAD POR CAJA ASIGNADA: Si NO es Admin ni Contador, solo ve movimientos vinculados a su caja
    if (strcasecmp($rol_sesion, 'admin') !== 0 && strcasecmp($rol_sesion, 'contador') !== 0) {
        $sql .= " AND c.usuario_id = $id_usuario_sesion";
    }

    $sql .= " ORDER BY m.fecha DESC, m.id DESC";

    $res = $conn->query($sql);
    $data = [];
    if ($res) {
        while($row = $res->fetch_assoc()){
            $data[] = $row;
        }
    }

    echo json_encode([
        'data' => $data
    ]);
    exit;
}


// =======================
// GUARDAR
// =======================
if($accion == 'guardar'){

    $id = $_POST['id'] ?? '';
    
    // ---------------------------------------------------------------------
    // VALIDACIÓN DE SEGURIDAD (EDICIÓN)
    // Si no es Admin/Contador, no puede editar movimientos que no sean MANUALES o ajenos.
    // ---------------------------------------------------------------------
    if(!empty($id)){
        if (strcasecmp($rol_sesion, 'admin') !== 0 && strcasecmp($rol_sesion, 'contador') !== 0) {
            $check_res = $conn->query("SELECT usuario_id, origen FROM movimientos_caja WHERE id = " . (int)$id);
            if ($check_res && $check_res->num_rows > 0) {
                $mov = $check_res->fetch_assoc();
                if ($mov['origen'] !== 'MANUAL' || $mov['usuario_id'] != $id_usuario_sesion) {
                    header('HTTP/1.1 403 Forbidden');
                    echo "ERROR: No tenés permisos para modificar este movimiento.";
                    exit;
                }
            }
        }
    }
    // ---------------------------------------------------------------------

    $fecha = $_POST['fecha'];
    $caja_id = (int)$_POST['caja_id'];
    $tipo = $_POST['tipo'];
    $concepto = $conn->real_escape_string(trim($_POST['concepto']));
    $comprobante = $conn->real_escape_string(trim($_POST['comprobante'] ?? ''));
    $importe = (float)$_POST['importe'];
    $observaciones = $conn->real_escape_string(trim($_POST['observaciones'] ?? ''));
    $origen = $_POST['origen'] ?? 'MANUAL';
    
    $referencia_id = !empty($_POST['referencia_id']) ? (int)$_POST['referencia_id'] : "NULL";
    $archivo_nombre = null;

    // =======================
    // ARCHIVO
    // =======================
    if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $permitidos = ['pdf', 'jpg', 'jpeg', 'png'];

        if(in_array($ext, $permitidos)){
            $nombre = time().'_'.rand(1000,9999).'.'.$ext;
            $ruta = $_SERVER['DOCUMENT_ROOT'].'/contable/uploads/caja/'.$nombre;

            // REEMPLAZAR ARCHIVO
            if($id){
                $res = $conn->query("SELECT archivo FROM movimientos_caja WHERE id = $id");
                $old = $res->fetch_assoc();
                if($old && !empty($old['archivo'])){
                    $rutaVieja = $_SERVER['DOCUMENT_ROOT'].'/contable/uploads/caja/'.$old['archivo'];
                    if(file_exists($rutaVieja)){
                        unlink($rutaVieja);
                    }
                }
            }

            if(move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta)){
                $archivo_nombre = $nombre;
            }
        }
    }

    // =======================
    // UPDATE
    // =======================
    if($id){
        $sql = "
            UPDATE movimientos_caja SET
                fecha = '$fecha',
                caja_id = $caja_id,
                tipo = '$tipo',
                concepto = '$concepto',
                comprobante = '$comprobante',
                importe = '$importe',
                observaciones = '$observaciones',
                origen = '$origen',
                referencia_id = $referencia_id,
                updated_at = NOW()
        ";

        if($archivo_nombre){
            $sql .= ", archivo = '$archivo_nombre'";
        }

        $sql .= " WHERE id = $id";

    } else {
        // =======================
        // INSERT
        // =======================
        $sql = "
            INSERT INTO movimientos_caja(
                fecha,
                caja_id,
                tipo,
                concepto,
                comprobante,
                archivo,
                importe,
                observaciones,
                origen,
                referencia_id,
                usuario_id
            ) VALUES (
                '$fecha',
                $caja_id,
                '$tipo',
                '$concepto',
                '$comprobante',
                ".($archivo_nombre ? "'$archivo_nombre'" : "NULL").",
                '$importe',
                '$observaciones',
                '$origen',
                $referencia_id,
                '$id_usuario_sesion'
            )
        ";
    }

    $ok = $conn->query($sql);
    if(!$ok){
        echo "ERROR: ".$conn->error;
    }else{
        echo "OK";
    }
    exit;
}


// =======================
// ELIMINAR
// =======================
if($accion == 'eliminar'){
    $id = (int)$_POST['id'];

    // ---------------------------------------------------------------------
    // VALIDACIÓN DE SEGURIDAD (ELIMINAR)
    // Si no es Admin/Contador, no puede eliminar movimientos que no sean MANUALES o ajenos.
    // ---------------------------------------------------------------------
    if (strcasecmp($rol_sesion, 'admin') !== 0 && strcasecmp($rol_sesion, 'contador') !== 0) {
        $check_res = $conn->query("SELECT usuario_id, origen FROM movimientos_caja WHERE id = $id");
        if ($check_res && $check_res->num_rows > 0) {
            $mov = $check_res->fetch_assoc();
            if ($mov['origen'] !== 'MANUAL' || $mov['usuario_id'] != $id_usuario_sesion) {
                header('HTTP/1.1 403 Forbidden');
                echo "ERROR: No tenés permisos para eliminar este movimiento.";
                exit;
            }
        }
    }
    // ---------------------------------------------------------------------

    $res = $conn->query("SELECT archivo FROM movimientos_caja WHERE id = $id");
    $row = $res->fetch_assoc();

    if($row && !empty($row['archivo'])){
        $ruta = $_SERVER['DOCUMENT_ROOT'].'/contable/uploads/caja/'.$row['archivo'];
        if(file_exists($ruta)){
            unlink($ruta);
        }
    }

    $conn->query("DELETE FROM movimientos_caja WHERE id = $id");
    echo "OK";
    exit;
}