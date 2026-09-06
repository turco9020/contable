<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';
$usuario_logueado = $_SESSION['id'] ?? 0;

// LISTAR UNIDADES
if($accion == 'listar'){
    $clasificacion = $_GET['clasificacion'] ?? 'VEHICULO';
    $sql = "SELECT v.*, u.usuario as usuario_creador
            FROM vehiculos v
            LEFT JOIN usuarios u ON u.id = v.usuario_id
            WHERE v.clasificacion = '$clasificacion'
            ORDER BY v.id DESC";

    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }
    echo json_encode(["data" => $data]);
    exit;
}

// GUARDAR / EDITAR VEHÍCULO
if($accion == 'guardar'){
    $id = $_POST['id'] ?? '';
    
    $clasificacion = $conn->real_escape_string($_POST['clasificacion']);
    $dominio_patente = $conn->real_escape_string($_POST['dominio_patente']);
    $marca = $conn->real_escape_string($_POST['marca']);
    $modelo = $conn->real_escape_string($_POST['modelo']);
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $anio = $_POST['anio'] ? intval($_POST['anio']) : 'NULL';
    $titular = $conn->real_escape_string($_POST['titular']);
    $km_horas_inicial = floatval($_POST['km_horas_inicial'] ?? 0);
    $fecha_adquisicion = $_POST['fecha_adquisicion'] ? "'".$conn->real_escape_string($_POST['fecha_adquisicion'])."'" : 'NULL';
    $km_horas_actual = floatval($_POST['km_horas_actual'] ?? 0);
    $unidad_medida = $conn->real_escape_string($_POST['unidad_medida']);
    $estado = $conn->real_escape_string($_POST['estado']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    
    // Ficha Técnica con Cantidades
    $aceite_motor = $conn->real_escape_string($_POST['aceite_motor']);
    $cant_aceite_motor = $conn->real_escape_string($_POST['cant_aceite_motor']);
    $aceite_caja = $conn->real_escape_string($_POST['aceite_caja']);
    $cant_aceite_caja = $conn->real_escape_string($_POST['cant_aceite_caja']);
    $aceite_diferencial = $conn->real_escape_string($_POST['aceite_diferencial']);
    $cant_aceite_diferencial = $conn->real_escape_string($_POST['cant_aceite_diferencial']);
    $aceite_hidraulico = $conn->real_escape_string($_POST['aceite_hidraulico']);
    $cant_aceite_hidraulico = $conn->real_escape_string($_POST['cant_aceite_hidraulico']);
    $filtros_codigos = $conn->real_escape_string($_POST['filtros_codigos']);
    $vencimiento_vtv = $_POST['vencimiento_vtv'] ? "'".$conn->real_escape_string($_POST['vencimiento_vtv'])."'" : 'NULL';
    $vencimiento_seguro = $_POST['vencimiento_seguro'] ? "'".$conn->real_escape_string($_POST['vencimiento_seguro'])."'" : 'NULL';
    $compañia_seguro = $conn->real_escape_string($_POST['compañia_seguro']);
    $nro_poliza = $conn->real_escape_string($_POST['nro_poliza']);

    $usuario_id_db = ($usuario_logueado > 0) ? intval($usuario_logueado) : 'NULL';

    if($id){
        $sql = "UPDATE vehiculos SET 
            clasificacion='$clasificacion', dominio_patente='$dominio_patente', marca='$marca', modelo='$modelo',
            tipo='$tipo', anio=$anio, titular='$titular', km_horas_inicial=$km_horas_inicial,
            fecha_adquisicion=$fecha_adquisicion, km_horas_actual=$km_horas_actual, unidad_medida='$unidad_medida',
            estado='$estado', descripcion='$descripcion', aceite_motor='$aceite_motor', cant_aceite_motor='$cant_aceite_motor',
            aceite_caja='$aceite_caja', cant_aceite_caja='$cant_aceite_caja', aceite_diferencial='$aceite_diferencial', cant_aceite_diferencial='$cant_aceite_diferencial',
            aceite_hidraulico='$aceite_hidraulico', cant_aceite_hidraulico='$cant_aceite_hidraulico', filtros_codigos='$filtros_codigos',
            vencimiento_vtv=$vencimiento_vtv, vencimiento_seguro=$vencimiento_seguro, compañia_seguro='$compañia_seguro', nro_poliza='$nro_poliza'
            WHERE id=$id";
        $conn->query($sql);
        $vehiculo_id = $id;
    } else {
        $sql = "INSERT INTO vehiculos (clasificacion, dominio_patente, marca, modelo, tipo, anio, titular, km_horas_inicial, fecha_adquisicion, km_horas_actual, unidad_medida, estado, descripcion, aceite_motor, cant_aceite_motor, aceite_caja, cant_aceite_caja, aceite_diferencial, cant_aceite_diferencial, aceite_hidraulico, cant_aceite_hidraulico, filtros_codigos, vencimiento_vtv, vencimiento_seguro, compañia_seguro, nro_poliza, usuario_id)
        VALUES ('$clasificacion', '$dominio_patente', '$marca', '$modelo', '$tipo', $anio, '$titular', $km_horas_inicial, $fecha_adquisicion, $km_horas_actual, '$unidad_medida', '$estado', '$descripcion', '$aceite_motor', '$cant_aceite_motor', '$aceite_caja', '$cant_aceite_caja', '$aceite_diferencial', '$cant_aceite_diferencial', '$aceite_hidraulico', '$cant_aceite_hidraulico', '$filtros_codigos', $vencimiento_vtv, $vencimiento_seguro, '$compañia_seguro', '$nro_poliza', $usuario_id_db)";
        $conn->query($sql);
        $vehiculo_id = $conn->insert_id;
    }

    echo json_encode(["status" => "OK", "id" => $vehiculo_id]);
    exit;
}

// SUBIR ARCHIVOS ADJUNTOS POR CATEGORÍA
if($accion == 'subir_adjunto'){
    $vehiculo_id = intval($_POST['vehiculo_id']);
    $tipo_adjunto = $_POST['tipo_adjunto'] ?? 'DOCUMENTO';

    if($vehiculo_id > 0 && isset($_FILES['archivo']) && $_FILES['archivo']['error'] == UPLOAD_ERR_OK) {
        $carpeta = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/vehiculos/' . $vehiculo_id . '/';
        if(!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $nombre_orig = $conn->real_escape_string($_FILES['archivo']['name']);
        $ext = pathinfo($nombre_orig, PATHINFO_EXTENSION);
        $nombre_sis = 'adj_' . uniqid() . '.' . $ext;

        if(move_uploaded_file($_FILES['archivo']['tmp_name'], $carpeta . $nombre_sis)) {
            $ruta_rel = $vehiculo_id . '/' . $nombre_sis;
            $conn->query("INSERT INTO vehiculo_archivos (vehiculo_id, archivo, nombre_original, tipo_adjunto) 
                          VALUES ($vehiculo_id, '$ruta_rel', '$nombre_orig', '$tipo_adjunto')");
            echo json_encode(["status" => "OK"]);
            exit;
        }
    }
    echo json_encode(["status" => "ERROR"]);
    exit;
}

// LISTAR ARCHIVOS POR VEHÍCULO
if($accion == 'listar_archivos') {
    $vehiculo_id = intval($_GET['vehiculo_id'] ?? 0);
    $archivos = [];
    if($vehiculo_id > 0) {
        $res = $conn->query("SELECT * FROM vehiculo_archivos WHERE vehiculo_id = $vehiculo_id ORDER BY id DESC");
        while($r = $res->fetch_assoc()) $archivos[] = $r;
    }
    echo json_encode(["success" => true, "archivos" => $archivos]);
    exit;
}

// ELIMINAR ARCHIVO ADJUNTO
if($accion == 'eliminar_archivo') {
    $id = intval($_POST['id']);
    $res = $conn->query("SELECT vehiculo_id, archivo FROM vehiculo_archivos WHERE id = $id");
    if($r = $res->fetch_assoc()) {
        $filepath = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/vehiculos/' . $r['archivo'];
        if(file_exists($filepath)) unlink($filepath);
        $conn->query("DELETE FROM vehiculo_archivos WHERE id = $id");
    }
    echo "OK";
    exit;
}

// GUARDAR SERVICE INDIVIDUAL (INCLUYE USUARIO)
if($accion == 'guardar_service') {
    $vehiculo_id = intval($_POST['vehiculo_id']);
    $fecha = $conn->real_escape_string($_POST['fecha_service']);
    $lectura = floatval($_POST['lectura_km_horas']);
    $realizado_por = $conn->real_escape_string($_POST['realizado_por']);
    $trabajo = $conn->real_escape_string($_POST['trabajo_realizado']);
    $costo = floatval($_POST['costo']);

    $conn->query("INSERT INTO vehiculo_services (vehiculo_id, fecha, lectura_km_horas, realizado_por, trabajo_realizado, costo, usuario_id) 
                  VALUES ($vehiculo_id, '$fecha', $lectura, '$realizado_por', '$trabajo', $costo, $usuario_logueado)");

    $conn->query("UPDATE vehiculos SET km_horas_actual = GREATEST(km_horas_actual, $lectura) WHERE id = $vehiculo_id");

    echo json_encode(["success" => true]);
    exit;
}

// LISTAR SERVICES CON USUARIO CREADOR
if($accion == 'listar_services') {
    $vehiculo_id = intval($_GET['vehiculo_id'] ?? 0);
    $services = [];
    if($vehiculo_id > 0) {
        $sql = "SELECT s.*, u.usuario as usuario_nombre 
                FROM vehiculo_services s 
                LEFT JOIN usuarios u ON u.id = s.usuario_id 
                WHERE s.vehiculo_id = $vehiculo_id 
                ORDER BY s.fecha DESC";
        $res = $conn->query($sql);
        while($r = $res->fetch_assoc()) $services[] = $r;
    }
    echo json_encode(["success" => true, "services" => $services]);
    exit;
}

// LISTAR GASTOS
if($accion == 'listar_gastos') {
    $vehiculo_id = intval($_GET['vehiculo_id'] ?? 0);
    $gastos = [];
    if($vehiculo_id > 0) {
        $sql = "SELECT g.id, g.fecha, g.numero_comprobante, g.detalle, g.total, p.nombre AS proveedor
                FROM gastos g
                LEFT JOIN proveedores p ON p.id = g.proveedor_id
                WHERE g.vehiculo_id = $vehiculo_id
                ORDER BY g.fecha DESC";
        $res = $conn->query($sql);
        while($r = $res->fetch_assoc()) $gastos[] = $r;
    }
    echo json_encode(["success" => true, "gastos" => $gastos]);
    exit;
}

// ELIMINAR VEHÍCULO
if($accion == 'eliminar') {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM vehiculos WHERE id = $id");
    echo "OK";
    exit;
}

// SELECT PARA SELECT2 / TOMSELECT EN OTROS MÓDULOS (GASTOS, ETC.)
if($accion == 'select_opt'){
    $sql = "SELECT id, CONCAT(marca, ' ', modelo, ' (', dominio_patente, ')') AS nombre 
            FROM vehiculos 
            ORDER BY marca, modelo ASC";
    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }
    echo json_encode(["data" => $data]);
    exit;
}
?>