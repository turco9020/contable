<?php
include '../config/database.php';

if($_GET['accion']=='listar'){
    $r=$conn->query("SELECT * FROM categorias");
    $data=[];
    while($row=$r->fetch_assoc()) $data[]=$row;
    echo json_encode(["data"=>$data]);
    exit;
}

if($_GET['accion']=='guardar'){

    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';

    if($id != ''){
        $conn->query("UPDATE categorias SET nombre='$nombre' WHERE id=$id");
    } else {
        $conn->query("INSERT INTO categorias(nombre) VALUES('$nombre')");
    }

    echo "OK";
    exit;
}

if($_GET['accion']=='eliminar'){
    $id = $_POST['id'];
    $conn->query("DELETE FROM categorias WHERE id=$id");
    echo "OK";
    exit;
}