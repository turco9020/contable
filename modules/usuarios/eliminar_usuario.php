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

    // 1) VERIFICAR SI EL USUARIO A ELIMINAR ES ADMINISTRADOR
    $stmtCheck = $conn->prepare("SELECT rol_id FROM usuarios WHERE id = ?");
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if ($resCheck) {
        // Asumiendo que el rol_id de Administrador es 1 (Ajustar si tu DB usa otro ID para admin)
        if (intval($resCheck['rol_id']) === 1) {
            // Contar cuántos administradores quedan en total
            $resCount = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol_id = 1");
            $rowCount = $resCount->fetch_assoc();

            if (intval($rowCount['total']) <= 1) {
                echo json_encode(['success' => false, 'message' => 'Operación cancelada. Debe quedar al menos un Administrador activo en el sistema.']);
                exit;
            }
        }
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