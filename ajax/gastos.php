<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/database.php';

if (ob_get_length()) ob_clean();

$usuario = $_SESSION['id'] ?? 0;
$rol = $_SESSION['rol'] ?? 'user';

function limpiarMonto($valor) {
    if (empty($valor)) return 0;
    $limpio = str_replace('.', '', $valor);
    $limpio = str_replace(',', '.', $limpio);
    return (float)$limpio;
}

/* =========================
   LISTAR
========================= */
if($_GET['accion']=='listar'){
    header('Content-Type: application/json');
    
    if (strcasecmp($rol, 'admin') === 0 || strcasecmp($rol, 'contador') === 0) {
        $where = "WHERE 1=1";
    } else {
        $where = "WHERE g.usuario_id=$usuario";
    }

    if (!empty($_GET['f_desde']))     $where .= " AND g.fecha >= '".$_GET['f_desde']."'";
    if (!empty($_GET['f_hasta']))     $where .= " AND g.fecha <= '".$_GET['f_hasta']."'";
    if (!empty($_GET['f_centro']))    $where .= " AND g.centro_costo_id = '".$_GET['f_centro']."'";
    if (!empty($_GET['f_categoria'])) $where .= " AND g.categoria_id = '".$_GET['f_categoria']."'";
    if (!empty($_GET['f_obra']))      $where .= " AND g.obra_id = '".$_GET['f_obra']."'";

    $sql = "SELECT g.*, t.nombre AS tipo_comprobante, m.nombre AS medio_pago, o.nombre AS obra, 
            c.nombre AS centro, cat.nombre AS categoria, sub.nombre AS subcategoria, p.nombre AS proveedor,
            IFNULL(u.usuario, 'Sistema') AS usuario_nombre
            FROM gastos g
            LEFT JOIN tipos_comprobante t ON t.id = g.tipo_comprobante_id
            LEFT JOIN medios_pago m ON m.id = g.medio_pago_id
            LEFT JOIN obras o ON o.id = g.obra_id
            LEFT JOIN centros_costos c ON c.id = g.centro_costo_id
            LEFT JOIN categorias cat ON cat.id = g.categoria_id
            LEFT JOIN subcategorias sub ON sub.id = g.subcategoria_id
            LEFT JOIN proveedores p ON p.id = g.proveedor_id
            LEFT JOIN usuarios u ON u.id = g.usuario_id
            $where ORDER BY g.id DESC";

    $r = $conn->query($sql);
    $data=[];
    if($r) while($row=$r->fetch_assoc()) $data[]=$row;

    echo json_encode(["data"=>$data]);
    exit;
}

/* =========================
   OBTENER UN GASTO
========================= */
if($_GET['accion']=='obtener'){
    header('Content-Type: application/json');
    $id = (int)$_GET['id'];

    $sql = "SELECT g.*, mc.caja_id, t.nombre AS tipo_comprobante, m.nombre AS medio_pago,
                   o.nombre AS obra, c.nombre AS centro, cat.nombre AS categoria, 
                   sub.nombre AS subcategoria, p.nombre AS proveedor,
                   IFNULL(u.usuario, 'Sistema') AS usuario_nombre
            FROM gastos g
            LEFT JOIN movimientos_caja mc ON mc.origen='GASTO' AND mc.referencia_id=g.id
            LEFT JOIN tipos_comprobante t ON t.id = g.tipo_comprobante_id
            LEFT JOIN medios_pago m ON m.id = g.medio_pago_id
            LEFT JOIN obras o ON o.id = g.obra_id
            LEFT JOIN centros_costos c ON c.id = g.centro_costo_id
            LEFT JOIN categorias cat ON cat.id = g.categoria_id
            LEFT JOIN subcategorias sub ON sub.id = g.subcategoria_id
            LEFT JOIN proveedores p ON p.id = g.proveedor_id
            LEFT JOIN usuarios u ON u.id = g.usuario_id
            WHERE g.id = $id";

    if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
        $sql .= " AND g.usuario_id = $usuario";
    }

    $r = $conn->query($sql);
    if($r && $r->num_rows){
        echo json_encode($r->fetch_assoc());
    }else{
        echo json_encode([]);
    }
    exit;
}

