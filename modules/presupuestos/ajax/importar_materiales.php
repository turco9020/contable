<?php
// ajax/importar_materiales.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/contable/config/database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? NULL;

// Obtener nombre del usuario activo
$db_conn = $conn ?? $conexion ?? $db ?? null;
$usuario_actual = 'Sistema';

if ($db_conn && $usuario_id) {
    $stmt_u = $db_conn->prepare("SELECT usuario FROM usuarios WHERE id = ?");
    if ($stmt_u) {
        $stmt_u->bind_param("i", $usuario_id);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();
        if ($row_u = $res_u->fetch_assoc()) {
            $usuario_actual = $row_u['usuario'];
        }
    }
}

function parseMonto($val) {
    if (empty($val)) return 0.0;
    $val = str_replace(',', '.', (string)$val);
    return (float)preg_replace('/[^\d.]/', '', $val);
}

// ---- FASE 1: PREVISUALIZACIÓN ----
if ($action === 'preview') {
    if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir el archivo CSV.']);
        exit;
    }

    $filePath = $_FILES['archivo_csv']['tmp_name'];
    $handle = fopen($filePath, 'r');
    
    if (!$handle) {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo abrir el archivo CSV.']);
        exit;
    }

    // Detectar separador (coma o punto y coma)
    $primeraLinea = fgets($handle);
    $separador = (substr_count($primeraLinea, ';') > substr_count($primeraLinea, ',')) ? ';' : ',';
    rewind($handle);

    $registros = [];
    $nuevosCount = 0;
    $actualizadosCount = 0;

    // Prepared statements para verificar existencia por ID o Nombre
    $stmtCheckId = $db_conn->prepare("SELECT id FROM presupuesto_materiales WHERE id = ? LIMIT 1");
    $stmtCheckNombre = $db_conn->prepare("SELECT id FROM presupuesto_materiales WHERE LOWER(nombre) = LOWER(?) LIMIT 1");

    while (($row = fgetcsv($handle, 1000, $separador)) !== FALSE) {
        $val_col0 = trim($row[0] ?? '');
        $val_col1 = trim($row[1] ?? '');

        // Saltar encabezados
        if (in_array(strtolower($val_col0), ['id', 'material', 'nombre', 'sistema contable']) || 
            in_array(strtolower($val_col1), ['material', 'nombre', 'sistema contable'])) {
            continue;
        }

        $id        = (!empty($val_col0) && is_numeric($val_col0)) ? (int)$val_col0 : null;
        $nombre    = $val_col1;
        $precio_b  = parseMonto($row[2] ?? 0);
        $unidad    = trim($row[3] ?? '');
        $precio_u  = parseMonto($row[4] ?? 0);
        $proveedor = trim($row[5] ?? '');

        if (empty($nombre)) continue;

        // Verificar si existe
        $existeId = null;
        if ($id && $id > 0) {
            $stmtCheckId->bind_param("i", $id);
            $stmtCheckId->execute();
            $res = $stmtCheckId->get_result();
            if ($rowExist = $res->fetch_assoc()) $existeId = $rowExist['id'];
        }

        if (!$existeId) {
            $stmtCheckNombre->bind_param("s", $nombre);
            $stmtCheckNombre->execute();
            $res = $stmtCheckNombre->get_result();
            if ($rowExist = $res->fetch_assoc()) $existeId = $rowExist['id'];
        }

        $esExiste = !empty($existeId);
        if ($esExiste) {
            $actualizadosCount++;
        } else {
            $nuevosCount++;
        }

        $registros[] = [
            'id'             => $existeId ?? $id,
            'nombre'         => $nombre,
            'unidad_medida'  => $unidad,
            'precio_bulto'   => $precio_b,
            'precio_unitario'=> $precio_u,
            'proveedor'      => $proveedor,
            'existe'         => $esExiste
        ];
    }

    fclose($handle);
    $stmtCheckId->close();
    $stmtCheckNombre->close();

    echo json_encode([
        'status' => 'success',
        'totales' => [
            'total'        => count($registros),
            'nuevos'       => $nuevosCount,
            'actualizados' => $actualizadosCount
        ],
        'data' => $registros
    ]);
    exit;
}

// ---- FASE 2: CONFIRMACIÓN E INSERCIÓN/UPDATE ----
if ($action === 'confirm_import') {
    $items = json_decode($_POST['items'] ?? '[]', true);

    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'No hay datos para importar.']);
        exit;
    }

    $processed = 0;
    $db_conn->begin_transaction();

    try {
        $fecha_act = date('Y-m-d');

        $stmtUpdate = $db_conn->prepare("UPDATE presupuesto_materiales SET 
            nombre=?, unidad_medida=?, precio_bulto=?, precio_unitario=?, proveedor=?, fecha_actualizacion=?, usuario_nombre=? 
            WHERE id=?");

        $stmtInsert = $db_conn->prepare("INSERT INTO presupuesto_materiales 
            (nombre, unidad_medida, precio_bulto, precio_unitario, proveedor, fecha_actualizacion, usuario_nombre) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $nombre    = $item['nombre'];
            $unidad    = $item['unidad_medida'];
            $precio_b  = (float)$item['precio_bulto'];
            $precio_u  = (float)$item['precio_unitario'];
            $proveedor = $item['proveedor'];
            $id        = !empty($item['id']) ? (int)$item['id'] : null;

            if (!empty($item['existe']) && $id > 0) {
                // UPDATE
                $stmtUpdate->bind_param("ssddsssi", $nombre, $unidad, $precio_b, $precio_u, $proveedor, $fecha_act, $usuario_actual, $id);
                $stmtUpdate->execute();
            } else {
                // INSERT
                $stmtInsert->bind_param("ssddsss", $nombre, $unidad, $precio_b, $precio_u, $proveedor, $fecha_act, $usuario_actual);
                $stmtInsert->execute();
            }
            $processed++;
        }

        $db_conn->commit();
        $stmtUpdate->close();
        $stmtInsert->close();

        echo json_encode(['status' => 'success', 'imported_count' => $processed]);
    } catch (Exception $e) {
        $db_conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}