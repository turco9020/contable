<?php

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// LISTAR
if($accion == 'listar'){

    $res = $conn->query("SELECT * FROM cajas ORDER BY nombre");

    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(['data'=>$data]);
    exit;
}

// GUARDAR
if($accion == 'guardar'){

    $id = $_POST['id'] ?? '';
    $nombre = strtoupper(trim($_POST['nombre']));
    $descripcion = trim($_POST['descripcion']);
    $activa = $_POST['activa'] ?? 1;

    if($id){

        $conn->query("
            UPDATE cajas
            SET nombre='$nombre',
                descripcion='$descripcion',
                activa='$activa'
            WHERE id=$id
        ");

    }else{

        $conn->query("
            INSERT INTO cajas(nombre,descripcion,activa)
            VALUES('$nombre','$descripcion','$activa')
        ");
    }

    echo "OK";
    exit;
}

// ELIMINAR
if($accion == 'eliminar'){

    $id = $_POST['id'];

    $conn->query("DELETE FROM cajas WHERE id=$id");

    echo "OK";
    exit;
}