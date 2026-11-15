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

// =====================================================================
// PESTAÑA: DATOS DE LA EMPRESA (solo admin)
// =====================================================================
require_once $_SERVER['DOCUMENT_ROOT'].'/contable/includes/empresa.php';

// =======================
// OBTENER DATOS
// =======================
if($accion == 'empresa_obtener'){
    $d = empresaDatos($conn);
    $d['logo_sistema_url'] = empresaArchivoLogo('logo_sistema') ? empresaLogoUrl() : '';
    $d['logo_pdf_url'] = empresaArchivoLogo('logo_pdf') ? '/contable/uploads/empresa/' . rawurlencode(empresaArchivoLogo('logo_pdf')) . '?v=' . time() : '';
    echo json_encode(['status' => 'OK', 'data' => $d]);
    exit;
}

// =======================
// GUARDAR DATOS Y LOGOS
// =======================
if($accion == 'empresa_guardar'){
    $valores = [];
    foreach (EMPRESA_CAMPOS as $campo) {
        $valores[$campo] = trim($_POST[$campo] ?? '');
    }
    if ($valores['razon_social'] === '') {
        echo json_encode(['status' => 'ERROR', 'msg' => 'La razón social es obligatoria.']);
        exit;
    }
    $valores['inicio_actividades'] = $valores['inicio_actividades'] !== '' ? $valores['inicio_actividades'] : null;

    // Logos: se validan antes de guardar nada
    $logos_nuevos = [];
    foreach (['logo_sistema', 'logo_pdf'] as $tipo) {
        if (empty($_FILES[$tipo]['name']) || $_FILES[$tipo]['error'] === UPLOAD_ERR_NO_FILE) continue;
        $ext = strtolower(pathinfo($_FILES[$tipo]['name'], PATHINFO_EXTENSION));
        if ($_FILES[$tipo]['error'] !== UPLOAD_ERR_OK || !in_array($ext, EMPRESA_LOGO_EXT[$tipo], true) || @getimagesize($_FILES[$tipo]['tmp_name']) === false) {
            echo json_encode(['status' => 'ERROR', 'msg' => $tipo === 'logo_pdf'
                ? 'El logo para PDF debe ser una imagen JPG válida.'
                : 'El logo del sistema debe ser una imagen PNG o JPG válida.']);
            exit;
        }
        $logos_nuevos[$tipo] = $ext;
    }

    $dir = empresaDirUploads();
    if ($logos_nuevos && !is_dir($dir)) mkdir($dir, 0775, true);

    $anteriores = empresaDatos($conn);
    foreach ($logos_nuevos as $tipo => $ext) {
        $nombre = $tipo . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($_FILES[$tipo]['tmp_name'], $dir . $nombre)) {
            echo json_encode(['status' => 'ERROR', 'msg' => 'No se pudo guardar el logo.']);
            exit;
        }
        $valores[$tipo] = $nombre;
    }

    // Un solo registro (id = 1): se inserta o actualiza
    $columnas = array_keys($valores);
    $sql = "INSERT INTO config_empresa (id, " . implode(', ', $columnas) . ") VALUES (1, " . implode(', ', array_fill(0, count($columnas), '?')) . ")
            ON DUPLICATE KEY UPDATE " . implode(', ', array_map(fn($c) => "$c = VALUES($c)", $columnas));
    $stmt = $conn->prepare($sql);
    $params = array_values($valores);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);

    if ($stmt->execute()) {
        // Recién guardado, se borran los logos reemplazados
        foreach (array_keys($logos_nuevos) as $tipo) {
            $viejo = basename((string)$anteriores[$tipo]);
            if ($viejo !== '' && is_file($dir . $viejo)) @unlink($dir . $viejo);
        }
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => $conn->error]);
    }
    exit;
}

// =======================
// QUITAR UN LOGO (vuelve al de siempre)
// =======================
if($accion == 'empresa_quitar_logo'){
    $tipo = $_POST['tipo'] ?? '';
    if (!in_array($tipo, ['logo_sistema', 'logo_pdf'], true)) {
        echo json_encode(['status' => 'ERROR', 'msg' => 'Logo inválido.']);
        exit;
    }
    $archivo = basename((string)empresaDatos($conn)[$tipo]);
    $conn->query("UPDATE config_empresa SET $tipo = NULL WHERE id = 1");
    if ($archivo !== '' && is_file(empresaDirUploads() . $archivo)) @unlink(empresaDirUploads() . $archivo);
    echo json_encode(['status' => 'OK']);
    exit;
}

echo json_encode(['status' => 'ERROR', 'msg' => 'Acción no válida']);
