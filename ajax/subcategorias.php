<?php
include '../config/database.php';

if($_GET['accion']=='listar'){

    $r=$conn->query("
    SELECT s.*, c.nombre as categoria 
    FROM subcategorias s
    LEFT JOIN categorias c ON c.id=s.categoria_id
    ");

    $data=[];
    while($row=$r->fetch_assoc()) $data[]=$row;

    echo json_encode(["data"=>$data]);
    exit;
}

if($_GET['accion']=='guardar'){

    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $categoria_id = $_POST['categoria_id'] ?? '';

    if($id != ''){
        $conn->query("UPDATE subcategorias SET 
        nombre='$nombre',
        categoria_id='$categoria_id'
        WHERE id=$id");
    } else {
        $conn->query("INSERT INTO subcategorias(nombre,categoria_id)
        VALUES('$nombre','$categoria_id')");
    }

    echo "OK";
    exit;
}

if($_GET['accion']=='eliminar'){
    $id = $_POST['id'];
    $conn->query("DELETE FROM subcategorias WHERE id=$id");
    echo "OK";
    exit;
}