<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

$tipo = $_GET['tipo_reporte'] ?? 'gastos_generales';

// Recoger filtros
$f_desde    = $_GET['fecha_desde'] ?? '';
$f_hasta    = $_GET['fecha_hasta'] ?? '';
$obra_id    = $_GET['obra_id'] ?? '';
$centro_id  = $_GET['centro_costo_id'] ?? '';
$cat_id     = $_GET['categoria_id'] ?? '';
$subcat_id  = $_GET['subcategoria_id'] ?? '';
$cliente_id = $_GET['cliente_id'] ?? '';
$prov_id    = $_GET['proveedor_id'] ?? '';
$caja_id    = $_GET['caja_id'] ?? '';
$usuario_id = $_GET['usuario_id'] ?? '';
$tipo_mov   = $_GET['tipo_movimiento'] ?? '';

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
        if (!empty($f_desde))    $w[] = "fv.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))    $w[] = "fv.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id))    $w[] = "fv.obra_id = " . intval($obra_id);
        if (!empty($centro_id))  $w[] = "fv.centro_costo_id = " . intval($centro_id);
        if (!empty($cliente_id)) $w[] = "fv.cliente_id = " . intval($cliente_id);
        if (!empty($usuario_id)) $w[] = "fv.usuario_id = " . intval($usuario_id);
        $whereSql = implode(" AND ", $w);

        if ($tipo === 'ventas_cliente') {
            $q = "SELECT IFNULL(cl.nombre, 'Sin Cliente') AS Cliente, COUNT(fv.id) AS Facturas, SUM(fv.neto) AS Neto, SUM(fv.iva) AS IVA, SUM(fv.total) AS Total
                  FROM facturas_venta fv
                  LEFT JOIN clientes cl ON fv.cliente_id = cl.id
                  WHERE $whereSql GROUP BY fv.cliente_id ORDER BY Total DESC";
            $response['columns'] = ['Cliente', 'Cant. Facturas', 'Neto ($)', 'IVA ($)', 'Total Sold ($)'];
        } elseif ($tipo === 'ventas_obra') {
            $q = "SELECT IFNULL(o.nombre, 'Sin Obra') AS Obra, COUNT(fv.id) AS Facturas, SUM(fv.total) AS Total
                  FROM facturas_venta fv
                  LEFT JOIN obras o ON fv.obra_id = o.id
                  WHERE $whereSql GROUP BY fv.obra_id ORDER BY Total DESC";
            $response['columns'] = ['Obra / Proyecto', 'Cant. Facturas', 'Total Facturado ($)'];
        } elseif ($tipo === 'ventas_centro') {
            $q = "SELECT IFNULL(cc.nombre, 'Sin Centro') AS Centro_Costo, COUNT(fv.id) AS Facturas, SUM(fv.total) AS Total
                  FROM facturas_venta fv
                  LEFT JOIN centros_costos cc ON fv.centro_costo_id = cc.id
                  WHERE $whereSql GROUP BY fv.centro_costo_id ORDER BY Total DESC";
            $response['columns'] = ['Centro de Costo', 'Cant. Facturas', 'Total Facturado ($)'];
        } else {
            $q = "SELECT DATE_FORMAT(fv.fecha, '%d/%m/%Y') AS fecha, 
                         CONCAT(fv.punto_venta, '-', fv.nro_factura) AS nro_factura, 
                         IFNULL(cl.nombre, 'Sin Cliente') AS cliente, 
                         IFNULL(o.nombre, '-') AS obra, 
                         IFNULL(cc.nombre, '-') AS centro, 
                         IFNULL(fv.neto, 0) AS neto, 
                         IFNULL(fv.iva, 0) AS iva, 
                         IFNULL(fv.total, 0) AS total, 
                         fv.estado
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

        $totalNeto = 0;
        $totalIva  = 0;
        $totalG    = 0;

        while ($r = $res->fetch_assoc()) {
            if ($tipo === 'ventas_generales') {
                $neto  = floatval($r['neto'] ?? 0);
                $iva   = floatval($r['iva'] ?? 0);
                $total = floatval($r['total'] ?? 0);

                $totalNeto += $neto;
                $totalIva  += $iva;
                $totalG    += $total;

                $r['neto']  = '$ ' . number_format($neto, 2, ',', '.');
                $r['iva']   = '$ ' . number_format($iva, 2, ',', '.');
                $r['total'] = '$ ' . number_format($total, 2, ',', '.');
            } else {
                $totalG += floatval($r['Total'] ?? 0);
            }
            $response['data'][] = $r;
        }

        if ($tipo === 'ventas_generales') {
            $response['total_neto'] = '$ ' . number_format($totalNeto, 2, ',', '.');
            $response['total_iva']  = '$ ' . number_format($totalIva, 2, ',', '.');
            $response['total']      = '$ ' . number_format($totalG, 2, ',', '.');
        } else {
            $response['total']      = '$ ' . number_format($totalG, 2, ',', '.');
        }
        break;

    // ==========================================
    // 2. COMPRAS Y GASTOS (gastos_generales)
    // ==========================================
    case 'gastos_generales':
    case 'compras_proveedor':
    case 'compras_obra':
    case 'compras_centro':
        $w = ["1=1"];
        if (!empty($f_desde))    $w[] = "g.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))    $w[] = "g.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id))    $w[] = "g.obra_id = " . intval($obra_id);
        if (!empty($centro_id))  $w[] = "g.centro_costo_id = " . intval($centro_id);
        if (!empty($cat_id))     $w[] = "g.categoria_id = " . intval($cat_id);
        if (!empty($subcat_id))  $w[] = "g.subcategoria_id = " . intval($subcat_id);
        if (!empty($prov_id))    $w[] = "g.proveedor_id = " . intval($prov_id);
        if (!empty($caja_id))    $w[] = "g.caja_id = " . intval($caja_id);
        if (!empty($usuario_id)) $w[] = "g.usuario_id = " . intval($usuario_id);
        $whereSql = implode(" AND ", $w);

        if ($tipo === 'compras_proveedor') {
            $q = "SELECT IFNULL(pr.nombre, 'Sin Proveedor') AS Proveedor, COUNT(g.id) AS Cant_Gastos, SUM(g.total) AS Total
                  FROM gastos g
                  LEFT JOIN proveedores pr ON g.proveedor_id = pr.id
                  WHERE $whereSql GROUP BY g.proveedor_id ORDER BY Total DESC";
            $response['columns'] = ['Proveedor', 'Cant. Compras', 'Total Comprado ($)'];
        } elseif ($tipo === 'compras_obra') {
            $q = "SELECT IFNULL(o.nombre, 'Sin Obra') AS Obra, COUNT(g.id) AS Cant_Gastos, SUM(g.total) AS Total
                  FROM gastos g
                  LEFT JOIN obras o ON g.obra_id = o.id
                  WHERE $whereSql GROUP BY g.obra_id ORDER BY Total DESC";
            $response['columns'] = ['Obra / Proyecto', 'Cant. Compras', 'Total Gastado ($)'];
        } elseif ($tipo === 'compras_centro') {
            $q = "SELECT IFNULL(cc.nombre, 'Sin Centro') AS Centro_Costo, COUNT(g.id) AS Cant_Gastos, SUM(g.total) AS Total
                  FROM gastos g
                  LEFT JOIN centros_costos cc ON g.centro_costo_id = cc.id
                  WHERE $whereSql GROUP BY g.centro_costo_id ORDER BY Total DESC";
            $response['columns'] = ['Centro de Costo', 'Cant. Compras', 'Total Gastado ($)'];
        } else {
            $q = "SELECT DATE_FORMAT(g.fecha, '%d/%m/%Y') AS fecha, 
                         IFNULL(cc.nombre, 'Sin Centro') AS centro_costo,
                         IFNULL(g.detalle, '-') AS detalle, 
                         IFNULL(g.neto, 0) AS neto, 
                         IFNULL(g.iva, 0) AS iva,
                         IFNULL(g.total, 0) AS total
                  FROM gastos g
                  LEFT JOIN centros_costos cc ON g.centro_costo_id = cc.id
                  WHERE $whereSql 
                  ORDER BY g.fecha DESC";
            $response['columns'] = ['Fecha', 'Centro de Costo', 'Detalle', 'Neto ($)', 'IVA ($)', 'Total ($)'];
        }

        $res = $conn->query($q);
        if (!$res) {
            echo json_encode(['status' => false, 'message' => 'Error SQL en Gastos: ' . $conn->error]);
            exit;
        }

        $totalNeto = 0;
        $totalIva  = 0;
        $totalG    = 0;

        while ($r = $res->fetch_assoc()) {
            if ($tipo === 'gastos_generales') {
                $neto  = floatval($r['neto'] ?? 0);
                $iva   = floatval($r['iva'] ?? 0);
                $total = floatval($r['total'] ?? 0);

                $totalNeto += $neto;
                $totalIva  += $iva;
                $totalG    += $total;

                $r['neto']  = '$ ' . number_format($neto, 2, ',', '.');
                $r['iva']   = '$ ' . number_format($iva, 2, ',', '.');
                $r['total'] = '$ ' . number_format($total, 2, ',', '.');
            } else {
                $totalG += floatval($r['Total'] ?? 0);
                if (isset($r['Total'])) {
                    $r['Total'] = '$ ' . number_format(floatval($r['Total']), 2, ',', '.');
                }
            }
            $response['data'][] = $r;
        }

        if ($tipo === 'gastos_generales') {
            $response['total_neto'] = '$ ' . number_format($totalNeto, 2, ',', '.');
            $response['total_iva']  = '$ ' . number_format($totalIva, 2, ',', '.');
            $response['total']      = '$ ' . number_format($totalG, 2, ',', '.');
        } else {
            $response['total']      = '$ ' . number_format($totalG, 2, ',', '.');
        }
        break;

    // ==========================================
    // 3. GASTOS VS VENTAS
    // ==========================================
    case 'gastos_vs_ventas':
        $wVentas = ["1=1"];
        if (!empty($f_desde))    $wVentas[] = "fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))    $wVentas[] = "fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id))    $wVentas[] = "obra_id = " . intval($obra_id);
        if (!empty($centro_id))  $wVentas[] = "centro_costo_id = " . intval($centro_id);
        if (!empty($cliente_id)) $wVentas[] = "cliente_id = " . intval($cliente_id);
        $whereVentas = implode(" AND ", $wVentas);

        $wCompras = ["1=1"];
        if (!empty($f_desde))   $wCompras[] = "fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))   $wCompras[] = "fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id))   $wCompras[] = "obra_id = " . intval($obra_id);
        if (!empty($centro_id)) $wCompras[] = "centro_costo_id = " . intval($centro_id);
        if (!empty($prov_id))   $wCompras[] = "proveedor_id = " . intval($prov_id);
        $whereCompras = implode(" AND ", $wCompras);

        $rV = $conn->query("SELECT IFNULL(SUM(neto),0) AS neto, IFNULL(SUM(iva),0) AS iva, IFNULL(SUM(total),0) AS total FROM facturas_venta WHERE $whereVentas")->fetch_assoc();
        $rC = $conn->query("SELECT IFNULL(SUM(neto),0) AS neto, IFNULL(SUM(iva),0) AS iva, IFNULL(SUM(total),0) AS total FROM gastos WHERE $whereCompras")->fetch_assoc();

        $totalVentas = (float)$rV['total'];
        $totalGastos = (float)$rC['total'];
        $resultadoNeto = $totalVentas - $totalGastos;

        $response['columns'] = ['Concepto', 'Neto Gravado ($)', 'IVA ($)', 'Total Acumulado ($)', 'Margen / Estado'];

        $response['data'] = [
            [
                'concepto' => 'TOTAL VENTAS (Ingresos)',
                'neto' => '$ ' . number_format($rV['neto'], 2, ',', '.'),
                'iva' => '$ ' . number_format($rV['iva'], 2, ',', '.'),
                'total' => '$ ' . number_format($totalVentas, 2, ',', '.'),
                'estado' => '<span class="badge bg-success">Ingreso</span>'
            ],
            [
                'concepto' => 'TOTAL GASTOS (Egresos)',
                'neto' => '$ ' . number_format($rC['neto'], 2, ',', '.'),
                'iva' => '$ ' . number_format($rC['iva'], 2, ',', '.'),
                'total' => '$ ' . number_format($totalGastos, 2, ',', '.'),
                'estado' => '<span class="badge bg-danger">Egreso</span>'
            ],
            [
                'concepto' => 'RESULTADO OPERATIVO NETO',
                'neto' => '$ ' . number_format($rV['neto'] - $rC['neto'], 2, ',', '.'),
                'iva' => '$ ' . number_format($rV['iva'] - $rC['iva'], 2, ',', '.'),
                'total' => '$ ' . number_format($resultadoNeto, 2, ',', '.'),
                'estado' => $resultadoNeto >= 0 
                    ? '<b class="text-success">GANANCIA: $ ' . number_format($resultadoNeto, 2, ',', '.') . '</b>' 
                    : '<b class="text-danger">PÉRDIDA: $ ' . number_format(abs($resultadoNeto), 2, ',', '.') . '</b>'
            ]
        ];

        $response['total'] = '$ ' . number_format($resultadoNeto, 2, ',', '.');
        break;

    // ==========================================
    // 4. RESUMEN ANUAL E INCIDENCIA DE GASTOS
    // ==========================================
    case 'resumen_anual_gastos':
        $w = ["1=1"];
        if (!empty($f_desde))   $w[] = "g.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))   $w[] = "g.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id))   $w[] = "g.obra_id = " . intval($obra_id);
        if (!empty($centro_id)) $w[] = "g.centro_costo_id = " . intval($centro_id);
        if (!empty($cat_id))    $w[] = "g.categoria_id = " . intval($cat_id);
        if (!empty($subcat_id)) $w[] = "g.subcategoria_id = " . intval($subcat_id); // <-- HABILITADO FILTRO SUBCATEGORÍA
        $whereSql = implode(" AND ", $w);

        // Total global para calcular los porcentajes de incidencia
        $qTotal = "SELECT IFNULL(SUM(total), 1) AS total_global FROM gastos g WHERE $whereSql";
        $rTotal = $conn->query($qTotal)->fetch_assoc();
        $totalGlobal = (float)$rTotal['total_global'];
        if ($totalGlobal <= 0) $totalGlobal = 1;

        // Consulta agrupada por Categoría y Subcategoría
        $q = "SELECT 
                IFNULL(c.nombre, 'Sin Categoría') AS Categoria,
                IFNULL(sc.nombre, 'Sin Subcategoría') AS Subcategoria,
                SUM(g.total) AS Total_Gasto
              FROM gastos g
              LEFT JOIN categorias c ON g.categoria_id = c.id
              LEFT JOIN subcategorias sc ON g.subcategoria_id = sc.id
              WHERE $whereSql
              GROUP BY g.categoria_id, g.subcategoria_id
              ORDER BY Categoria ASC, Total_Gasto DESC";

        $response['columns'] = ['Categoría', 'Subcategoría', 'Total Gastado ($)', 'Incidencia (%)'];
        $res = $conn->query($q);
        if (!$res) {
            echo json_encode(['status' => false, 'message' => 'Error SQL: ' . $conn->error]);
            exit;
        }

        $totalG = 0;
        while ($r = $res->fetch_assoc()) {
            $monto = (float)$r['Total_Gasto'];
            $totalG += $monto;
            $incidencia = round(($monto / $totalGlobal) * 100, 2);

            $response['data'][] = [
                'categoria'   => $r['Categoria'],
                'subcategoria'=> $r['Subcategoria'],
                'monto'       => '$ ' . number_format($monto, 2, ',', '.'),
                'incidencia'  => $incidencia . ' %'
            ];
        }
        $response['total'] = '$ ' . number_format($totalG, 2, ',', '.');
        break;

    // ==========================================
    // 5. HISTÓRICO DE MOVIMIENTOS DE CAJA
    // ==========================================
    case 'historico_cajas':
        $w = ["1=1"];
        if (!empty($f_desde))    $w[] = "mc.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))    $w[] = "mc.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($caja_id))    $w[] = "mc.caja_id = " . intval($caja_id);
        if (!empty($usuario_id)) $w[] = "mc.usuario_id = " . intval($usuario_id);
        if (!empty($tipo_mov))   $w[] = "mc.tipo = '" . $conn->real_escape_string($tipo_mov) . "'";
        $whereSql = implode(" AND ", $w);

        $q = "SELECT DATE_FORMAT(mc.fecha, '%d/%m/%Y') AS fecha, 
                     IFNULL(c.nombre, '-') AS caja, 
                     mc.tipo, 
                     mc.concepto, 
                     IFNULL(mc.comprobante, '-') AS comprobante, 
                     IFNULL(u.usuario, '-') AS usuario, 
                     mc.importe
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
            $imp = (float)$r['importe'];
            $totalG += $imp;
            $r['importe'] = '$ ' . number_format($imp, 2, ',', '.');
            $response['data'][] = $r;
        }
        $response['total'] = '$ ' . number_format($totalG, 2, ',', '.');
        break;

    // ==========================================
    // 6. RETENCIONES DE VENTA
    // ==========================================
    case 'informe_retenciones':
        $w = ["1=1"];
        if (!empty($f_desde))    $w[] = "rv.fecha_retencion >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))    $w[] = "rv.fecha_retencion <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($cliente_id)) $w[] = "fv.cliente_id = " . intval($cliente_id);
        $whereSql = implode(" AND ", $w);

        $q = "SELECT DATE_FORMAT(rv.fecha_retencion, '%d/%m/%Y') AS fecha_retencion, 
                     IFNULL(tr.nombre, '-') AS tipo_retencion, 
                     IFNULL(rv.nro_certificado, '-') AS nro_certificado, 
                     IFNULL(CONCAT(fv.punto_venta, '-', fv.nro_factura), '-') AS factura, 
                     IFNULL(cl.nombre, '-') AS cliente, 
                     rv.importe
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
            $imp = (float)$r['importe'];
            $totalG += $imp;
            $r['importe'] = '$ ' . number_format($imp, 2, ',', '.');
            $response['data'][] = $r;
        }
        $response['total'] = '$ ' . number_format($totalG, 2, ',', '.');
        break;

    // ==========================================
    // 7. HISTÓRICO DE CHEQUES
    // ==========================================
    case 'informe_cheques':
        $w = ["1=1"];
        if (!empty($f_desde))    $w[] = "ch.fecha_emision >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))    $w[] = "ch.fecha_emision <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($usuario_id)) $w[] = "ch.usuario_id = " . intval($usuario_id);
        $whereSql = implode(" AND ", $w);

        $q = "SELECT DATE_FORMAT(ch.fecha_emision, '%d/%m/%Y') AS fecha_emision, 
                     DATE_FORMAT(ch.fecha_pago, '%d/%m/%Y') AS fecha_pago, 
                     ch.nro_cheque, ch.tipo, ch.estado, ch.beneficiario, 
                     IFNULL(u.usuario, '-') AS usuario, 
                     ch.importe
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
            $imp = (float)$r['importe'];
            $totalG += $imp;
            $r['importe'] = '$ ' . number_format($imp, 2, ',', '.');
            $response['data'][] = $r;
        }
        $response['total'] = '$ ' . number_format($totalG, 2, ',', '.');
        break;

    // ==========================================
    // 8. AUDITORÍA / USUARIOS
    // ==========================================
    case 'historico_usuarios':
        $w = ["1=1"];
        if (!empty($f_desde))    $w[] = "mc.fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))    $w[] = "mc.fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
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
            $r['Importe_Total_Manejado'] = '$ ' . number_format((float)$r['Importe_Total_Manejado'], 2, ',', '.');
            $response['data'][] = $r;
        }
        break;

    // ==========================================
    // 9. POSICIÓN FISCAL IVA
    // ==========================================
    case 'posicion_iva':
        $wVentas = ["1=1"];
        if (!empty($f_desde))    $wVentas[] = "fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))    $wVentas[] = "fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id))    $wVentas[] = "obra_id = " . intval($obra_id);
        if (!empty($centro_id))  $wVentas[] = "centro_costo_id = " . intval($centro_id);
        if (!empty($cliente_id)) $wVentas[] = "cliente_id = " . intval($cliente_id);
        $whereVentas = implode(" AND ", $wVentas);

        $wCompras = ["1=1"];
        if (!empty($f_desde))   $wCompras[] = "fecha >= '" . $conn->real_escape_string($f_desde) . "'";
        if (!empty($f_hasta))   $wCompras[] = "fecha <= '" . $conn->real_escape_string($f_hasta) . "'";
        if (!empty($obra_id))   $wCompras[] = "obra_id = " . intval($obra_id);
        if (!empty($centro_id)) $wCompras[] = "centro_costo_id = " . intval($centro_id);
        if (!empty($prov_id))   $wCompras[] = "proveedor_id = " . intval($prov_id);
        $whereCompras = implode(" AND ", $wCompras);

        $resVentas = $conn->query("SELECT IFNULL(SUM(neto),0) AS neto_ventas, IFNULL(SUM(iva),0) AS iva_ventas, IFNULL(SUM(total),0) AS total_ventas FROM facturas_venta WHERE $whereVentas")->fetch_assoc();
        $resCompras = $conn->query("SELECT IFNULL(SUM(neto),0) AS neto_compras, IFNULL(SUM(iva),0) AS iva_compras, IFNULL(SUM(total),0) AS total_compras FROM gastos WHERE $whereCompras")->fetch_assoc();

        $ivaVentas = (float)$resVentas['iva_ventas'];
        $ivaCompras = (float)$resCompras['iva_compras'];
        $saldoIVA = $ivaVentas - $ivaCompras;

        $response['columns'] = ['Concepto', 'Neto Gravado ($)', 'IVA Fiscal ($)', 'Total Facturado ($)', 'Resultado Posición IVA'];

        $response['data'] = [
            [
                'concepto' => 'VENTAS (IVA Débito Fiscal)',
                'neto' => '$ ' . number_format($resVentas['neto_ventas'], 2, ',', '.'),
                'iva' => '$ ' . number_format($ivaVentas, 2, ',', '.'),
                'total' => '$ ' . number_format($resVentas['total_ventas'], 2, ',', '.'),
                'resultado' => '-'
            ],
            [
                'concepto' => 'COMPRAS / GASTOS (IVA Crédito Fiscal)',
                'neto' => '$ ' . number_format($resCompras['neto_compras'], 2, ',', '.'),
                'iva' => '$ ' . number_format($ivaCompras, 2, ',', '.'),
                'total' => '$ ' . number_format($resCompras['total_compras'], 2, ',', '.'),
                'resultado' => '-'
            ],
            [
                'concepto' => 'SALDO POSICIÓN IVA PERÍODO',
                'neto' => '$ ' . number_format($resVentas['neto_ventas'] - $resCompras['neto_compras'], 2, ',', '.'),
                'iva' => '$ ' . number_format($saldoIVA, 2, ',', '.'),
                'total' => '$ ' . number_format($resVentas['total_ventas'] - $resCompras['total_compras'], 2, ',', '.'),
                'resultado' => $saldoIVA >= 0 
                    ? '<b class="text-danger">A PAGAR: $ ' . number_format($saldoIVA, 2, ',', '.') . '</b>' 
                    : '<b class="text-success">A FAVOR: $ ' . number_format(abs($saldoIVA), 2, ',', '.') . '</b>'
            ]
        ];

        $response['total'] = '$ ' . number_format($saldoIVA, 2, ',', '.');
        break;    
}

echo json_encode($response);