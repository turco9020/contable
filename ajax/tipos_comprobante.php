<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? '';

if($accion == 'listar'){

    $res = $conn->query("SELECT * FROM tipos_comprobante");

    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(["data"=>$data]);
    exit;
}

if($accion == 'guardar'){

    $id = $_POST['id'] ?? '';
    $nombre = strtoupper($_POST['nombre'] ?? '');

    if($id){
        $conn->query("UPDATE tipos_comprobante SET nombre='$nombre' WHERE id=$id");
    } else {
        $conn->query("INSERT INTO tipos_comprobante (nombre) VALUES ('$nombre')");
    }

    echo json_encode(["status"=>"ok"]);
    exit;
}

if($accion == 'eliminar'){

    $id = $_POST['id'];

    $conn->query("DELETE FROM tipos_comprobante WHERE id=$id");

    echo json_encode(["status"=>"ok"]);
    exit;
}