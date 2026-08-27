<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
$conexion = $conn;
$tarea_id = intval($_GET['id'] ?? 0);

if ($tarea_id > 0) {
    // Cargar Tarea
    $res = mysqli_query($conexion, "SELECT * FROM tareas WHERE id = $tarea_id");
    $tarea = mysqli_fetch_assoc($res);

    if ($tarea) {
        // Cargar Adjuntos
        $adjuntos = [];
        $res_adj = mysqli_query($conexion, "SELECT * FROM tarea_adjuntos WHERE tarea_id = $tarea_id ORDER BY id DESC");
        while ($a = mysqli_fetch_assoc($res_adj)) {
            $adjuntos[] = $a;
        }

        // Cargar Comentarios con nombre de usuario
        $comentarios = [];
        $res_com = mysqli_query($conexion, "SELECT c.*, u.usuario AS usuario_nombre 
                                           FROM tarea_comentarios c 
                                           LEFT JOIN usuarios u ON c.usuario_id = u.id 
                                           WHERE c.tarea_id = $tarea_id 
                                           ORDER BY c.fecha_creacion DESC");
        while ($c = mysqli_fetch_assoc($res_com)) {
            $comentarios[] = $c;
        }

        echo json_encode(['success' => true, 'tarea' => $tarea, 'adjuntos' => $adjuntos, 'comentarios' => $comentarios]);
        exit;
    }
}

echo json_encode(['success' => false, 'msg' => 'Tarea no encontrada']);