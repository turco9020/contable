<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// Captura de variables de sesión del usuario logueado
$usuario_logueado = $_SESSION['id'] ?? 0;
$rol = $_SESSION['rol'] ?? 'user';

// =======================
// LISTAR CAJAS
// =======================
if($accion == 'listar'){
    header('Content-Type: application/json');

    // Si es Admin o Contador, ve todas las cajas del sistema sin restricción
    if (strcasecmp($rol, 'admin') === 0 || strcasecmp($rol, 'contador') === 0) {
        $where = "WHERE 1=1";
    } else {
        // El operador SOLO ve la caja que le pertenece estrictamente a él
        $where = "WHERE c.usuario_id = $usuario_logueado";
    }

    // AJUSTE: u.usuario es el nombre real de tu columna en la tabla usuarios
    $sql = "
        SELECT 
            c.*, 
            u.usuario AS usuario_nombre 
        FROM cajas c
        LEFT JOIN usuarios u ON u.id = c.usuario_id 
        $where 
        ORDER BY c.nombre
    ";

    $res = $conn->query($sql);

    $data = [];
    if ($res) {
        while($row = $res->fetch_assoc()){
            $data[] = $row;
        }
    }

    echo json_encode(['data' => $data]);
    exit;
}

// =======================
// GUARDAR / EDITAR CAJA
// =======================
if($accion == 'guardar'){

    $id = $_POST['id'] ?? '';
    $nombre = $conn->real_escape_string(strtoupper(trim($_POST['nombre'])));
    $descripcion = $conn->real_escape_string(trim($_POST['descripcion']));
    $activa = $_POST['activa'] ?? 1;
    
    // Si viene un usuario_id del formulario se asigna, sino se guarda como NULL (Caja Central)
    $usuario_id = !empty($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : "NULL";

    if($id){
        // EDITAR
        $conn->query("
            UPDATE cajas
            SET nombre='$nombre',
                descripcion='$descripcion',
                activa='$activa',
                usuario_id=$usuario_id
            WHERE id=$id
        ");
    }else{
        // NUEVO
        $conn->query("
            INSERT INTO cajas(nombre, descripcion, activa, usuario_id)
            VALUES('$nombre', '$descripcion', '$activa', $usuario_id)
        ");
    }

    echo "OK";
    exit;
}

// =======================
// ELIMINAR CAJA
// =======================
if($accion == 'eliminar'){

    $id = (int)$_POST['id'];

    $conn->query("DELETE FROM cajas WHERE id=$id");

    echo "OK";
    exit;
}