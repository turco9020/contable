<?php
include '../config/database.php';

header('Content-Type: application/json');

$categoria_id = $_GET['categoria_id'] ?? 0;

$r = $conn->query("
SELECT id, nombre 
FROM subcategorias 
WHERE categoria_id = $categoria_id
");

$data=[];
while($row=$r->fetch_assoc()){
    $data[]=$row;
}

echo json_encode($data);
exit;