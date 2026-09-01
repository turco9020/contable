<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// =========================
// SALDO DISPONIBLE TOTAL (SOLO CAJAS ACTIVAS)
// =========================
$sql = "
SELECT
    IFNULL(
        SUM(
            CASE
                WHEN m.tipo = 'INGRESO' THEN m.importe
                WHEN m.tipo = 'EGRESO' THEN -m.importe
                ELSE 0
            END
        ), 0
    ) AS saldo
FROM movimientos_caja m
INNER JOIN cajas c ON c.id = m.caja_id
WHERE c.activa = 1
";
$r = $conn->query($sql);
$saldo = (float)($r->fetch_assoc()['saldo'] ?? 0);

// =========================
// GASTOS MES ACTUAL Y ANTERIOR
// =========================
$sqlGastos = "
SELECT 
    IFNULL(SUM(CASE WHEN MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) THEN total ELSE 0 END), 0) AS mes_actual,
    IFNULL(SUM(CASE WHEN MONTH(fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN total ELSE 0 END), 0) AS mes_anterior
FROM gastos
";
$rGastos = $conn->query($sqlGastos)->fetch_assoc();
$gastosMes = (float)($rGastos['mes_actual'] ?? 0);
$gastosMesAnterior = (float)($rGastos['mes_anterior'] ?? 0);


// =========================
// VENTAS MES ACTUAL Y ANTERIOR
// =========================
$sqlVentas = "
SELECT 
    IFNULL(SUM(CASE WHEN MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) THEN total ELSE 0 END), 0) AS mes_actual,
    IFNULL(SUM(CASE WHEN MONTH(fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN total ELSE 0 END), 0) AS mes_anterior
FROM facturas_venta
";
$rVentas = $conn->query($sqlVentas)->fetch_assoc();
$ventasMes = (float)($rVentas['mes_actual'] ?? 0);
$ventasMesAnterior = (float)($rVentas['mes_anterior'] ?? 0);


// RENTABILIDAD NETA (VENTAS - GASTOS)
$rentabilidadMes = $ventasMes - $gastosMes;
$rentabilidadMesAnterior = $ventasMesAnterior - $gastosMesAnterior;


// =========================
// DIFERENCIA DE IVA Y RETENCIONES (MES ACTUAL)
// =========================
$sqlIvaVentas = "SELECT IFNULL(SUM(iva),0) AS total FROM facturas_venta WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE())";
$sqlIvaGastos = "SELECT IFNULL(SUM(iva),0) AS total FROM gastos WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE())";
$sqlRetenciones = "SELECT IFNULL(SUM(importe),0) AS total FROM retenciones_venta WHERE MONTH(fecha_retencion)=MONTH(CURDATE()) AND YEAR(fecha_retencion)=YEAR(CURDATE())";

$ivaVentas = (float)($conn->query($sqlIvaVentas)->fetch_assoc()['total'] ?? 0);
$ivaGastos = (float)($conn->query($sqlIvaGastos)->fetch_assoc()['total'] ?? 0);
$diferenciaIva = $ivaVentas - $ivaGastos;
$retencionesMes = (float)($conn->query($sqlRetenciones)->fetch_assoc()['total'] ?? 0);


// =========================
// CHEQUES EMITIDOS A CUBRIR
// =========================
$sqlChequesMes = "
SELECT IFNULL(SUM(importe), 0) AS total 
FROM cheques 
WHERE estado = 'EMITIDO' 
  AND tipo IN ('PROPIO', 'ECHEQ_PROPIO')
  AND MONTH(fecha_pago) = MONTH(CURDATE()) 
  AND YEAR(fecha_pago) = YEAR(CURDATE())
";
$chequesEmitidosMes = (float)($conn->query($sqlChequesMes)->fetch_assoc()['total'] ?? 0);

$sqlChequesTotal = "
SELECT IFNULL(SUM(importe), 0) AS total 
FROM cheques 
WHERE estado = 'EMITIDO' 
  AND tipo IN ('PROPIO', 'ECHEQ_PROPIO')
";
$chequesEmitidosTotal = (float)($conn->query($sqlChequesTotal)->fetch_assoc()['total'] ?? 0);


// =========================
// CONTROL DE RUBROS (MES, MES ANTERIOR Y ANUAL)
// =========================

// 1. Top Rubros del Mes
$sqlRubrosMes = "
SELECT 
    c.nombre, 
    IFNULL(SUM(g.total), 0) AS monto
FROM gastos g
INNER JOIN categorias c ON c.id = g.categoria_id
WHERE MONTH(g.fecha) = MONTH(CURDATE()) AND YEAR(g.fecha) = YEAR(CURDATE())
GROUP BY c.id, c.nombre
ORDER BY monto DESC
LIMIT 8
";
$rRubrosMes = $conn->query($sqlRubrosMes);
$rubrosMes = [];
if ($rRubrosMes) {
    while($row = $rRubrosMes->fetch_assoc()){
        $pct = $gastosMes > 0 ? round(($row['monto'] / $gastosMes) * 100, 1) : 0;
        $rubrosMes[] = [
            "nombre" => $row["nombre"],
            "monto" => (float)$row["monto"],
            "porcentaje" => $pct
        ];
    }
}

// 2. Top Rubros Mes Anterior (NUEVO AGREGADO)
$sqlRubrosMesAnterior = "
SELECT 
    c.nombre, 
    IFNULL(SUM(g.total), 0) AS monto
FROM gastos g
INNER JOIN categorias c ON c.id = g.categoria_id
WHERE MONTH(g.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
  AND YEAR(g.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
GROUP BY c.id, c.nombre
ORDER BY monto DESC
LIMIT 8
";
$rRubrosMesAnterior = $conn->query($sqlRubrosMesAnterior);
$rubrosMesAnterior = [];
if ($rRubrosMesAnterior) {
    while($row = $rRubrosMesAnterior->fetch_assoc()){
        $pct = $gastosMesAnterior > 0 ? round(($row['monto'] / $gastosMesAnterior) * 100, 1) : 0;
        $rubrosMesAnterior[] = [
            "nombre" => $row["nombre"],
            "monto" => (float)$row["monto"],
            "porcentaje" => $pct
        ];
    }
}

// 3. Top Rubros Anual
$sqlGastoAnual = "SELECT IFNULL(SUM(total), 0) AS total FROM gastos WHERE YEAR(fecha) = YEAR(CURDATE())";
$gastoAnualTotal = (float)($conn->query($sqlGastoAnual)->fetch_assoc()['total'] ?? 0);

$sqlRubrosAnual = "
SELECT 
    c.nombre, 
    IFNULL(SUM(g.total), 0) AS monto
FROM gastos g
INNER JOIN categorias c ON c.id = g.categoria_id
WHERE YEAR(g.fecha) = YEAR(CURDATE())
GROUP BY c.id, c.nombre
ORDER BY monto DESC
LIMIT 8
";
$rRubrosAnual = $conn->query($sqlRubrosAnual);
$rubrosAnual = [];
if ($rRubrosAnual) {
    while($row = $rRubrosAnual->fetch_assoc()){
        $pct = $gastoAnualTotal > 0 ? round(($row['monto'] / $gastoAnualTotal) * 100, 1) : 0;
        $rubrosAnual[] = [
            "nombre" => $row["nombre"],
            "monto" => (float)$row["monto"],
            "porcentaje" => $pct
        ];
    }
}


// =========================
// PERÍODO PARA CENTROS DE COSTO
// =========================
$periodoCentros = $_GET['periodo_centros'] ?? 'actual';

switch ($periodoCentros) {
    case 'pasado':
        $filtroGastosCentro = "WHERE MONTH(g.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(g.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        $filtroVentasCentro = "WHERE MONTH(v.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(v.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        break;

    case 'anual':
        $filtroGastosCentro = "WHERE YEAR(g.fecha) = YEAR(CURDATE())";
        $filtroVentasCentro = "WHERE YEAR(v.fecha) = YEAR(CURDATE())";
        break;

    case 'actual':
    default:
        $filtroGastosCentro = "WHERE MONTH(g.fecha) = MONTH(CURDATE()) AND YEAR(g.fecha) = YEAR(CURDATE())";
        $filtroVentasCentro = "WHERE MONTH(v.fecha) = MONTH(CURDATE()) AND YEAR(v.fecha) = YEAR(CURDATE())";
        break;
}

// =========================
// GASTOS POR CENTRO
// =========================
$sqlCentros = "
SELECT
    c.id,
    c.nombre,
    IFNULL(SUM(g.total), 0) AS total
FROM centros_costos c
INNER JOIN gastos g ON g.centro_costo_id = c.id
{$filtroGastosCentro}
GROUP BY c.id, c.nombre
HAVING total > 0
ORDER BY total DESC
";

$rCentros = $conn->query($sqlCentros);
$centros = [];
if ($rCentros) {
    while($row = $rCentros->fetch_assoc()){
        $centros[] = [
            "id"     => (int)$row["id"],
            "nombre" => $row["nombre"],
            "total"  => (float)$row["total"]
        ];
    }
}

// =========================
// VENTAS POR CENTRO
// =========================
$sqlVentasCentro = "
SELECT 
    c.id, 
    c.nombre, 
    IFNULL(SUM(v.total), 0) AS total
FROM centros_costos c
INNER JOIN facturas_venta v ON v.centro_costo_id = c.id
{$filtroVentasCentro}
GROUP BY c.id, c.nombre
HAVING total > 0
ORDER BY total DESC
";

$rVentasC = $conn->query($sqlVentasCentro);
$ventasCentro = [];

if ($rVentasC) {
    while ($row = $rVentasC->fetch_assoc()) {
        $ventasCentro[] = [
            "id"     => (int)$row["id"],
            "nombre" => $row["nombre"],
            "total"  => (float)$row["total"]
        ];
    }
}

// =========================
// CHEQUES A VENCER
// =========================
$sqlCheques = "
SELECT id, nro_cheque AS numero, beneficiario AS banco, importe, fecha_pago AS fecha_vencimiento
FROM cheques
WHERE estado IN ('RECIBIDO', 'EMITIDO') AND fecha_pago >= CURDATE()
ORDER BY fecha_pago ASC
LIMIT 5
";
$rCheques = $conn->query($sqlCheques);
$cheques = [];
if ($rCheques) {
    while($row = $rCheques->fetch_assoc()){
        $cheques[] = [
            "id" => $row["id"],
            "numero" => $row["numero"],
            "banco" => $row["banco"],
            "importe" => (float)$row["importe"],
            "fecha_vencimiento" => date('d/m/Y', strtotime($row["fecha_vencimiento"]))
        ];
    }
}

// =========================
// AGENDA DE VENCIMIENTOS PARA EL DASHBOARD (MES CORRIENTE + VENCIDOS ANTERIORES)
// =========================
$sqlVencimientos = "
SELECT id, titulo AS servicio, monto AS importe, fecha_vencimiento, estado
FROM vencimientos 
WHERE estado = 'PENDIENTE'
  AND (
    fecha_vencimiento < CURRENT_DATE() 
    OR (
      MONTH(fecha_vencimiento) = MONTH(CURRENT_DATE()) 
      AND YEAR(fecha_vencimiento) = YEAR(CURRENT_DATE())
    )
  )
ORDER BY fecha_vencimiento ASC 
LIMIT 15
";

$rVencimientos = $conn->query($sqlVencimientos);
$vencimientos = [];

if ($rVencimientos) {
    while($row = $rVencimientos->fetch_assoc()){
        // Determina si está vencido comparando con la fecha actual
        $esVencido = (strtotime($row["fecha_vencimiento"]) < strtotime(date('Y-m-d')));
        
        $vencimientos[] = [
            "id"                => $row["id"],
            "servicio"          => $row["servicio"],
            "importe"           => (float)$row["importe"],
            "estado"            => $esVencido ? 'VENCIDO' : 'PENDIENTE',
            "fecha_vencimiento" => date('d/m/Y', strtotime($row["fecha_vencimiento"]))
        ];
    }
}

// =========================
// FACTURAS PENDIENTES DE COBRO
// =========================
$sqlFacturas = "
SELECT f.id, f.nro_factura AS numero, c.nombre AS cliente, f.total, f.fecha_vencimiento
FROM facturas_venta f
LEFT JOIN clientes c ON c.id = f.cliente_id
WHERE f.estado = 'DEBE'
ORDER BY f.fecha_vencimiento ASC
LIMIT 5
";
$rFacturas = $conn->query($sqlFacturas);
$facturasCobrar = [];
if ($rFacturas) {
    while($row = $rFacturas->fetch_assoc()){
        $facturasCobrar[] = [
            "id" => $row["id"],
            "numero" => $row["numero"],
            "cliente" => $row["cliente"] ?? 'Cliente Sin Nombre',
            "total" => (float)$row["total"],
            "fecha_vencimiento" => $row["fecha_vencimiento"] ? date('d/m/Y', strtotime($row["fecha_vencimiento"])) : 'Sin fecha'
        ];
    }
}


// =========================
// RESPUESTA JSON COMPLETA
// =========================
echo json_encode([
    "saldo"                 => $saldo,
    "gastos_mes"            => $gastosMes,
    "gastos_mes_anterior"   => $gastosMesAnterior,
    "ventas_mes"            => $ventasMes,
    "ventas_mes_anterior"   => $ventasMesAnterior,
    "rentabilidad_mes"      => $rentabilidadMes,
    "rentabilidad_mes_anterior" => $rentabilidadMesAnterior,
    "diferencia_iva"        => $diferenciaIva,
    "retenciones_mes"       => $retencionesMes,
    "cheques_emitidos_mes"  => $chequesEmitidosMes,
    "cheques_emitidos_total"=> $chequesEmitidosTotal,
    "rubros_mes"            => $rubrosMes,
    "rubros_mes_anterior"   => $rubrosMesAnterior, 
    "rubros_anual"          => $rubrosAnual,
    "centros"               => $centros,
    "ventas_centros"        => $ventasCentro,
    "cheques"               => $cheques,
    "vencimientos"          => $vencimientos, // 👈 AHORA SÍ INCLUIDO
    "facturas_cobrar"       => $facturasCobrar
]);