/* =========================
   GUARDAR (INSERT / UPDATE)
========================= */
if($_GET['accion']=='guardar'){
    $id = $_POST['id'] ?? '';
    $fecha = $_POST['fecha'];
    $detalle = $conn->real_escape_string($_POST['detalle']);
    
    $total          = limpiarMonto($_POST['total']);
    $neto           = limpiarMonto($_POST['neto']);
    $iva            = limpiarMonto($_POST['iva']);
    $ret_iibb       = limpiarMonto($_POST['ret_iibb']);
    $otros_tributos = limpiarMonto($_POST['otros_tributos']);

    $tipo_comprobante_id = !empty($_POST['tipo_comprobante_id']) ? $_POST['tipo_comprobante_id'] : "NULL";
    $medio_pago_id       = !empty($_POST['medio_pago_id']) ? $_POST['medio_pago_id'] : "NULL";
    $caja_id             = !empty($_POST['caja_id']) ? $_POST['caja_id'] : "NULL";
    $obra_id             = !empty($_POST['obra_id']) ? $_POST['obra_id'] : "NULL";
    $centro_costo_id     = !empty($_POST['centro_costo_id']) ? $_POST['centro_costo_id'] : "NULL";
    $categoria_id        = !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : "NULL";
    $subcategoria_id     = !empty($_POST['subcategoria_id']) ? $_POST['subcategoria_id'] : "NULL";
    $proveedor_id        = !empty($_POST['proveedor_id']) ? $_POST['proveedor_id'] : "NULL";
    
    $numero_comprobante = $_POST['numero_comprobante'] ?? '';

    // Lógica de Validación > $800.000
    $estado_val = 'APROBADO';
    if ($total >= 800000 && strcasecmp($rol, 'admin') !== 0) {
        $estado_val = 'PENDIENTE';
    }

    // --- PROCESAR ARCHIVO ---
    $archivo_nombre = null;
    if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','pdf'];

        if(in_array($ext, $permitidos)){
            $nombre = time().'_'.rand(1000,9999).'.'.$ext;
            $ruta_folder = $_SERVER['DOCUMENT_ROOT'].'/contable/uploads/gastos/';
            $ruta_final = $ruta_folder . $nombre;

            if(!empty($id)){
                $res_old = $conn->query("SELECT archivo FROM gastos WHERE id=$id");
                $row_old = $res_old->fetch_assoc();
                if($row_old && !empty($row_old['archivo'])){
                    $old_file = $ruta_folder . $row_old['archivo'];
                    if(file_exists($old_file)) unlink($old_file);
                }
            }

            if(move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_final)){
                $archivo_nombre = $nombre;
            }
        }
    }

    if($id){
        // UPDATE
        $sql = "UPDATE gastos SET 
                fecha='$fecha', detalle='$detalle', total='$total', 
                tipo_comprobante_id=$tipo_comprobante_id, numero_comprobante='$numero_comprobante',
                neto='$neto', iva='$iva', ret_iibb='$ret_iibb', otros_tributos='$otros_tributos',
                caja_id=$caja_id, centro_costo_id=$centro_costo_id, categoria_id=$categoria_id, 
                subcategoria_id=$subcategoria_id, proveedor_id=$proveedor_id,
                medio_pago_id=$medio_pago_id, obra_id=$obra_id";
        
        if (strcasecmp($rol, 'admin') !== 0 && $total >= 800000) {
            $sql .= ", estado_validacion='PENDIENTE'";
        }
        if($archivo_nombre) $sql .= ", archivo='$archivo_nombre'";
        $sql .= " WHERE id=$id";
    } else {
        // INSERT
        $sql = "INSERT INTO gastos (fecha, detalle, total, tipo_comprobante_id, numero_comprobante, 
                neto, iva, ret_iibb, otros_tributos, caja_id, centro_costo_id, categoria_id, subcategoria_id, 
                proveedor_id, medio_pago_id, obra_id, archivo, usuario_id, estado_validacion) 
                VALUES ('$fecha', '$detalle', '$total', $tipo_comprobante_id, '$numero_comprobante', 
                '$neto', '$iva', '$ret_iibb', '$otros_tributos', $caja_id, $centro_costo_id, $categoria_id, 
                $subcategoria_id, $proveedor_id, $medio_pago_id, $obra_id, 
                ".($archivo_nombre ? "'$archivo_nombre'" : "NULL").", $usuario, '$estado_val')";
    }

    $ok = $conn->query($sql);

    if($ok){
        if($id){
            if($caja_id != "NULL"){
                $concepto = "GASTO #".$id;
                $conn->query("UPDATE movimientos_caja SET
                                fecha='$fecha',
                                caja_id=$caja_id,
                                concepto='$concepto',
                                comprobante='$numero_comprobante',
                                importe='$total'
                              WHERE origen='GASTO' AND referencia_id=$id");
            }
        }else{
            if($caja_id != "NULL"){
                $gasto_id = $conn->insert_id;
                $concepto = "GASTO #".$gasto_id;

                $conn->query("INSERT INTO movimientos_caja(
                                fecha, caja_id, tipo, concepto, comprobante, importe, origen, referencia_id, usuario_id
                              ) VALUES(
                                '$fecha', $caja_id, 'EGRESO', '$concepto', '$numero_comprobante', '$total', 'GASTO', $gasto_id, $usuario
                              )");
            }
        }
        echo json_encode(["status" => "OK", "estado_validacion" => $estado_val]);
    }else{
        echo json_encode(["status" => "ERROR", "message" => $conn->error]);
    }
    exit;
}

