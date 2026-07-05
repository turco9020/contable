<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

if($accion == 'listar'){

    $res = $conn->query("SELECT * FROM medios_pago");

    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(["data"=>$data]);
}

// ================= GUARDAR =================
if($accion == 'guardar'){

    $id = $_POST['id'] ?? '';
    $nombre = strtoupper($_POST['nombre']);

    if($id){

        $conn->query("UPDATE medios_pago SET nombre='$nombre' WHERE id=$id");

    } else {

        $conn->query("INSERT INTO medios_pago (nombre) VALUES ('$nombre')");
    }

    echo "OK";
}

// ================= ELIMINAR =================
if($accion == 'eliminar'){

    $id = $_POST['id'];

    $conn->query("DELETE FROM medios_pago WHERE id=$id");

    echo "OK";
}