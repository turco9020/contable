<?php
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
            g.archivo AS gasto_archivo,
            u.usuario AS usuario_nombre
        FROM movimientos_caja m
        LEFT JOIN cajas c ON c.id = m.caja_id
        LEFT JOIN gastos g ON g.id = m.referencia_id AND m.origen = 'GASTO'
        LEFT JOIN usuarios u ON u.id = m.usuario_id
        WHERE 1=1
    ";

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
    $tipo = $_POST['tipo'];

    // ---------------------------------------------------------------------
    // LOGICA ESPECIAL PARA TRANSFERENCIAS NUEVAS
    // Genera automáticamente los dos asientos en simultáneo con transacción
    // ---------------------------------------------------------------------
    if($tipo === 'TRANSFERENCIA' && empty($id)){
        $caja_origen_id = (int)$_POST['caja_origen_id'];
        $caja_destino_id = (int)$_POST['caja_destino_id'];

        if($caja_origen_id === $caja_destino_id){
            header('HTTP/1.1 400 Bad Request');
            echo "La caja de origen y la caja de destino no pueden ser la misma.";
            exit;
        }

        $fecha = $_POST['fecha'];
        $concepto_base = $conn->real_escape_string(trim($_POST['concepto']));
        $comprobante = $conn->real_escape_string(trim($_POST['comprobante'] ?? ''));
        $importe = (float)$_POST['importe'];
        $observaciones = $conn->real_escape_string(trim($_POST['observaciones'] ?? ''));

        // Obtener nombres de las cajas
        $resOrigen = $conn->query("SELECT nombre FROM cajas WHERE id = $caja_origen_id");
        $resDestino = $conn->query("SELECT nombre FROM cajas WHERE id = $caja_destino_id");
        $nombreOrigen = $resOrigen ? $resOrigen->fetch_assoc()['nombre'] : '';
        $nombreDestino = $resDestino ? $resDestino->fetch_assoc()['nombre'] : '';

        // Subir archivo adjunto si viene alguno
        $archivo_nombre = null;
        if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0){
            $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
            if(in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])){
                $nombre = time().'_'.rand(1000,9999).'.'.$ext;
                $ruta = $_SERVER['DOCUMENT_ROOT'].'/contable/uploads/caja/'.$nombre;
                if(move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta)){
                    $archivo_nombre = $nombre;
                }
            }
        }

        $conn->begin_transaction();

        try {
            // 1. Asiento de EGRESO
            $conceptoEgreso = "Transferencia enviada a $nombreDestino - " . $concepto_base;
            $sqlEgreso = "
                INSERT INTO movimientos_caja(
                    fecha, caja_id, tipo, concepto, comprobante, archivo, 
                    importe, observaciones, origen, usuario_id
                ) VALUES (
                    '$fecha', $caja_origen_id, 'EGRESO', '$conceptoEgreso', '$comprobante', 
                    ".($archivo_nombre ? "'$archivo_nombre'" : "NULL").",
                    '$importe', '$observaciones', 'TRANSFERENCIA', '$id_usuario_sesion'
                )
            ";
            if(!$conn->query($sqlEgreso)){
                throw new Exception($conn->error);
            }

            // 2. Asiento de INGRESO
            $conceptoIngreso = "Transferencia recibida de $nombreOrigen - " . $concepto_base;
            $sqlIngreso = "
                INSERT INTO movimientos_caja(
                    fecha, caja_id, tipo, concepto, comprobante, archivo, 
                    importe, observaciones, origen, usuario_id
                ) VALUES (
                    '$fecha', $caja_destino_id, 'INGRESO', '$conceptoIngreso', '$comprobante', 
                    ".($archivo_nombre ? "'$archivo_nombre'" : "NULL").",
                    '$importe', '$observaciones', 'TRANSFERENCIA', '$id_usuario_sesion'
                )
            ";
            if(!$conn->query($sqlIngreso)){
                throw new Exception($conn->error);
            }

            $conn->commit();
            echo "OK";
        } catch (Exception $e) {
            $conn->rollback();
            echo "ERROR: " . $e->getMessage();
        }
        exit;
    }

    // ---------------------------------------------------------------------
    // VALIDACIÓN DE SEGURIDAD PARA EDICIÓN DE MOVIMIENTO INDIVIDUAL
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

    $fecha = $_POST['fecha'];
    $caja_id = (int)$_POST['caja_id'];
    $concepto = $conn->real_escape_string(trim($_POST['concepto']));
    $comprobante = $conn->real_escape_string(trim($_POST['comprobante'] ?? ''));
    $importe = (float)$_POST['importe'];
    $observaciones = $conn->real_escape_string(trim($_POST['observaciones'] ?? ''));
    $origen = $_POST['origen'] ?? 'MANUAL';
    
    $referencia_id = !empty($_POST['referencia_id']) ? (int)$_POST['referencia_id'] : "NULL";
    $archivo_nombre = null;

    // Subida / Reemplazo de archivo para guardado individual
    if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $permitidos = ['pdf', 'jpg', 'jpeg', 'png'];

        if(in_array($ext, $permitidos)){
            $nombre = time().'_'.rand(1000,9999).'.'.$ext;
            $ruta = $_SERVER['DOCUMENT_ROOT'].'/contable/uploads/caja/'.$nombre;

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

    if($id){
        // UPDATE INDIVIDUAL
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
        // INSERT INDIVIDUAL
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