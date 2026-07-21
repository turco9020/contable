<?php
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../includes/auth.php';

if (!esAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    if ($id === $_SESSION['id']) {
        echo json_encode(['success' => false, 'message' => 'No puedes eliminar a tu propio usuario en sesión.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al intentar eliminar el registro.']);
    }
    $stmt->close();
    exit;
}