<?php
// Aseguramos las rutas con __DIR__ para evitar fallos en peticiones AJAX
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../includes/auth.php';

// Validar que sea administrador
if (!esAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

if ($_POST) {
    $id = intval($_POST['id']);
    $usuario = trim($_POST['user']);
    $password = trim($_POST['pass']);
    $rol_id = intval($_POST['rol_id']);

    // Validaciones básicas de campos obligatorios
    if (empty($id) || empty($usuario) || empty($rol_id)) {
        echo json_encode(['success' => false, 'message' => 'El usuario y el rol son campos obligatorios.']);
        exit;
    }

    // Comprobar si el nombre de usuario ya lo tiene OTRO usuario diferente (evitar duplicados)
    $stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ? AND id != ?");
    $stmtCheck->bind_param("si", $usuario, $id);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya se encuentra registrado por otra persona.']);
        $stmtCheck->close();
        exit;
    }
    $stmtCheck->close();

    // LÓGICA DE ACTUALIZACIÓN SEGÚN LA CONTRASEÑA
    if (!empty($password)) {
        // Si escribió algo, validamos el largo y la cambiamos
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.']);
            exit;
        }
        
        $passwordHasheada = password_hash($password, PASSWORD_DEFAULT);
        
        $stmtUpdate = $conn->prepare("UPDATE usuarios SET usuario = ?, password = ?, rol_id = ? WHERE id = ?");
        $stmtUpdate->bind_param("ssii", $usuario, $passwordHasheada, $rol_id, $id);
    } else {
        // Si vino vacía, actualizamos solo usuario y rol (dejando intacta la clave actual)
        $stmtUpdate = $conn->prepare("UPDATE usuarios SET usuario = ?, rol_id = ? WHERE id = ?");
        $stmtUpdate->bind_param("sii", $usuario, $rol_id, $id);
    }

    // Ejecutar la actualización definitiva
    if ($stmtUpdate->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al intentar actualizar el usuario en la base de datos.']);
    }
    
    $stmtUpdate->close();
    exit;
}