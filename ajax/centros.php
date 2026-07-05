<?php
include '../config/database.php';

if($_GET['accion']=='listar'){
    $r=$conn->query("SELECT * FROM centros_costos");
    $data=[];
    while($row=$r->fetch_assoc()) $data[]=$row;
    echo json_encode(["data"=>$data]);
    exit;
}

if($_GET['accion']=='guardar'){

    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';

    if($id != ''){
        $conn->query("UPDATE centros_costos SET nombre='$nombre' WHERE id=$id");
    } else {
        $conn->query("INSERT INTO centros_costos(nombre) VALUES('$nombre')");
    }

    echo "OK";
    exit;
}

if($_GET['accion']=='eliminar'){
    $id = $_POST['id'];
    $conn->query("DELETE FROM centros_costos WHERE id=$id");
    echo "OK";
    exit;
}