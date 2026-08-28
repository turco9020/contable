<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$conexion = $conn;
$usuario_id  = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;
$rol_usuario = strtoupper($_SESSION['rol'] ?? '');
$rol_id      = $_SESSION['rol_id'] ?? 0;
$es_admin    = ($rol_usuario === 'ADMIN' || $rol_usuario === 'ADMINISTRADOR' || $rol_id == 1);

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

if (!$usuario_id) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['success' => false, 'msg' => 'Sesión expirada']);
        exit;
    }
    header("Location: index.php?error=no_session");
    exit;
}

// ==========================================
// GUARDAR COMENTARIO VÍA AJAX
// ==========================================
if ($accion === 'comentar_ajax' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar cualquier HTML/Warning previo que rompa el JSON
    if (ob_get_length()) ob_clean(); 
    header('Content-Type: application/json');

    $tarea_id   = intval($_POST['tarea_id'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');

    if ($tarea_id > 0 && !empty($comentario)) {
        $comentario_esc = mysqli_real_escape_string($conexion, $comentario);
        $sql = "INSERT INTO tarea_comentarios (tarea_id, usuario_id, comentario) 
                VALUES ($tarea_id, $usuario_id, '$comentario_esc')";
        
        if (mysqli_query($conexion, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Error SQL: ' . mysqli_error($conexion)]);
        }
    } else {
        echo json_encode(['success' => false, 'msg' => 'Faltan datos obligatorios.']);
    }
    exit;
}

// ==========================================
// 2. EDITAR COMENTARIO VÍA AJAX
// ==========================================
if ($accion === 'editar_comentario_ajax' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $comentario_id = intval($_POST['comentario_id'] ?? 0);
    $nuevo_texto   = trim($_POST['comentario'] ?? '');

    if ($comentario_id > 0 && !empty($nuevo_texto)) {
        $texto_esc = mysqli_real_escape_string($conexion, $nuevo_texto);
        $where = $es_admin ? "WHERE id = $comentario_id" : "WHERE id = $comentario_id AND usuario_id = $usuario_id";
        
        $sql = "UPDATE tarea_comentarios SET comentario = '$texto_esc' $where";
        if (mysqli_query($conexion, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'No tienes permiso o error SQL.']);
        }
    } else {
        echo json_encode(['success' => false, 'msg' => 'Datos inválidos.']);
    }
    exit;
}

// ==========================================
// 3. ELIMINAR COMENTARIO VÍA AJAX
// ==========================================
if ($accion === 'eliminar_comentario_ajax' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $comentario_id = intval($_POST['comentario_id'] ?? 0);

    if ($comentario_id > 0) {
        $where = $es_admin ? "WHERE id = $comentario_id" : "WHERE id = $comentario_id AND usuario_id = $usuario_id";
        if (mysqli_query($conexion, "DELETE FROM tarea_comentarios $where")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Error al eliminar comentario.']);
        }
    }
    exit;
}

// ==========================================
// 4. GUARDAR / EDITAR TAREA
// ==========================================
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tarea_id     = intval($_POST['tarea_id'] ?? 0);
    $titulo       = trim($_POST['titulo'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $prioridad    = $_POST['prioridad'] ?? 'MEDIA';
    $fecha_limite = !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : null;
    $asignado_id  = !empty($_POST['asignado_id']) ? intval($_POST['asignado_id']) : $usuario_id;

    if (empty($titulo)) {
        header("Location: index.php?error=titulo_requerido");
        exit;
    }

    $titulo_esc       = mysqli_real_escape_string($conexion, $titulo);
    $descripcion_esc  = mysqli_real_escape_string($conexion, $descripcion);
    $prioridad_esc    = mysqli_real_escape_string($conexion, $prioridad);
    $fecha_limite_sql = $fecha_limite ? "'" . mysqli_real_escape_string($conexion, $fecha_limite) . "'" : "NULL";

    if ($tarea_id > 0) {
        $sql = "UPDATE tareas SET 
                    titulo = '$titulo_esc', 
                    descripcion = '$descripcion_esc', 
                    prioridad = '$prioridad_esc', 
                    fecha_limite = $fecha_limite_sql";
        if ($es_admin) {
            $sql .= ", asignado_id = $asignado_id";
        }
        $sql .= " WHERE id = $tarea_id";
        if (!$es_admin) {
            $sql .= " AND (creador_id = $usuario_id OR asignado_id = $usuario_id)";
        }
        mysqli_query($conexion, $sql);
    } else {
        $sql = "INSERT INTO tareas (titulo, descripcion, creador_id, asignado_id, prioridad, fecha_limite) 
                VALUES ('$titulo_esc', '$descripcion_esc', $usuario_id, $asignado_id, '$prioridad_esc', $fecha_limite_sql)";
        mysqli_query($conexion, $sql);
    }
    header("Location: index.php?status=success");
    exit;
}

// ==========================================
// 5. ELIMINAR TAREA
// ==========================================
if ($accion === 'eliminar' && isset($_GET['id'])) {
    $tarea_id = intval($_GET['id']);
    $res_adj = mysqli_query($conexion, "SELECT ruta_archivo FROM tarea_adjuntos WHERE tarea_id = $tarea_id");
    while ($adj = mysqli_fetch_assoc($res_adj)) {
        $file_path = __DIR__ . '/../../' . $adj['ruta_archivo'];
        if (file_exists($file_path)) { @unlink($file_path); }
    }
    $where = $es_admin ? "WHERE id = $tarea_id" : "WHERE id = $tarea_id AND creador_id = $usuario_id";
    mysqli_query($conexion, "DELETE FROM tareas $where");
    header("Location: index.php?status=deleted");
    exit;
}

// ==========================================
// 6. ADJUNTO (SUBIR Y ELIMINAR)
// ==========================================
if ($accion === 'subir_adjunto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tarea_id = intval($_POST['tarea_id'] ?? 0);
    if ($tarea_id > 0 && isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $target_dir = __DIR__ . '/../../uploads/tareas/';
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_name = $_FILES['archivo']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_filename = uniqid('tarea_' . $tarea_id . '_') . '.' . $ext;
        $target_file = $target_dir . $new_filename;
        $db_path = 'uploads/tareas/' . $new_filename;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $target_file)) {
            $nombre_esc = mysqli_real_escape_string($conexion, $file_name);
            $ruta_esc   = mysqli_real_escape_string($conexion, $db_path);
            mysqli_query($conexion, "INSERT INTO tarea_adjuntos (tarea_id, usuario_id, nombre_archivo, ruta_archivo) VALUES ($tarea_id, $usuario_id, '$nombre_esc', '$ruta_esc')");
        }
    }
    header("Location: index.php?open_id=" . $tarea_id);
    exit;
}

if ($accion === 'eliminar_adjunto' && isset($_GET['id'])) {
    $adjunto_id = intval($_GET['id']);
    $res = mysqli_query($conexion, "SELECT * FROM tarea_adjuntos WHERE id = $adjunto_id");
    if ($adj = mysqli_fetch_assoc($res)) {
        $tarea_id = $adj['tarea_id'];
        $file_path = __DIR__ . '/../../' . $adj['ruta_archivo'];
        if (file_exists($file_path)) { @unlink($file_path); }
        mysqli_query($conexion, "DELETE FROM tarea_adjuntos WHERE id = $adjunto_id");
        header("Location: index.php?open_id=" . $tarea_id);
        exit;
    }
}

// ==========================================
// 7. OBTENER TAREA VÍA AJAX
// ==========================================
if ($accion === 'obtener') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    
    $tarea_id = intval($_GET['id'] ?? 0);
    if ($tarea_id > 0) {
        // Marcamos la fecha/hora en la que el usuario abrió la tarea
        mysqli_query($conexion, "UPDATE tareas SET ultima_vista_en = NOW() WHERE id = $tarea_id");

        $sql = "SELECT t.*, u.usuario AS asignado_nombre 
                FROM tareas t 
                LEFT JOIN usuarios u ON t.asignado_id = u.id 
                WHERE t.id = $tarea_id";
        $res = mysqli_query($conexion, $sql);
        if ($res && $t = mysqli_fetch_assoc($res)) {
            echo json_encode(['success' => true, 'tarea' => $t]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tarea no encontrada.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID de tarea inválido.']);
    }
    exit;
}

header("Location: index.php");
exit;