<?php
include '../config/database.php';

header('Content-Type: application/json');

$r = $conn->query("SELECT id, nombre, cuit FROM proveedores");

$data = [];
while($row = $r->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
exit;