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
            f.estado,
            IFNULL(c.nombre, 'SIN CLIENTE') AS cliente_nombre,
            IFNULL(tc.nombre, 'SIN TIPO') AS tipo_comprobante_nombre,
            IFNULL(cc.nombre, 'SIN CENTRO') AS centro_costo_nombre
        FROM facturas_venta f
        LEFT JOIN clientes c ON c.id = f.cliente_id
        LEFT JOIN tipos_comprobante tc ON tc.id = f.tipo_comprobante_id
        LEFT JOIN centros_costos cc ON cc.id = f.centro_costo_id
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
// GUARDAR / EDITAR
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
        $sql = "INSERT INTO facturas_venta (fecha, tipo_comprobante_id, cliente_id, punto_venta, nro_factura, fecha_vencimiento, detalle, neto, iva, total, observaciones, centro_costo_id, archivo, estado, usuario_id) 
                VALUES ('$fecha', $tipo_comprobante_id, $cliente_id, $punto_venta, $nro_factura, $fecha_vencimiento, '$detalle', $neto, $iva, $total, '$observaciones', $centro_costo_id, " . ($archivo_nombre ? "'$archivo_nombre'" : "NULL") . ", '$estado', $usuario)";
    }
    
    if($conn->query($sql)){
        // Si el UPDATE no afectó filas (porque el usuario_id no coincidía), tirará OK pero no cambiará nada en la BD
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
        // Si no se encontró la fila (o no le pertenece al usuario) mandamos un error silencioso o controlado
        echo json_encode(['status' => 'ERROR', 'msg' => 'No autorizado o registro no encontrado.']);
    }
    exit;
}