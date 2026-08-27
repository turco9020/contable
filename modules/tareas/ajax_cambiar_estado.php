<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

$conexion = $conn;

// 1. Identificar usuario y rol con fallbacks de sesión
$usuario_id  = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;
$rol_usuario = strtoupper($_SESSION['rol'] ?? '');
$rol_id      = $_SESSION['rol_id'] ?? 0;

$es_admin = ($rol_usuario === 'ADMIN' || $rol_usuario === 'ADMINISTRADOR' || $rol_id == 1);

// 2. Capturar y limpiar parámetros POST
$tarea_id     = isset($_POST['tarea_id']) ? intval($_POST['tarea_id']) : 0;
$nuevo_estado = isset($_POST['nuevo_estado']) ? trim($_POST['nuevo_estado']) : '';

$estados_permitidos = ['PENDIENTE', 'EN_PROCESO', 'REVISION', 'COMPLETADO'];

// 3. Validación de parámetros
if ($tarea_id > 0 && in_array($nuevo_estado, $estados_permitidos) && $usuario_id) {
    
    // Si NO es admin, verificar que la tarea pertenezca al usuario (creador o asignado)
    if (!$es_admin) {
        $check = mysqli_query($conexion, "SELECT id FROM tareas WHERE id = $tarea_id AND (creador_id = $usuario_id OR asignado_id = $usuario_id)");
        if (!$check || mysqli_num_rows($check) === 0) {
            echo json_encode(['success' => false, 'msg' => 'No tienes permiso para modificar esta tarea.']);
            exit;
        }
    }

    // Actualizar estado en la BD
    $stmt = $conexion->prepare("UPDATE tareas SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $tarea_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Error SQL: ' . $conexion->error]);
    }
} else {
    // Si falla la validación, devolvemos el detalle para depurar fácilmente
    echo json_encode([
        'success' => false, 
        'msg' => 'Parámetros no válidos.',
        'debug' => [
            'tarea_id' => $tarea_id,
            'nuevo_estado' => $nuevo_estado,
            'usuario_id' => $usuario_id
        ]
    ]);
}