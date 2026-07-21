<?php
include '../../config/database.php';
include '../../includes/auth.php';

if (!esAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // INTEGRIDAD: Verificar si existen usuarios con este rol asignado antes de borrar
    $stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE rol_id = ?");
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'No se puede eliminar el rol porque tiene usuarios asociados. Reasigna a los usuarios primero.'
        ]);
        $stmtCheck->close();
        exit;
    }
    $stmtCheck->close();

    // Borrado seguro
    $stmtDelete = $conn->prepare("DELETE FROM roles WHERE id = ?");
    $stmtDelete->bind_param("i", $id);

    if ($stmtDelete->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al intentar eliminar el rol.']);
    }
    $stmtDelete->close();
    exit;
}