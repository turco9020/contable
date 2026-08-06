<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

$query = "SELECT v.*, 
                 p.nombre AS proveedor_nombre, 
                 o.nombre AS obra_nombre,
                 DATEDIFF(v.fecha_vencimiento, CURDATE()) AS dias_restantes
          FROM vencimientos v
          LEFT JOIN proveedores p ON v.proveedor_id = p.id
          LEFT JOIN obras o ON v.obra_id = o.id
          ORDER BY v.fecha_vencimiento ASC";

$result = $conn->query($query);
$data = [];

$totVencidos = 0;
$totProximos = 0;
$totPendientes = 0;
$totPagados = 0;

while ($row = $result->fetch_assoc()) {
    $monto = (float)$row['monto'];
    $esVencido = ($row['estado'] === 'PENDIENTE' && $row['dias_restantes'] < 0);
    $esProximo = ($row['estado'] === 'PENDIENTE' && $row['dias_restantes'] >= 0 && $row['dias_restantes'] <= (int)$row['dias_aviso']);

    if ($row['estado'] === 'PENDIENTE') {
        $totPendientes += $monto;
        if ($esVencido) $totVencidos += $monto;
        if ($esProximo) $totProximos += $monto;
    } else if ($row['estado'] === 'PAGADO') {
        // Acumular si se pagó en el mes actual
        if (!empty($row['fecha_pago']) && date('Y-m', strtotime($row['fecha_pago'])) === date('Y-m')) {
            $totPagados += $monto;
        }
    }

    $item = [
        'id' => $row['id'],
        'titulo' => htmlspecialchars($row['titulo']),
        'descripcion' => htmlspecialchars($row['descripcion'] ?? ''),
        'monto' => $row['monto'],
        'monto_fmt' => number_format($monto, 2, ',', '.'),
        'fecha_vencimiento' => $row['fecha_vencimiento'],
        'fecha_vencimiento_fmt' => date('d/m/Y', strtotime($row['fecha_vencimiento'])),
        'categoria' => htmlspecialchars($row['categoria']),
        'proveedor_id' => $row['proveedor_id'],
        'proveedor' => htmlspecialchars($row['proveedor_nombre'] ?? ''),
        'obra_id' => $row['obra_id'],
        'obra' => htmlspecialchars($row['obra_nombre'] ?? ''),
        'estado' => $row['estado'],
        'dias_aviso' => $row['dias_aviso'],
        'archivo' => $row['archivo'],
        'es_vencido' => $esVencido,
        'es_proximo' => $esProximo,
        'id_json' => htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8')
    ];

    $data[] = $item;
}

echo json_encode([
    'status' => true,
    'resumen' => [
        'vencidos' => number_format($totVencidos, 2, ',', '.'),
        'proximos' => number_format($totProximos, 2, ',', '.'),
        'pendientes' => number_format($totPendientes, 2, ',', '.'),
        'pagados' => number_format($totPagados, 2, ',', '.')
    ],
    'data' => $data
]);