<?php
// Iniciamos la sesión para poder auditar el usuario y validar roles
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// Captura de datos de sesión actuales
$usuario = $_SESSION['id'] ?? 0;
$rol = $_SESSION['rol'] ?? 'user';

// =======================
// LISTAR FACTURAS
// =======================
if($accion == 'listar'){
    header('Content-Type: application/json');

    // Construcción de la condición base según rol
    $where = "WHERE 1=1";
    if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
        $where .= " AND f.usuario_id = $usuario";
    }

    // Consulta limpia adaptada con f.obra_id incorporado
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
            f.obra_id,
            f.estado,
            f.usuario_id,
            IFNULL(u.usuario, 'Sistema') AS usuario_nombre,
            IFNULL(c.nombre, 'SIN CLIENTE') AS cliente_nombre,
            IFNULL(tc.nombre, 'SIN TIPO') AS tipo_comprobante_nombre,
            IFNULL(cc.nombre, 'SIN CENTRO') AS centro_costo_nombre
        FROM facturas_venta f
        LEFT JOIN clientes c ON c.id = f.cliente_id
        LEFT JOIN tipos_comprobante tc ON tc.id = f.tipo_comprobante_id
        LEFT JOIN centros_costos cc ON cc.id = f.centro_costo_id
        LEFT JOIN usuarios u ON u.id = f.usuario_id 
        $where
        ORDER BY f.fecha DESC, f.id DESC
    ";
    
    $res = $conn->query($sql);
    
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
// GUARDAR / EDITAR FACTURA
// =======================
if($accion == 'guardar'){
    header('Content-Type: application/json');

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
    $estado = $conn->real_escape_string(trim($_POST['estado'] ?? 'DEBE'));
    
    // CAPTURA Y PROCESAMIENTO DEL CAMPO NUEVO (OBRA)
    $obra_id = !empty($_POST['obra_id']) ? (int)$_POST['obra_id'] : "NULL";
    
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
                    centro_costo_id=$centro_costo_id,
                    obra_id=$obra_id,
                    estado='$estado'";

        if($archivo_nombre){
            $sql .= ", archivo='$archivo_nombre'";
        }
        
        $sql .= " WHERE id=$id";
        
        // 🛡️ SEGURIDAD EDITAR: Si no es admin ni contador, sólo puede editar sus propias facturas
        if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
            $sql .= " AND usuario_id = $usuario";
        }
        
    } else {
        // NUEVO 
        $sql = "INSERT INTO facturas_venta (fecha, tipo_comprobante_id, cliente_id, punto_venta, nro_factura, fecha_vencimiento, detalle, neto, iva, total, observaciones, centro_costo_id, obra_id, archivo, estado, usuario_id) 
                VALUES ('$fecha', $tipo_comprobante_id, $cliente_id, $punto_venta, $nro_factura, $fecha_vencimiento, '$detalle', $neto, $iva, $total, '$observaciones', $centro_costo_id, $obra_id, " . ($archivo_nombre ? "'$archivo_nombre'" : "NULL") . ", '$estado', $usuario)";
    }
    
    if($conn->query($sql)){
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => $conn->error]);
    }
    exit;
}

// =======================
// ELIMINAR FACTURA
// =======================
if($accion == 'eliminar'){
    header('Content-Type: application/json');
    $id = (int)$_POST['id'];
    
    // 🛡️ SEGURIDAD ELIMINAR: Modificamos las consultas para validar el dueño si no es admin/contador
    $restriccion_usuario = "";
    if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
        $restriccion_usuario = " AND usuario_id = $usuario";
    }

    // Buscamos el archivo asegurándonos de que tenga permisos sobre el registro
    $res = $conn->query("SELECT archivo FROM facturas_venta WHERE id=$id $restriccion_usuario");
    if($row = $res->fetch_assoc()){
        if(!empty($row['archivo'])){
            $path = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/facturacion/' . $row['archivo'];
            if(file_exists($path)) @unlink($path);
        }
        
        // Procedemos al borrado físico únicamente si pasó la validación de propiedad
        $conn->query("DELETE FROM facturas_venta WHERE id=$id $restriccion_usuario");
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => 'No autorizado o registro no encontrado.']);
    }
    exit;
}

// ==========================================
// NUEVO: GUARDAR RETENCIÓN
// ==========================================
if($accion == 'guardar_retencion'){
    header('Content-Type: application/json');
    
    $factura_id = (int)($_POST['ret_factura_id'] ?? 0);
    $tipo_retencion_id = (int)($_POST['tipo_retencion_id'] ?? 0);
    $nro_certificado = $conn->real_escape_string(trim($_POST['nro_certificado'] ?? ''));
    $importe = (float)($_POST['importe_retencion'] ?? 0);
    $fecha_retencion = $_POST['fecha_retencion'] ?? '';

    if ($factura_id === 0 || $tipo_retencion_id === 0 || empty($fecha_retencion)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    // Procesar archivo adjunto del certificado
    $archivo_nombre = null;
    if (isset($_FILES['ret_archivo']) && $_FILES['ret_archivo']['error'] == 0) {
        $ext = pathinfo($_FILES['ret_archivo']['name'], PATHINFO_EXTENSION);
        $archivo_nombre = 'RET_' . $factura_id . '_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $ruta_destino = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/retenciones/';
        
        if (!file_exists($ruta_destino)) {
            mkdir($ruta_destino, 0777, true);
        }
        
        move_uploaded_file($_FILES['ret_archivo']['tmp_name'], $ruta_destino . $archivo_nombre);
    }

    $sql = "INSERT INTO retenciones_venta (factura_id, tipo_retencion_id, nro_certificado, importe, fecha_retencion, archivo, usuario_id) 
            VALUES ($factura_id, $tipo_retencion_id, '$nro_certificado', $importe, '$fecha_retencion', " . ($archivo_nombre ? "'$archivo_nombre'" : "NULL") . ", $usuario)";

    if($conn->query($sql)){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}

// ==========================================
// NUEVO: LISTAR RETENCIONES DE UNA FACTURA
// ==========================================
if($accion == 'listar_retenciones'){
    header('Content-Type: application/json');
    $factura_id = (int)($_GET['factura_id'] ?? 0);

    $sql = "SELECT r.id, r.nro_certificado, r.importe, r.fecha_retencion, r.archivo, t.nombre AS tipo_nombre 
            FROM retenciones_venta r
            LEFT JOIN tipos_retenciones t ON r.tipo_retencion_id = t.id
            WHERE r.factura_id = $factura_id 
            ORDER BY r.id DESC";

    $res = $conn->query($sql);
    
    if(!$res){
        echo json_encode(["success" => false, "message" => $conn->error]);
        exit;
    }
    
    $data = [];
    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// ==========================================
// NUEVO: ELIMINAR RETENCIÓN
// ==========================================
if($accion == 'eliminar_retencion'){
    header('Content-Type: application/json');
    $id = (int)$_POST['id'];

    // Buscamos si la retención tiene un archivo físico asociado para eliminarlo
    $res = $conn->query("SELECT archivo FROM retenciones_venta WHERE id=$id");
    if($row = $res->fetch_assoc()){
        if(!empty($row['archivo'])){
            $path = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/retenciones/' . $row['archivo'];
            if(file_exists($path)) @unlink($path);
        }
        
        $conn->query("DELETE FROM retenciones_venta WHERE id=$id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado.']);
    }
    exit;
}