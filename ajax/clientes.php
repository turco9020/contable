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

    $id = (int)($_POST['id'] ?? 0);
    $usuario = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 'NULL';

    $nombre        = $conn->real_escape_string($_POST['nombre'] ?? '');
    $cuit          = $conn->real_escape_string($_POST['cuit'] ?? '');
    $condicion     = $conn->real_escape_string($_POST['condicion_fiscal'] ?? '');
    $direccion     = $conn->real_escape_string($_POST['direccion'] ?? '');
    $localidad     = $conn->real_escape_string($_POST['localidad'] ?? '');
    $provincia     = $conn->real_escape_string($_POST['provincia'] ?? '');
    $cp            = $conn->real_escape_string($_POST['cp'] ?? '');
    $whatsapp      = $conn->real_escape_string($_POST['whatsapp'] ?? '');
    $telefono      = $conn->real_escape_string($_POST['telefono'] ?? '');
    $contacto      = $conn->real_escape_string($_POST['contacto'] ?? '');
    $email         = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $red_social    = $conn->real_escape_string(trim($_POST['red_social'] ?? ''));
    $sitio_web     = $conn->real_escape_string(trim($_POST['sitio_web'] ?? ''));
    $observaciones = $conn->real_escape_string($_POST['observaciones'] ?? '');

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
        email='$email',
        red_social='$red_social',
        sitio_web='$sitio_web',
        observaciones='$observaciones'
        WHERE id=$id";
    } else {
        $sql = "INSERT INTO clientes
        (nombre,cuit,condicion_fiscal,direccion,localidad,provincia,cp,whatsapp,telefono,contacto,email,red_social,sitio_web,observaciones,usuario_id)
        VALUES
        ('$nombre','$cuit','$condicion','$direccion','$localidad','$provincia','$cp','$whatsapp','$telefono','$contacto','$email','$red_social','$sitio_web','$observaciones',$usuario)";
    }

    $conn->query($sql);

    echo "OK";
    exit;
}

/* ELIMINAR */
if($_GET['accion'] == 'eliminar'){
    $id = (int)($_POST['id'] ?? 0);
    $conn->query("DELETE FROM clientes WHERE id=$id");
    echo "OK";
    exit;
}