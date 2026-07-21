<?php
// Aseguramos las rutas con __DIR__ para que el AJAX no falle
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../includes/auth.php';

// Validar que sea administrador
if (!esAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

if ($_POST) {
    $usuario = trim($_POST['user']);
    $password = trim($_POST['pass']);
    $rol_id = intval($_POST['rol_id']);

    if (empty($usuario) || empty($password) || empty($rol_id)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
        exit;
    }

    // Comprobar si el usuario ya existe
    $stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $stmtCheck->bind_param("s", $usuario);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya se encuentra registrado.']);
        $stmtCheck->close();
        exit;
    }
    $stmtCheck->close();

    // Hashear la contraseña
    $passwordHasheada = password_hash($password, PASSWORD_DEFAULT);

    // Insertar
    $stmtInsert = $conn->prepare("INSERT INTO usuarios (usuario, password, rol_id) VALUES (?, ?, ?)");
    $stmtInsert->bind_param("ssi", $usuario, $passwordHasheada, $rol_id);

    if ($stmtInsert->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
    }
    
    $stmtInsert->close();
    exit;
}