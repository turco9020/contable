<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$titulo = trim($_POST['titulo'] ?? '');
$monto = floatval($_POST['monto'] ?? 0);
$fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
$categoria = $_POST['categoria'] ?? 'General';
$dias_aviso = intval($_POST['dias_aviso'] ?? 7);
$proveedor_id = !empty($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : NULL;
$obra_id = !empty($_POST['obra_id']) ? intval($_POST['obra_id']) : NULL;
$descripcion = trim($_POST['descripcion'] ?? '');
$usuario_id = $_SESSION['id'] ?? NULL;

if (empty($titulo) || empty($fecha_vencimiento) || $monto <= 0) {
    echo json_encode(['status' => false, 'message' => 'Complete los campos obligatorios.']);
    exit;
}

// Subida de Archivo
$nombreArchivo = NULL;
if (!empty($_FILES['archivo']['name'])) {
    $dirUpload = __DIR__ . '/../../uploads/vencimientos/';
    if (!is_dir($dirUpload)) {
        mkdir($dirUpload, 0777, true);
    }

    $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
    $nombreArchivo = 'venc_' . time() . '_' . uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['archivo']['tmp_name'], $dirUpload . $nombreArchivo);
}

if (!empty($id)) {
    // ACTUALIZAR
    $sql = "UPDATE vencimientos SET 
            titulo=?, monto=?, fecha_vencimiento=?, categoria=?, dias_aviso=?, 
            proveedor_id=?, obra_id=?, descripcion=?, usuario_id=? ";
    
    if ($nombreArchivo) {
        $sql .= ", archivo='$nombreArchivo' ";
    }
    $sql .= " WHERE id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssiiisii", $titulo, $monto, $fecha_vencimiento, $categoria, $dias_aviso, $proveedor_id, $obra_id, $descripcion, $usuario_id, $id);
} else {
    // INSERTAR
    $sql = "INSERT INTO vencimientos 
            (titulo, monto, fecha_vencimiento, categoria, dias_aviso, proveedor_id, obra_id, descripcion, archivo, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssiiissi", $titulo, $monto, $fecha_vencimiento, $categoria, $dias_aviso, $proveedor_id, $obra_id, $descripcion, $nombreArchivo, $usuario_id);
}

if ($stmt->execute()) {
    echo json_encode(['status' => true, 'message' => 'Vencimiento guardado correctamente.']);
} else {
    echo json_encode(['status' => false, 'message' => 'Error al guardar en BD: ' . $conn->error]);
}