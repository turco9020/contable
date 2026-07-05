<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

if($accion == 'listar'){

    $sql = "SELECT o.*, c.nombre as cliente
            FROM obras o
            LEFT JOIN clientes c ON c.id = o.cliente_id";

    $res = $conn->query($sql);

    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(["data"=>$data]);
}

// ================= GUARDAR =================
if($accion == 'guardar'){

    $id = $_POST['id'] ?? '';

    $nombre = $_POST['nombre'];
    $cliente_id = $_POST['cliente_id'] ?: 'NULL';
    $fecha_inicio = $_POST['fecha_inicio'];
    $detalle = $_POST['detalle'];
    $estado = $_POST['estado'];

    if($id){

        $sql = "UPDATE obras SET
            nombre='$nombre',
            cliente_id=$cliente_id,
            fecha_inicio='$fecha_inicio',
            detalle='$detalle',
            estado='$estado'
        WHERE id=$id";

    } else {

        $sql = "INSERT INTO obras (nombre, cliente_id, fecha_inicio, detalle, estado)
        VALUES ('$nombre',$cliente_id,'$fecha_inicio','$detalle','$estado')";
    }

    $conn->query($sql);

    echo "OK";
}

// ================= ELIMINAR =================
if($accion == 'eliminar'){

    $id = $_POST['id'];

    $conn->query("DELETE FROM obras WHERE id=$id");

    echo "OK";
}