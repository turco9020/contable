<?php
error_reporting(0);
ini_set('display_errors', 0);

// Cargar la conexión desde /config/ y la sesión desde /includes/
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

$tipo = $_GET['tipo_reporte'] ?? 'gastos_generales';

// Recoger filtros
$f_desde = $_GET['fecha_desde'] ?? '';
$f_hasta = $_GET['fecha_hasta'] ?? '';
$obra_id = $_GET['obra_id'] ?? '';
$centro_id = $_GET['centro_costo_id'] ?? '';
$cat_id = $_GET['categoria_id'] ?? '';
$subcat_id = $_GET['subcategoria_id'] ?? '';
$cliente_id = $_GET['cliente_id'] ?? '';
$prov_id = $_GET['proveedor_id'] ?? '';
$caja_id = $_GET['caja_id'] ?? '';
$usuario_id = $_GET['usuario_id'] ?? '';
$tipo_mov = $_GET['tipo_movimiento'] ?? '';

$response = [
    'status' => true,
    'columns' => [],
    'data' => []
];

switch ($tipo) {

    // ==========================================
    // 1. VENTAS Y FACTURACIÓN (facturas_venta)
    // ==========================================
    case 'ventas_generales':
    case 'ventas_cliente':
    case 'ventas_obra':
    case 'ventas_centro':
        $w = ["1=1"];
        if (!empty($f_desde)) $w[] = "fv.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta)) $w[] = "fv.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id)) $w[] = "fv.obra_id = " . intval($obra_id);
        if (!empty($centro_id)) $w[] = "fv.centro_costo_id = " . intval($centro_id);
        if (!empty($cliente_id)) $w[] = "fv.cliente_id = " . intval($cliente_id);
        if (!empty($usuario_id)) $w[] = "fv.usuario_id = " . intval($usuario_id);
        $whereSql = implode(" AND ", $w);

        if ($tipo === 'ventas_cliente') {
            $q = "SELECT cl.nombre AS Cliente, COUNT(fv.id) AS Facturas, SUM(fv.neto) AS Neto, SUM(fv.iva) AS IVA, SUM(fv.total) AS Total
                  FROM facturas_venta fv
                  LEFT JOIN clientes cl ON fv.cliente_id = cl.id
                  WHERE $whereSql GROUP BY fv.cliente_id ORDER BY Total DESC";
            $response['columns'] = ['Cliente', 'Cant. Facturas', 'Neto ($)', 'IVA ($)', 'Total Sold ($)'];
        } elseif ($tipo === 'ventas_obra') {
            $q = "SELECT o.nombre AS Obra, COUNT(fv.id) AS Facturas, SUM(fv.total) AS Total
                  FROM facturas_venta fv
                  LEFT JOIN obras o ON fv.obra_id = o.id
                  WHERE $whereSql GROUP BY fv.obra_id ORDER BY Total DESC";
            $response['columns'] = ['Obra / Proyecto', 'Cant. Facturas', 'Total Facturado ($)'];
        } elseif ($tipo === 'ventas_centro') {
            $q = "SELECT cc.nombre AS Centro_Costo, COUNT(fv.id) AS Facturas, SUM(fv.total) AS Total
                  FROM facturas_venta fv
                  LEFT JOIN centros_costos cc ON fv.centro_costo_id = cc.id
                  WHERE $whereSql GROUP BY fv.centro_costo_id ORDER BY Total DESC";
            $response['columns'] = ['Centro de Costo', 'Cant. Facturas', 'Total Facturado ($)'];
        } else {
            $q = "SELECT fv.fecha, CONCAT(fv.punto_venta, '-', fv.nro_factura) AS Nro_Factura, cl.nombre AS Cliente, 
                         o.nombre AS Obra, cc.nombre AS Centro, fv.neto, fv.iva, fv.total, fv.estado
                  FROM facturas_venta fv
                  LEFT JOIN clientes cl ON fv.cliente_id = cl.id
                  LEFT JOIN obras o ON fv.obra_id = o.id
                  LEFT JOIN centros_costos cc ON fv.centro_costo_id = cc.id
                  WHERE $whereSql ORDER BY fv.fecha DESC";
            $response['columns'] = ['Fecha', 'N° Factura', 'Cliente', 'Obra', 'Centro Costo', 'Neto ($)', 'IVA ($)', 'Total ($)', 'Estado'];
        }

        $res = $conn->query($q);
        if (!$res) {
            echo json_encode(['status' => false, 'message' => 'Error SQL: ' . $conn->error]);
            exit;
        }
        $totalG = 0;
        while ($r = $res->fetch_assoc()) {
            if (isset($r['Total'])) $totalG += $r['Total'];
            if (isset($r['total'])) $totalG += $r['total'];
            $response['data'][] = $r;
        }
        $response['total'] = $totalG;
        break;

    // ==========================================
    // 2. COMPRAS Y GASTOS (gastos)
    // ==========================================
    case 'gastos_generales':
    case 'compras_proveedor':
    case 'compras_obra':
    case 'compras_centro':
        $w = ["1=1"];
        if (!empty($f_desde)) $w[] = "g.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta)) $w[] = "g.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id)) $w[] = "g.obra_id = " . intval($obra_id);
        if (!empty($centro_id)) $w[] = "g.centro_costo_id = " . intval($centro_id);
        if (!empty($cat_id)) $w[] = "g.categoria_id = " . intval($cat_id);
        if (!empty($subcat_id)) $w[] = "g.subcategoria_id = " . intval($subcat_id);
        if (!empty($prov_id)) $w[] = "g.proveedor_id = " . intval($prov_id);
        if (!empty($caja_id)) $w[] = "g.caja_id = " . intval($caja_id);
        if (!empty($usuario_id)) $w[] = "g.usuario_id = " . intval($usuario_id);
        $whereSql = implode(" AND ", $w);

        if ($tipo === 'compras_proveedor') {
            $q = "SELECT pr.nombre AS Proveedor, COUNT(g.id) AS Cant_Gastos, SUM(g.total) AS Total
                  FROM gastos g
                  LEFT JOIN proveedores pr ON g.proveedor_id = pr.id
                  WHERE $whereSql GROUP BY g.proveedor_id ORDER BY Total DESC";
            $response['columns'] = ['Proveedor', 'Cant. Compras', 'Total Comprado ($)'];
        } elseif ($tipo === 'compras_obra') {
            $q = "SELECT o.nombre AS Obra, COUNT(g.id) AS Cant_Gastos, SUM(g.total) AS Total
                  FROM gastos g
                  LEFT JOIN obras o ON g.obra_id = o.id
                  WHERE $whereSql GROUP BY g.obra_id ORDER BY Total DESC";
            $response['columns'] = ['Obra / Proyecto', 'Cant. Compras', 'Total Gastado ($)'];
        } elseif ($tipo === 'compras_centro') {
            $q = "SELECT cc.nombre AS Centro_Costo, COUNT(g.id) AS Cant_Gastos, SUM(g.total) AS Total
                  FROM gastos g
                  LEFT JOIN centros_costos cc ON g.centro_costo_id = cc.id
                  WHERE $whereSql GROUP BY g.centro_costo_id ORDER BY Total DESC";
            $response['columns'] = ['Centro de Costo', 'Cant. Compras', 'Total Gastado ($)'];
        } else {
            $q = "SELECT g.fecha, pr.nombre AS Proveedor, c.nombre AS Categoria, sub.nombre AS Subcategoria,
                         o.nombre AS Obra, cc.nombre AS Centro, g.detalle, g.total
                  FROM gastos g
                  LEFT JOIN proveedores pr ON g.proveedor_id = pr.id
                  LEFT JOIN categorias c ON g.categoria_id = c.id
                  LEFT JOIN subcategorias sub ON g.subcategoria_id = sub.id
                  LEFT JOIN obras o ON g.obra_id = o.id
                  LEFT JOIN centros_costos cc ON g.centro_costo_id = cc.id
                  WHERE $whereSql ORDER BY g.fecha DESC";
            $response['columns'] = ['Fecha', 'Proveedor', 'Categoría', 'Subcategoría', 'Obra', 'Centro Costo', 'Detalle', 'Total ($)'];
        }

        $res = $conn->query($q);
        if (!$res) {
            echo json_encode(['status' => false, 'message' => 'Error SQL: ' . $conn->error]);
            exit;
        }
        $totalG = 0;
        while ($r = $res->fetch_assoc()) {
            if (isset($r['Total'])) $totalG += $r['Total'];
            if (isset($r['total'])) $totalG += $r['total'];
            $response['data'][] = $r;
        }
        $response['total'] = $totalG;
        break;

    // ==========================================
    // 3. RESUMEN ANUAL E INCIDENCIA DE GASTOS
    // ==========================================
    case 'resumen_anual_gastos':
        $w = ["1=1"];
        if (!empty($f_desde)) $w[] = "g.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta)) $w[] = "g.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id)) $w[] = "g.obra_id = " . intval($obra_id);
        if (!empty($centro_id)) $w[] = "g.centro_costo_id = " . intval($centro_id);
        $whereSql = implode(" AND ", $w);

        $q = "SELECT c.nombre AS Categoria, SUM(g.total) AS Total_Gasto,
                     ROUND((SUM(g.total) / (SELECT IFNULL(SUM(total),1) FROM gastos g WHERE $whereSql)) * 100, 2) AS Incidencia
              FROM gastos g
              LEFT JOIN categorias c ON g.categoria_id = c.id
              WHERE $whereSql
              GROUP BY g.categoria_id
              ORDER BY Total_Gasto DESC";

        $response['columns'] = ['Categoría', 'Total Gastado ($)', 'Incidencia (%)'];
        $res = $conn->query($q);
        if (!$res) {
            echo json_encode(['status' => false, 'message' => 'Error SQL: ' . $conn->error]);
            exit;
        }
        $totalG = 0;
        while ($r = $res->fetch_assoc()) {
            $totalG += $r['Total_Gasto'];
            $response['data'][] = [
                'categoria' => $r['Categoria'] ?? 'Sin Categoría',
                'monto' => number_format($r['Total_Gasto'], 2, '.', ''),
                'incidencia' => ($r['Incidencia'] ?? 0) . ' %'
            ];
        }
        $response['total'] = $totalG;
        break;

    // ==========================================
    // 4. HISTÓRICO DE MOVIMIENTOS DE CAJA
    // ==========================================
    case 'historico_cajas':
        $w = ["1=1"];
        if (!empty($f_desde)) $w[] = "mc.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta)) $w[] = "mc.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($caja_id)) $w[] = "mc.caja_id = " . intval($caja_id);
        if (!empty($usuario_id)) $w[] = "mc.usuario_id = " . intval($usuario_id);
        if (!empty($tipo_mov)) $w[] = "mc.tipo = '" . $conn->real_escape_string($tipo_mov) . "'";
        $whereSql = implode(" AND ", $w);

        $q = "SELECT mc.fecha, c.nombre AS Caja, mc.tipo, mc.concepto, mc.comprobante, u.usuario, mc.importe
              FROM movimientos_caja mc
              LEFT JOIN cajas c ON mc.caja_id = c.id
              LEFT JOIN usuarios u ON mc.usuario_id = u.id
              WHERE $whereSql ORDER BY mc.fecha DESC";

        $response['columns'] = ['Fecha', 'Caja', 'Tipo Movimiento', 'Concepto', 'Comprobante', 'Usuario', 'Importe ($)'];
        $res = $conn->query($q);
        if (!$res) {
            echo json_encode(['status' => false, 'message' => 'Error SQL: ' . $conn->error]);
            exit;
        }
        $totalG = 0;
        while ($r = $res->fetch_assoc()) {
            $totalG += $r['importe'];
            $response['data'][] = $r;
        }
        $response['total'] = $totalG;
        break;

    // ==========================================
    // 5. RETENCIONES DE VENTA
    // ==========================================
    case 'informe_retenciones':
        $w = ["1=1"];
        if (!empty($f_desde)) $w[] = "rv.fecha_retencion >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta)) $w[] = "rv.fecha_retencion <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($cliente_id)) $w[] = "fv.cliente_id = " . intval($cliente_id);
        $whereSql = implode(" AND ", $w);

        $q = "SELECT rv.fecha_retencion, tr.nombre AS Tipo_Retencion, rv.nro_certificado, 
                     CONCAT(fv.punto_venta, '-', fv.nro_factura) AS Factura, cl.nombre AS Cliente, rv.importe
              FROM retenciones_venta rv
              LEFT JOIN tipos_retenciones tr ON rv.tipo_retencion_id = tr.id
              LEFT JOIN facturas_venta fv ON rv.factura_id = fv.id
              LEFT JOIN clientes cl ON fv.cliente_id = cl.id
              WHERE $whereSql ORDER BY rv.fecha_retencion DESC";

        $response['columns'] = ['Fecha Retención', 'Tipo Retención', 'Certificado N°', 'Factura Aplicada', 'Cliente', 'Importe ($)'];
        $res = $conn->query($q);
        if (!$res) {
            echo json_encode(['status' => false, 'message' => 'Error SQL: ' . $conn->error]);
            exit;
        }
        $totalG = 0;
        while ($r = $res->fetch_assoc()) {
            $totalG += $r['importe'];
            $response['data'][] = $r;
        }
        $response['total'] = $totalG;
        break;

    // ==========================================
    // 6. HISTÓRICO DE CHEQUES
    // ==========================================
    case 'informe_cheques':
        $w = ["1=1"];
        if (!empty($f_desde)) $w[] = "ch.fecha_emision >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta)) $w[] = "ch.fecha_emision <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($usuario_id)) $w[] = "ch.usuario_id = " . intval($usuario_id);
        $whereSql = implode(" AND ", $w);

        $q = "SELECT ch.fecha_emision, ch.fecha_pago, ch.nro_cheque, ch.tipo, ch.estado, ch.beneficiario, u.usuario, ch.importe
              FROM cheques ch
              LEFT JOIN usuarios u ON ch.usuario_id = u.id
              WHERE $whereSql ORDER BY ch.fecha_emision DESC";

        $response['columns'] = ['F. Emisión', 'F. Pago', 'N° Cheque', 'Tipo', 'Estado', 'Beneficiario', 'Usuario Carga', 'Importe ($)'];
        $res = $conn->query($q);
        if (!$res) {
            echo json_encode(['status' => false, 'message' => 'Error SQL: ' . $conn->error]);
            exit;
        }
        $totalG = 0;
        while ($r = $res->fetch_assoc()) {
            $totalG += $r['importe'];
            $response['data'][] = $r;
        }
        $response['total'] = $totalG;
        break;

    // ==========================================
    // 7. AUDITORÍA / USUARIOS
    // ==========================================
    case 'historico_usuarios':
        $w = ["1=1"];
        if (!empty($f_desde)) $w[] = "mc.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta)) $w[] = "mc.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($usuario_id)) $w[] = "u.id = " . intval($usuario_id);
        $whereSql = implode(" AND ", $w);

        $q = "SELECT u.usuario, COUNT(mc.id) AS Cant_Movimientos_Caja, IFNULL(SUM(mc.importe), 0) AS Importe_Total_Manejado
              FROM usuarios u
              LEFT JOIN movimientos_caja mc ON mc.usuario_id = u.id
              WHERE $whereSql GROUP BY u.id ORDER BY Cant_Movimientos_Caja DESC";

        $response['columns'] = ['Usuario', 'Cant. Movimientos Registrados', 'Monto Acumulado ($)'];
        $res = $conn->query($q);
        if (!$res) {
            echo json_encode(['status' => false, 'message' => 'Error SQL: ' . $conn->error]);
            exit;
        }
        while ($r = $res->fetch_assoc()) {
            $response['data'][] = $r;
        }
        break;
   
    // ==========================================
    // 8. POSICIÓN FISCAL IVA (VENTAS VS COMPRAS)
    // ==========================================
    case 'posicion_iva':
        // Filtros para Ventas
        $wVentas = ["1=1"];
        if (!empty($f_desde)) $wVentas[] = "fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta)) $wVentas[] = "fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id)) $wVentas[] = "obra_id = " . intval($obra_id);
        if (!empty($centro_id)) $wVentas[] = "centro_costo_id = " . intval($centro_id);
        if (!empty($cliente_id)) $wVentas[] = "cliente_id = " . intval($cliente_id);
        $whereVentas = implode(" AND ", $wVentas);

        // Filtros para Compras
        $wCompras = ["1=1"];
        if (!empty($f_desde)) $wCompras[] = "fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta)) $wCompras[] = "fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id)) $wCompras[] = "obra_id = " . intval($obra_id);
        if (!empty($centro_id)) $wCompras[] = "centro_costo_id = " . intval($centro_id);
        if (!empty($prov_id)) $wCompras[] = "proveedor_id = " . intval($prov_id);
        $whereCompras = implode(" AND ", $wCompras);

        // Consultar Totales e IVA de Ventas
        $qVentas = "SELECT IFNULL(SUM(neto),0) AS neto_ventas, IFNULL(SUM(iva),0) AS iva_ventas, IFNULL(SUM(total),0) AS total_ventas 
                    FROM facturas_venta WHERE $whereVentas";
        $resVentas = $conn->query($qVentas)->fetch_assoc();

        // Consultar Totales e IVA de Compras / Gastos
        $qCompras = "SELECT IFNULL(SUM(neto),0) AS neto_compras, IFNULL(SUM(iva),0) AS iva_compras, IFNULL(SUM(total),0) AS total_compras 
                     FROM gastos WHERE $whereCompras";
        $resCompras = $conn->query($qCompras)->fetch_assoc();

        $ivaVentas = (float)$resVentas['iva_ventas'];
        $ivaCompras = (float)$resCompras['iva_compras'];
        $saldoIVA = $ivaVentas - $ivaCompras; // Positivo = Saldo a Pagar (Débito) | Negativo = Saldo a Favor (Crédito)

        $response['columns'] = ['Concepto', 'Neto Gravado ($)', 'IVA Fiscal ($)', 'Total Facturado ($)', 'Resultado Posición IVA'];

        $response['data'] = [
            [
                'concepto' => 'VENTAS (IVA Débito Fiscal)',
                'neto' => number_format($resVentas['neto_ventas'], 2, '.', ''),
                'iva' => number_format($ivaVentas, 2, '.', ''),
                'total' => number_format($resVentas['total_ventas'], 2, '.', ''),
                'resultado' => '-'
            ],
            [
                'concepto' => 'COMPRAS / GASTOS (IVA Crédito Fiscal)',
                'neto' => number_format($resCompras['neto_compras'], 2, '.', ''),
                'iva' => number_format($ivaCompras, 2, '.', ''),
                'total' => number_format($resCompras['total_compras'], 2, '.', ''),
                'resultado' => '-'
            ],
            [
                'concepto' => 'SALDO POSICIÓN IVA PERÍODO',
                'neto' => number_format($resVentas['neto_ventas'] - $resCompras['neto_compras'], 2, '.', ''),
                'iva' => number_format($saldoIVA, 2, '.', ''),
                'total' => number_format($resVentas['total_ventas'] - $resCompras['total_compras'], 2, '.', ''),
                'resultado' => $saldoIVA >= 0 
                    ? '<b class="text-danger">A PAGAR: $ ' . number_format($saldoIVA, 2, '.', '') . '</b>' 
                    : '<b class="text-success">A FAVOR: $ ' . number_format(abs($saldoIVA), 2, '.', '') . '</b>'
            ]
        ];

        // Se envía el saldo directo
        $response['total'] = $saldoIVA;
        break;    
}

echo json_encode($response);