/* =========================
   APROBAR GASTO (SOLO ADMIN)
========================= */
if($_GET['accion']=='aprobar'){
    header('Content-Type: application/json');
    if (strcasecmp($rol, 'admin') !== 0) {
        echo json_encode(["status" => "ERROR", "message" => "No posee permisos de administrador."]);
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if($conn->query("UPDATE gastos SET estado_validacion='APROBADO' WHERE id=$id")){
        echo json_encode(["status" => "OK"]);
    }else{
        echo json_encode(["status" => "ERROR", "message" => $conn->error]);
    }
    exit;
}

/* =========================
   ELIMINAR ARCHIVO
========================= */
if($_GET['accion']=='eliminar_archivo'){
    $id = $_POST['id'];
    $res = $conn->query("SELECT archivo FROM gastos WHERE id=$id");
    $row = $res->fetch_assoc();

    if($row && $row['archivo']){
        $ruta = $_SERVER['DOCUMENT_ROOT'].'/contable/uploads/gastos/'.$row['archivo'];
        if(file_exists($ruta)) unlink($ruta);
        $conn->query("UPDATE gastos SET archivo=NULL WHERE id=$id");
    }
    echo "OK";
    exit;
}

/* =========================
   ELIMINAR GASTO COMPLETO
========================= */
if($_GET['accion']=='eliminar'){
    $id = (int)($_POST['id'] ?? 0);

    $res = $conn->query("SELECT archivo FROM gastos WHERE id = $id");
    $row = $res->fetch_assoc();

    if($row && !empty($row['archivo'])){
        $ruta = $_SERVER['DOCUMENT_ROOT'].'/contable/uploads/gastos/'.$row['archivo'];
        if(file_exists($ruta)) unlink($ruta);
    }

    $conn->query("DELETE FROM movimientos_caja WHERE origen='GASTO' AND referencia_id=$id");

    if($conn->query("DELETE FROM gastos WHERE id=$id")){
        echo "OK";
    }else{
        echo "ERROR: ".$conn->error;
    }
    exit;
}