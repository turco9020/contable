<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

$id = intval($_POST['id'] ?? 0);
$fecha_pago = $_POST['fecha_pago'] ?? date('Y-m-d H:i:s');
$caja_id = !empty($_POST['caja_id']) ? intval($_POST['caja_id']) : NULL;

if ($id <= 0) {
    echo json_encode(['status' => false, 'message' => 'ID inválido.']);
    exit;
}

$stmt = $conn->prepare("UPDATE vencimientos SET estado='PAGADO', fecha_pago=?, caja_id=? WHERE id=?");
$stmt->bind_param("sii", $fecha_pago, $caja_id, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => true, 'message' => 'Vencimiento marcado como PAGADO. Alerta desactivada.']);
} else {
    echo json_encode(['status' => false, 'message' => 'Error al actualizar estado: ' . $conn->error]);
}