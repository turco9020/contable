<?php
session_start();
include '../config/database.php';

ob_clean();

/* LISTAR */
if($_GET['accion'] == 'listar'){

    header('Content-Type: application/json');

    $result = $conn->query("SELECT * FROM clientes");

    $data = [];

    while($row = $result->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(["data"=>$data]);
    exit;
}

/* GUARDAR */
if($_GET['accion'] == 'guardar'){

    $id = $_POST['id'] ?? '';
    $usuario = $_SESSION['id'];

    $nombre = $_POST['nombre'];
    $cuit = $_POST['cuit'];
    $condicion = $_POST['condicion_fiscal'];
    $direccion = $_POST['direccion'];
    $localidad = $_POST['localidad'];
    $provincia = $_POST['provincia'];
    $cp = $_POST['cp'];
    $whatsapp = $_POST['whatsapp'];
    $telefono = $_POST['telefono'];
    $contacto = $_POST['contacto'];
    $observaciones = $_POST['observaciones'];

    if($id){
        $sql = "UPDATE clientes SET 
        nombre='$nombre',
        cuit='$cuit',
        condicion_fiscal='$condicion',
        direccion='$direccion',
        localidad='$localidad',
        provincia='$provincia',
        cp='$cp',
        whatsapp='$whatsapp',
        telefono='$telefono',
        contacto='$contacto',
        observaciones='$observaciones'
        WHERE id=$id";
    } else {
        $sql = "INSERT INTO clientes 
        (nombre,cuit,condicion_fiscal,direccion,localidad,provincia,cp,whatsapp,telefono,contacto,observaciones,usuario_id)
        VALUES 
        ('$nombre','$cuit','$condicion','$direccion','$localidad','$provincia','$cp','$whatsapp','$telefono','$contacto','$observaciones',$usuario)";
    }

    $conn->query($sql);

    echo "OK";
    exit;
}

/* ELIMINAR */
if($_GET['accion'] == 'eliminar'){
    $id = $_POST['id'];
    $conn->query("DELETE FROM clientes WHERE id=$id");
    echo "OK";
    exit;
}