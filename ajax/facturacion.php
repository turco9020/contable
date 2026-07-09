<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// =======================
// LISTAR FACTURAS
// =======================
if($accion == 'listar'){
    // Consulta limpia adaptada a las columnas reales de tu base de datos
    $sql = "
        SELECT 
            f.id,
            f.fecha, 
            f.punto_venta,
            f.nro_factura,
            f.fecha_vencimiento,
            f.detalle,
            f.neto,
            f.iva,
            f.total,
            f.observaciones,
            f.archivo,
            f.cliente_id,
            f.tipo_comprobante_id,
            f.centro_costo_id,
            IFNULL(c.nombre, 'SIN CLIENTE') AS cliente_nombre,
            IFNULL(tc.nombre, 'SIN TIPO') AS tipo_comprobante_nombre,
            IFNULL(cc.nombre, 'SIN CENTRO') AS centro_costo_nombre
        FROM facturas_venta f
        LEFT JOIN clientes c ON c.id = f.cliente_id
        LEFT JOIN tipos_comprobante tc ON tc.id = f.tipo_comprobante_id
        LEFT JOIN centros_costos cc ON cc.id = f.centro_costo_id
        ORDER BY f.fecha DESC, f.id DESC
    ";
    
    $res = $conn->query($sql);
    
    // Si la consulta llega a fallar por otra cosa, esto te lo avisa en limpio
    if(!$res){
        echo json_encode(["error" => "Error SQL: " . $conn->error]);
        exit;
    }
    
    $data = [];
    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }
    
    echo json_encode(['data' => $data]);
    exit;
}
// =======================
// GUARDAR / EDITAR
// =======================
if($accion == 'guardar'){
    $id = $_POST['id'] ?? '';
    $fecha = $_POST['fecha'];
    $tipo_comprobante_id = (int)$_POST['tipo_comprobante_id'];
    $cliente_id = (int)$_POST['cliente_id'];
    $punto_venta = (int)$_POST['punto_venta'];
    $nro_factura = (int)$_POST['nro_factura'];
    $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? "'".$_POST['fecha_vencimiento']."'" : "NULL";
    $detalle = $conn->real_escape_string(trim($_POST['detalle']));
    $neto = (float)$_POST['neto'];
    $iva = (float)$_POST['iva'];
    $total = (float)$_POST['total'];
    $observaciones = $conn->real_escape_string(trim($_POST['observaciones']));
    $centro_costo_id = (int)$_POST['centro_costo_id'];
    
    // Procesar archivo
    $archivo_nombre = null;
    if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0){
        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $archivo_nombre = 'FAC_VTA_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $ruta_destino = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/facturacion/';
        
        if (!file_exists($ruta_destino)) {
            mkdir($ruta_destino, 0777, true);
        }
        
        move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino . $archivo_nombre);
    }

    if($id){
        // EDITAR
        $sql = "UPDATE facturas_venta SET 
                    fecha='$fecha', 
                    tipo_comprobante_id=$tipo_comprobante_id, 
                    cliente_id=$cliente_id, 
                    punto_venta=$punto_venta, 
                    nro_factura=$nro_factura, 
                    fecha_vencimiento=$fecha_vencimiento,
                    detalle='$detalle',
                    neto=$neto, 
                    iva=$iva, 
                    total=$total, 
                    observaciones='$observaciones',
                    centro_costo_id=$centro_costo_id";
        
        if($archivo_nombre){
            $sql .= ", archivo='$archivo_nombre'";
        }
        $sql .= " WHERE id=$id";
    } else {
        // NUEVO
        $sql = "INSERT INTO facturas_venta (fecha, tipo_comprobante_id, cliente_id, punto_venta, nro_factura, fecha_vencimiento, detalle, neto, iva, total, observaciones, centro_costo_id, archivo) 
                VALUES ('$fecha', $tipo_comprobante_id, $cliente_id, $punto_venta, $nro_factura, $fecha_vencimiento, '$detalle', $neto, $iva, $total, '$observaciones', $centro_costo_id, " . ($archivo_nombre ? "'$archivo_nombre'" : "NULL") . ")";
    }
    
    if($conn->query($sql)){
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => $conn->error]);
    }
    exit;
}

// =======================
// ELIMINAR
// =======================
if($accion == 'eliminar'){
    $id = (int)$_POST['id'];
    
    $res = $conn->query("SELECT archivo FROM facturas_venta WHERE id=$id");
    if($row = $res->fetch_assoc()){
        if(!empty($row['archivo'])){
            $path = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/facturacion/' . $row['archivo'];
            if(file_exists($path)) @unlink($path);
        }
    }
    
    $conn->query("DELETE FROM facturas_venta WHERE id=$id");
    echo json_encode(['status' => 'OK']);
    exit;
}