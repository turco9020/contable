<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';
$usuario_logueado = $_SESSION['id'] ?? 0;

// LISTAR PERSONAL
if($accion == 'listar'){
    $clasificacion = $_GET['clasificacion'] ?? 'OPERATIVO';
    
    // Si filtran por INACTIVOS, muestra los dados de baja de cualquier clasificación
    if($clasificacion == 'INACTIVO') {
        $where = "p.estado = 'INACTIVO'";
    } else {
        $where = "p.clasificacion = '$clasificacion' AND p.estado != 'INACTIVO'";
    }

    $sql = "SELECT p.*, u.usuario as usuario_creador
            FROM personal p
            LEFT JOIN usuarios u ON u.id = p.usuario_id
            WHERE $where
            ORDER BY p.id DESC";

    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }
    echo json_encode(["data" => $data]);
    exit;
}

// GUARDAR / EDITAR PERSONAL
if($accion == 'guardar'){
    $id = $_POST['id'] ?? '';
    
    $clasificacion = $conn->real_escape_string($_POST['clasificacion'] ?? '');
    $cuil = $conn->real_escape_string($_POST['cuil'] ?? '');
    $apellido = $conn->real_escape_string($_POST['apellido'] ?? '');
    $nombre = $conn->real_escape_string($_POST['nombre'] ?? '');
    $puesto = $conn->real_escape_string($_POST['puesto'] ?? '');
    $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? "'".$conn->real_escape_string($_POST['fecha_nacimiento'])."'" : 'NULL';
    $fecha_ingreso = !empty($_POST['fecha_ingreso']) ? "'".$conn->real_escape_string($_POST['fecha_ingreso'])."'" : 'NULL';
    $telefono = $conn->real_escape_string($_POST['telefono'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $domicilio = $conn->real_escape_string($_POST['domicilio'] ?? '');
    $contacto_emergencia = $conn->real_escape_string($_POST['contacto_emergencia'] ?? '');
    $estado = $conn->real_escape_string($_POST['estado'] ?? 'ACTIVO');
    
    // Campos Registrales y Pago
    $situacion_laboral = $conn->real_escape_string($_POST['situacion_laboral'] ?? '');
    $procedencia = $conn->real_escape_string($_POST['procedencia'] ?? '');
    $fecha_alta_ieric = !empty($_POST['fecha_alta_ieric']) ? "'".$conn->real_escape_string($_POST['fecha_alta_ieric'])."'" : 'NULL';
    $fecha_alta_arca = !empty($_POST['fecha_alta_arca']) ? "'".$conn->real_escape_string($_POST['fecha_alta_arca'])."'" : 'NULL';
    $convenio_aplicable = $conn->real_escape_string($_POST['convenio_aplicable'] ?? '');
    $puesto_arca = $conn->real_escape_string($_POST['puesto_arca'] ?? '');
    $cond_pago = $conn->real_escape_string($_POST['cond_pago'] ?? '');
    $banco = $conn->real_escape_string($_POST['banco'] ?? '');
    $cbu = $conn->real_escape_string($_POST['cbu'] ?? '');
    $cta_cese = $conn->real_escape_string($_POST['cta_cese'] ?? '');

    // Coberturas, Seguros y Talles
    $calzado_talle = $conn->real_escape_string($_POST['calzado_talle'] ?? '');
    $pantalon_talle = $conn->real_escape_string($_POST['pantalon_talle'] ?? '');
    $camisa_talle = $conn->real_escape_string($_POST['camisa_talle'] ?? '');
    $vencimiento_preocupacional = !empty($_POST['vencimiento_preocupacional']) ? "'".$conn->real_escape_string($_POST['vencimiento_preocupacional'])."'" : 'NULL';
    $vencimiento_carnet_conducir = !empty($_POST['vencimiento_carnet_conducir']) ? "'".$conn->real_escape_string($_POST['vencimiento_carnet_conducir'])."'" : 'NULL';
    $vencimiento_art = !empty($_POST['vencimiento_art']) ? "'".$conn->real_escape_string($_POST['vencimiento_art'])."'" : 'NULL';
    $obra_social = $conn->real_escape_string($_POST['obra_social'] ?? '');
    $art_compañia = $conn->real_escape_string($_POST['art_compañia'] ?? '');
    
    // Campos Nuevos / Modificados
    $seguro_acc = $conn->real_escape_string($_POST['seguro_acc'] ?? '');
    $seguro_acc_desc = $conn->real_escape_string($_POST['seguro_acc_desc'] ?? '');
    $svo = $conn->real_escape_string($_POST['svo'] ?? '');
    $productores_seguro = $conn->real_escape_string($_POST['productores_seguro'] ?? '');
    $observaciones = $conn->real_escape_string($_POST['observaciones'] ?? '');

    $usuario_id_db = ($usuario_logueado > 0) ? intval($usuario_logueado) : 'NULL';

    if($id){
        $sql = "UPDATE personal SET 
            clasificacion='$clasificacion', cuil='$cuil', apellido='$apellido', nombre='$nombre',
            puesto='$puesto', fecha_nacimiento=$fecha_nacimiento, fecha_ingreso=$fecha_ingreso,
            telefono='$telefono', email='$email', domicilio='$domicilio', contacto_emergencia='$contacto_emergencia',
            estado='$estado', situacion_laboral='$situacion_laboral', procedencia='$procedencia',
            fecha_alta_ieric=$fecha_alta_ieric, fecha_alta_arca=$fecha_alta_arca, convenio_aplicable='$convenio_aplicable',
            puesto_arca='$puesto_arca', seguro_acc='$seguro_acc', seguro_acc_desc='$seguro_acc_desc', svo='$svo', 
            cond_pago='$cond_pago', banco='$banco', cbu='$cbu', cta_cese='$cta_cese',
            calzado_talle='$calzado_talle', pantalon_talle='$pantalon_talle', camisa_talle='$camisa_talle',
            vencimiento_preocupacional=$vencimiento_preocupacional, vencimiento_carnet_conducir=$vencimiento_carnet_conducir,
            vencimiento_art=$vencimiento_art, obra_social='$obra_social', art_compañia='$art_compañia', 
            productores_seguro='$productores_seguro', observaciones='$observaciones'
            WHERE id=$id";
        $conn->query($sql);
        $personal_id = $id;
    } else {
        $sql = "INSERT INTO personal (
            clasificacion, cuil, apellido, nombre, puesto, fecha_nacimiento, fecha_ingreso, telefono, email, domicilio,
            contacto_emergencia, estado, situacion_laboral, procedencia, fecha_alta_ieric, fecha_alta_arca, convenio_aplicable,
            puesto_arca, seguro_acc, seguro_acc_desc, svo, cond_pago, banco, cbu, cta_cese, calzado_talle, pantalon_talle, camisa_talle,
            vencimiento_preocupacional, vencimiento_carnet_conducir, vencimiento_art, obra_social, art_compañia, 
            productores_seguro, observaciones, usuario_id
        ) VALUES (
            '$clasificacion', '$cuil', '$apellido', '$nombre', '$puesto', $fecha_nacimiento, $fecha_ingreso, '$telefono', '$email', '$domicilio',
            '$contacto_emergencia', '$estado', '$situacion_laboral', '$procedencia', $fecha_alta_ieric, $fecha_alta_arca, '$convenio_aplicable',
            '$puesto_arca', '$seguro_acc', '$seguro_acc_desc', '$svo', '$cond_pago', '$banco', '$cbu', '$cta_cese', '$calzado_talle', '$pantalon_talle', '$camisa_talle',
            $vencimiento_preocupacional, $vencimiento_carnet_conducir, $vencimiento_art, '$obra_social', '$art_compañia', 
            '$productores_seguro', '$observaciones', $usuario_id_db
        )";
        $conn->query($sql);
        $personal_id = $conn->insert_id;
    }

    echo json_encode(["status" => "OK", "id" => $personal_id]);
    exit;
}

// MOVIMIENTOS / HISTÓRICO
if($accion == 'listar_movimientos') {
    $personal_id = intval($_GET['personal_id'] ?? 0);
    $data = [];
    if($personal_id > 0) {
        $res = $conn->query("SELECT m.*, u.usuario FROM personal_movimientos m LEFT JOIN usuarios u ON u.id = m.usuario_id WHERE m.personal_id = $personal_id ORDER BY m.fecha DESC, m.id DESC");
        while($r = $res->fetch_assoc()) $data[] = $r;
    }
    echo json_encode(["data" => $data]);
    exit;
}

if($accion == 'guardar_movimiento') {
    $personal_id = intval($_POST['personal_id']);
    $fecha = $conn->real_escape_string($_POST['fecha']);
    $tipo_evento = $conn->real_escape_string($_POST['tipo_evento']);
    $detalle = $conn->real_escape_string($_POST['detalle']);
    $usuario_id_db = ($usuario_logueado > 0) ? intval($usuario_logueado) : 'NULL';

    if($personal_id > 0 && !empty($tipo_evento)) {
        $conn->query("INSERT INTO personal_movimientos (personal_id, fecha, tipo_evento, detalle, usuario_id) VALUES ($personal_id, '$fecha', '$tipo_evento', '$detalle', $usuario_id_db)");
        echo "OK";
    }
    exit;
}

if($accion == 'eliminar_movimiento') {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM personal_movimientos WHERE id = $id");
    echo "OK";
    exit;
}

// SUBIR ADJUNTOS
if($accion == 'subir_adjunto'){
    $personal_id = intval($_POST['personal_id']);
    $tipo_adjunto = $_POST['tipo_adjunto'] ?? 'DOCUMENTO';

    if($personal_id > 0 && isset($_FILES['archivo']) && $_FILES['archivo']['error'] == UPLOAD_ERR_OK) {
        $carpeta = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/personal/' . $personal_id . '/';
        if(!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $nombre_orig = $conn->real_escape_string($_FILES['archivo']['name']);
        $ext = pathinfo($nombre_orig, PATHINFO_EXTENSION);
        $nombre_sis = 'adj_' . uniqid() . '.' . $ext;

        if(move_uploaded_file($_FILES['archivo']['tmp_name'], $carpeta . $nombre_sis)) {
            $ruta_rel = $personal_id . '/' . $nombre_sis;
            $conn->query("INSERT INTO personal_archivos (personal_id, archivo, nombre_original, tipo_adjunto) 
                          VALUES ($personal_id, '$ruta_rel', '$nombre_orig', '$tipo_adjunto')");
            echo json_encode(["status" => "OK"]);
            exit;
        }
    }
    echo json_encode(["status" => "ERROR"]);
    exit;
}

// LISTAR ARCHIVOS
if($accion == 'listar_archivos') {
    $personal_id = intval($_GET['personal_id'] ?? 0);
    $archivos = [];
    if($personal_id > 0) {
        $res = $conn->query("SELECT * FROM personal_archivos WHERE personal_id = $personal_id ORDER BY id DESC");
        while($r = $res->fetch_assoc()) $archivos[] = $r;
    }
    echo json_encode(["success" => true, "archivos" => $archivos]);
    exit;
}

// ELIMINAR ARCHIVO
if($accion == 'eliminar_archivo') {
    $id = intval($_POST['id']);
    $res = $conn->query("SELECT personal_id, archivo FROM personal_archivos WHERE id = $id");
    if($r = $res->fetch_assoc()) {
        $filepath = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/personal/' . $r['archivo'];
        if(file_exists($filepath)) unlink($filepath);
        $conn->query("DELETE FROM personal_archivos WHERE id = $id");
    }
    echo "OK";
    exit;
}

// ELIMINAR REGISTRO
if($accion == 'eliminar') {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM personal WHERE id = $id");
    echo "OK";
    exit;
}
?>