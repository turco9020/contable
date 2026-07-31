<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// =======================
// LISTAR TIPOS
// =======================
if($accion == 'listar'){
    header('Content-Type: application/json');
    $data = [];
    
    $res = $conn->query("SELECT id, nombre FROM tipos_retenciones ORDER BY nombre ASC");
    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }
    
    echo json_encode(['data' => $data]);
    exit;
}

// =======================
// GUARDAR / EDITAR
// =======================
if($accion == 'guardar'){
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? '';
    $nombre = $conn->real_escape_string(trim($_POST['nombre']));

    if($id){
        $sql = "UPDATE tipos_retenciones SET nombre='$nombre' WHERE id=$id";
    } else {
        $sql = "INSERT INTO tipos_retenciones (nombre) VALUES ('$nombre')";
    }
    
    if($conn->query($sql)){
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => $conn->error]);
    }
    exit;
}

// =======================
// ELIMINAR
// =======================
if($accion == 'eliminar'){
    header('Content-Type: application/json');
    $id = (int)$_POST['id'];
    
    $sql = "DELETE FROM tipos_retenciones WHERE id=$id";
    
    if($conn->query($sql)){
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => $conn->error]);
    }
    exit;
}