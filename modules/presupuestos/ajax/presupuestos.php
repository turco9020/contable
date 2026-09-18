<?php
// Desactivar impresión de errores HTML que rompen el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../../config/database.php';

// Limpiar cualquier buffer previo (espacios o warnings)
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');

$accion = $_GET['accion'] ?? '';

if ($accion === 'listar') {
    try {
        $f_desde = $_GET['f_desde'] ?? '';
        $f_hasta = $_GET['f_hasta'] ?? '';
        $f_cliente = $_GET['f_cliente'] ?? '';
        $f_estado = $_GET['f_estado'] ?? '';

        $where = ["1=1"];
        $params = [];
        $types = "";

        if (!empty($f_desde)) { $where[] = "p.fecha >= ?"; $params[] = $f_desde; $types .= "s"; }
        if (!empty($f_hasta)) { $where[] = "p.fecha <= ?"; $params[] = $f_hasta; $types .= "s"; }
        if (!empty($f_cliente)) { $where[] = "p.cliente_id = ?"; $params[] = $f_cliente; $types .= "i"; }
        if (!empty($f_estado)) { $where[] = "p.estado = ?"; $params[] = $f_estado; $types .= "s"; }

        $sql = "SELECT p.*, 
                       IFNULL(c.nombre, '-') AS cliente_nombre, 
                       IFNULL(o.nombre, '-') AS obra_nombre
                FROM presupuestos p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                LEFT JOIN obras o ON p.obra_id = o.id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY p.id DESC";

        $stmt = $conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['data' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

if ($accion === 'obtener') {
    $id = intval($_GET['id'] ?? 0);
    
    $stmt = $conn->prepare("SELECT * FROM presupuestos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $presupuesto = $stmt->get_result()->fetch_assoc();

    if ($presupuesto) {
        $stmtDet = $conn->prepare("SELECT * FROM presupuesto_detalles WHERE presupuesto_id = ? ORDER BY item_orden ASC");
        $stmtDet->bind_param("i", $id);
        $stmtDet->execute();
        $presupuesto['items'] = $stmtDet->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['status' => 'OK', 'data' => $presupuesto], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['status' => 'ERROR', 'message' => 'Presupuesto no encontrado']);
    }
    exit;
}

if ($accion === 'guardar') {
    try {
        $conn->begin_transaction();

        $id = intval($_POST['id'] ?? 0);
        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $cliente_id = intval($_POST['cliente_id'] ?? 0);
        $obra_id = !empty($_POST['obra_id']) ? intval($_POST['obra_id']) : null;
        $titulo = trim($_POST['titulo'] ?? '');
        $coeficiente = floatval(str_replace(',', '.', $_POST['coeficiente'] ?? 1));
        $condiciones = trim($_POST['condiciones'] ?? '');
        $estado = $_POST['estado'] ?? 'BORRADOR';
        $usuario_id = $_SESSION['id'] ?? null;

        $items = $_POST['items'] ?? [];

        $costo_directo = 0;
        foreach ($items as $item) {
            $cant = floatval(str_replace(',', '.', $item['cantidad'] ?? 0));
            $unit = floatval(str_replace(',', '.', $item['costo_unitario'] ?? 0));
            $costo_directo += ($cant * $unit);
        }

        $monto_total = $costo_directo * $coeficiente;

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE presupuestos SET fecha=?, cliente_id=?, obra_id=?, titulo=?, costo_directo=?, coeficiente=?, monto_total=?, condiciones=?, estado=? WHERE id=?");
            $stmt->bind_param("siisdddssi", $fecha, $cliente_id, $obra_id, $titulo, $costo_directo, $coeficiente, $monto_total, $condiciones, $estado, $id);
            $stmt->execute();

            $stmtDel = $conn->prepare("DELETE FROM presupuesto_detalles WHERE presupuesto_id = ?");
            $stmtDel->bind_param("i", $id);
            $stmtDel->execute();

            $presupuesto_id = $id;
        } else {
            $anio = date('Y', strtotime($fecha));
            $resMax = $conn->query("SELECT MAX(id) as max_id FROM presupuestos");
            $rowMax = $resMax->fetch_assoc();
            $nextId = intval($rowMax['max_id'] ?? 0) + 1;
            $numero = sprintf("PRES-%s-%04d", $anio, $nextId);

            $stmt = $conn->prepare("INSERT INTO presupuestos (numero, fecha, cliente_id, obra_id, titulo, costo_directo, coeficiente, monto_total, condiciones, estado, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiisdddssi", $numero, $fecha, $cliente_id, $obra_id, $titulo, $costo_directo, $coeficiente, $monto_total, $condiciones, $estado, $usuario_id);
            $stmt->execute();

            $presupuesto_id = $conn->insert_id;
        }

        $stmtDet = $conn->prepare("INSERT INTO presupuesto_detalles (presupuesto_id, item_orden, descripcion, unidad, cantidad, costo_unitario, costo_subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $orden = 1;
        foreach ($items as $item) {
            $desc = trim($item['descripcion'] ?? '');
            if (empty($desc)) continue;

            $uni = trim($item['unidad'] ?? 'GL');
            $cant = floatval(str_replace(',', '.', $item['cantidad'] ?? 0));
            $unit = floatval(str_replace(',', '.', $item['costo_unitario'] ?? 0));
            $subt = $cant * $unit;

            $stmtDet->bind_param("iissddd", $presupuesto_id, $orden, $desc, $uni, $cant, $unit, $subt);
            $stmtDet->execute();
            $orden++;
        }

        $conn->commit();
        echo json_encode(['status' => 'OK', 'message' => 'Presupuesto guardado con éxito']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($accion === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM presupuestos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(['status' => 'OK']);
    exit;
}