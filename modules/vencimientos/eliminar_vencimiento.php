<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => false, 'message' => 'ID de vencimiento inválido.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM vencimientos WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['status' => true, 'message' => 'Vencimiento eliminado con éxito.']);
} else {
    echo json_encode(['status' => false, 'message' => 'Error al eliminar: ' . $conn->error]);
}