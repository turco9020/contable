<?php
require_once __DIR__ . '/../../config/database.php';
$conexion = $conn;

header('Content-Type: application/json');

$padre_id = isset($_GET['padre_id']) ? intval($_GET['padre_id']) : 0;

if ($padre_id <= 0) {
    echo json_encode(['data' => []]);
    exit;
}

// Devuelve las cuotas hijas o el registro padre
$query = "SELECT v.*, p.nombre AS proveedor_nombre 
          FROM vencimientos v 
          LEFT JOIN proveedores p ON v.proveedor_id = p.id 
          WHERE v.vencimiento_padre_id = $padre_id OR v.id = $padre_id
          ORDER BY v.nro_cuota ASC";

$res = mysqli_query($conexion, $query);
$cuotas = [];

if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $cuotas[] = $r;
    }
}

echo json_encode(['data' => $cuotas]);