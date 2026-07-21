<?php
// Aseguramos que las rutas no fallen sin importar desde dónde se ejecute el script
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../includes/auth.php';

// Validar que sea administrador
if (!esAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

if ($_POST) {
    // Sanitizar y pasar a minúsculas
    $nombre = strtolower(trim($_POST['nombre_rol']));

    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre del rol no puede estar vacío.']);
        exit;
    }

    // Verificar duplicados
    $stmtCheck = $conn->prepare("SELECT id FROM roles WHERE nombre = ?");
    if (!$stmtCheck) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de verificación.']);
        exit;
    }
    
    $stmtCheck->bind_param("s", $nombre);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Este rol ya existe en el sistema.']);
        $stmtCheck->close();
        exit;
    }
    $stmtCheck->close();

    // Insertar el nuevo rol
    $stmtInsert = $conn->prepare("INSERT INTO roles (nombre) VALUES (?)");
    if (!$stmtInsert) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de inserción.']);
        exit;
    }
    
    $stmtInsert->bind_param("s", $nombre);

    if ($stmtInsert->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la inserción en la base de datos.']);
    }
    $stmtInsert->close();
    exit;
}