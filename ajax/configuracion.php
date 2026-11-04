<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/auth.php';

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? '';

// Listar está disponible para cualquier usuario logueado (ej: el select de Facturación);
// guardar y eliminar quedan solo para admin
$acciones_lectura = ['iibb_listar'];
if (!in_array($accion, $acciones_lectura) && !esAdmin()) {
    echo json_encode(['status' => 'ERROR', 'msg' => 'Sin permisos']);
    exit;
}

// =====================================================================
// PESTAÑA: PORCENTAJES IIBB
// =====================================================================

// =======================
// LISTAR ACTIVIDADES
// =======================
if($accion == 'iibb_listar'){
    $data = [];

    $res = $conn->query("SELECT id, codigo, actividad, alicuota, tratamiento, activo FROM config_iibb_actividades ORDER BY codigo ASC");
    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(['data' => $data]);
    exit;
}

// =======================
// GUARDAR / EDITAR
// =======================
if($accion == 'iibb_guardar'){
    $id = (int)($_POST['id'] ?? 0);
    $codigo = trim($_POST['codigo'] ?? '');
    $actividad = trim($_POST['actividad'] ?? '');
    $tratamiento = trim($_POST['tratamiento'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;

    // La alícuota puede quedar vacía (ej: actividad exenta)
    $alicuota_txt = str_replace(',', '.', trim($_POST['alicuota'] ?? ''));
    $alicuota = ($alicuota_txt === '') ? null : (float)$alicuota_txt;
    if ($tratamiento === '') $tratamiento = null;

    if ($codigo === '' || $actividad === '') {
        echo json_encode(['status' => 'ERROR', 'msg' => 'El código y la actividad son obligatorios.']);
        exit;
    }

    // El código no se puede repetir
    $stmt = $conn->prepare("SELECT id FROM config_iibb_actividades WHERE codigo = ? AND id <> ?");
    $stmt->bind_param('si', $codigo, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'ERROR', 'msg' => 'Ya existe una actividad con el código ' . $codigo . '.']);
        exit;
    }

    if($id){
        $stmt = $conn->prepare("UPDATE config_iibb_actividades SET codigo=?, actividad=?, alicuota=?, tratamiento=?, activo=? WHERE id=?");
        $stmt->bind_param('ssdsii', $codigo, $actividad, $alicuota, $tratamiento, $activo, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO config_iibb_actividades (codigo, actividad, alicuota, tratamiento, activo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssdsi', $codigo, $actividad, $alicuota, $tratamiento, $activo);
    }

    if($stmt->execute()){
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => $conn->error]);
    }
    exit;
}

// =======================
// ELIMINAR
// =======================
if($accion == 'iibb_eliminar'){
    $id = (int)($_POST['id'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM config_iibb_actividades WHERE id=?");
    $stmt->bind_param('i', $id);

    if($stmt->execute()){
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => $conn->error]);
    }
    exit;
}

echo json_encode(['status' => 'ERROR', 'msg' => 'Acción no válida']);
