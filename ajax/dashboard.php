<?php

require_once '../config/database.php';

header('Content-Type: application/json');

// =========================
// SALDO DISPONIBLE
// =========================

$sql = "
SELECT
    IFNULL(
        SUM(
            CASE
                WHEN tipo='INGRESO' THEN importe
                WHEN tipo='TRANSFERENCIA' THEN importe
                ELSE -importe
            END
        ),0
    ) saldo
FROM movimientos_caja
";

$r = $conn->query($sql);
$saldo = (float)($r->fetch_assoc()['saldo'] ?? 0);


// =========================
// GASTOS HOY
// =========================

$sql = "
SELECT IFNULL(SUM(total),0) total
FROM gastos
WHERE fecha = CURDATE()
";

$r = $conn->query($sql);
$gastosHoy = (float)($r->fetch_assoc()['total'] ?? 0);


// =========================
// GASTOS MES
// =========================

$sql = "
SELECT IFNULL(SUM(total),0) total
FROM gastos
WHERE YEAR(fecha)=YEAR(CURDATE())
AND MONTH(fecha)=MONTH(CURDATE())
";

$r = $conn->query($sql);
$gastosMes = (float)($r->fetch_assoc()['total'] ?? 0);


// =========================
// GASTOS POR CENTRO
// =========================

$sql = "
SELECT

    c.id,
    c.nombre,

    SUM(g.total) total

FROM gastos g

INNER JOIN centros_costos c
ON c.id = g.centro_costo_id

WHERE
YEAR(g.fecha)=YEAR(CURDATE())
AND MONTH(g.fecha)=MONTH(CURDATE())

GROUP BY c.id,c.nombre

ORDER BY total DESC
";

$r = $conn->query($sql);

$centros = [];

while($row = $r->fetch_assoc()){

    $centros[] = [
        "id"=>$row["id"],
        "nombre"=>$row["nombre"],
        "total"=>(float)$row["total"]
    ];

}

// =========================
// CATEGORÍA CON MAYOR GASTO
// =========================

$sql = "
SELECT

    c.nombre,

    SUM(g.total) total

FROM gastos g

INNER JOIN categorias c
ON c.id = g.categoria_id

WHERE

YEAR(g.fecha)=YEAR(CURDATE())

AND MONTH(g.fecha)=MONTH(CURDATE())

GROUP BY c.id,c.nombre

ORDER BY total DESC

LIMIT 1
";

$r = $conn->query($sql);

$categoriaTop = $r->fetch_assoc();

$porcentajeCategoria = 0;

if($gastosMes > 0){

    $porcentajeCategoria = round(
        ($categoriaTop['total'] / $gastosMes) * 100,
        1
    );

}

// =========================
// RESPUESTA
// =========================

echo json_encode([

    "saldo"=>$saldo,

    "gastos_hoy"=>$gastosHoy,

    "gastos_mes"=>$gastosMes,

    "centros"=>$centros,

    "categoria_top" => $categoriaTop['nombre'] ?? '-',

    "categoria_total" => (float)($categoriaTop['total'] ?? 0),

    "categoria_porcentaje" => $porcentajeCategoria]);

