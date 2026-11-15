<?php
/* ==========================================================================
   CUENTA CORRIENTE DE UN CLIENTE
   --------------------------------------------------------------------------
   Debe: facturas. Haber: cobros, retenciones y notas de crédito.
   Las facturas marcadas como PAGADO a mano (sin cobros cargados) se cancelan
   con una línea "CANCELADA" por lo que falte, para que el saldo cierre.
   Lo usan ajax/clientes.php (pantalla) y modules/clientes/cuenta_corriente_pdf.php.
   ========================================================================== */

// $restr_usuario_id = 0: ve todas las facturas (admin / contador); si no, solo las de ese usuario
function ctaCteCalcular($conn, $cliente_id, $desde = '', $hasta = '', $restr_usuario_id = 0) {
    $cliente_id = (int)$cliente_id;
    $desde = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$desde) ? $desde : '';
    $hasta = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$hasta) ? $hasta : '';

    $res_cli = $conn->query("SELECT id, nombre, cuit, direccion FROM clientes WHERE id = $cliente_id");
    $cliente = $res_cli ? $res_cli->fetch_assoc() : null;
    if (!$cliente) return null;

    $restr = $restr_usuario_id > 0 ? ' AND f.usuario_id = ' . (int)$restr_usuario_id : '';
    $nro = function($pv, $n) { return str_pad($pv, 5, '0', STR_PAD_LEFT) . '-' . str_pad($n, 8, '0', STR_PAD_LEFT); };
    $movs = [];
    $pendientes = [];

    // Facturas (y notas de crédito)
    $res_f = $conn->query("SELECT f.id, f.fecha, f.punto_venta, f.nro_factura, f.detalle, f.total, f.estado, f.fecha_vencimiento,
                                  IFNULL(tc.nombre, 'FACTURA') AS tipo,
                                  (SELECT COALESCE(SUM(importe), 0) FROM cobros_venta WHERE factura_id = f.id) AS cobrado,
                                  (SELECT COALESCE(SUM(importe), 0) FROM retenciones_venta WHERE factura_id = f.id) AS retenido
                           FROM facturas_venta f
                           LEFT JOIN tipos_comprobante tc ON tc.id = f.tipo_comprobante_id
                           WHERE f.cliente_id = $cliente_id $restr");
    while ($res_f && ($f = $res_f->fetch_assoc())) {
        $comp = $f['tipo'] . ' ' . $nro($f['punto_venta'], $f['nro_factura']);
        $total = (float)$f['total'];
        $es_credito = stripos($f['tipo'], 'CREDITO') !== false || stripos($f['tipo'], 'CRÉDITO') !== false;

        $movs[] = ['fecha' => $f['fecha'], 'orden' => 1, 'tipo' => $es_credito ? 'NOTA DE CRÉDITO' : 'FACTURA', 'comprobante' => $comp,
                   'detalle' => (string)$f['detalle'], 'debe' => $es_credito ? 0 : $total, 'haber' => $es_credito ? $total : 0];
        if ($es_credito) continue;

        $saldo_f = round($total - (float)$f['cobrado'] - (float)$f['retenido'], 2);
        if (strtoupper($f['estado']) === 'PAGADO' && $saldo_f > 0.01) {
            $movs[] = ['fecha' => $f['fecha'], 'orden' => 4, 'tipo' => 'CANCELADA', 'comprobante' => $comp,
                       'detalle' => 'Marcada como pagada, sin cobros cargados', 'debe' => 0, 'haber' => $saldo_f];
            $saldo_f = 0;
        }
        if ($saldo_f > 0.01) {
            $pendientes[] = ['id' => (int)$f['id'], 'comprobante' => $comp, 'fecha' => $f['fecha'], 'vencimiento' => $f['fecha_vencimiento'],
                             'total' => $total, 'saldo' => $saldo_f];
        }
    }

    // Cobros
    $res_c = $conn->query("SELECT cv.fecha, cv.importe, cv.comprobante, IFNULL(mp.nombre, '') AS medio, f.punto_venta, f.nro_factura
                           FROM cobros_venta cv
                           JOIN facturas_venta f ON f.id = cv.factura_id
                           LEFT JOIN medios_pago mp ON mp.id = cv.medio_pago_id
                           WHERE f.cliente_id = $cliente_id $restr");
    while ($res_c && ($c = $res_c->fetch_assoc())) {
        $movs[] = ['fecha' => $c['fecha'], 'orden' => 2, 'tipo' => 'COBRO', 'comprobante' => (string)$c['comprobante'] !== '' ? 'Recibo ' . $c['comprobante'] : '',
                   'detalle' => 'Fact. ' . $nro($c['punto_venta'], $c['nro_factura']) . ($c['medio'] !== '' ? ' · ' . $c['medio'] : ''),
                   'debe' => 0, 'haber' => (float)$c['importe']];
    }

    // Retenciones
    $res_r = $conn->query("SELECT r.fecha_retencion AS fecha, r.importe, r.nro_certificado, IFNULL(t.nombre, '') AS tipo, f.punto_venta, f.nro_factura
                           FROM retenciones_venta r
                           JOIN facturas_venta f ON f.id = r.factura_id
                           LEFT JOIN tipos_retenciones t ON t.id = r.tipo_retencion_id
                           WHERE f.cliente_id = $cliente_id $restr");
    while ($res_r && ($r = $res_r->fetch_assoc())) {
        $movs[] = ['fecha' => $r['fecha'], 'orden' => 3, 'tipo' => 'RETENCIÓN', 'comprobante' => (string)$r['nro_certificado'] !== '' ? 'Cert. ' . $r['nro_certificado'] : '',
                   'detalle' => trim($r['tipo'] . ' · Fact. ' . $nro($r['punto_venta'], $r['nro_factura']), ' ·'),
                   'debe' => 0, 'haber' => (float)$r['importe']];
    }

    // Orden cronológico; en el mismo día, primero la factura y después lo que la cancela
    usort($movs, function($a, $b) { return [$a['fecha'], $a['orden']] <=> [$b['fecha'], $b['orden']]; });

    $saldo_inicial = 0; $saldo = 0; $tot_debe = 0; $tot_haber = 0;
    $salida = [];
    foreach ($movs as $m) {
        if ($desde !== '' && $m['fecha'] < $desde) { $saldo_inicial += $m['debe'] - $m['haber']; $saldo = $saldo_inicial; continue; }
        if ($hasta !== '' && $m['fecha'] > $hasta) continue;
        $saldo += $m['debe'] - $m['haber'];
        $tot_debe += $m['debe']; $tot_haber += $m['haber'];
        unset($m['orden']);
        $m['saldo'] = round($saldo, 2);
        $salida[] = $m;
    }

    usort($pendientes, function($a, $b) { return strcmp((string)$a['vencimiento'], (string)$b['vencimiento']); });

    return [
        'cliente' => $cliente,
        'desde' => $desde,
        'hasta' => $hasta,
        'saldo_inicial' => round($saldo_inicial, 2),
        'movimientos' => $salida,
        'totales' => ['debe' => round($tot_debe, 2), 'haber' => round($tot_haber, 2), 'saldo' => round($saldo_inicial + $tot_debe - $tot_haber, 2)],
        'pendientes' => $pendientes,
    ];
}

// Usuario cuyas facturas se restringen: 0 si es admin o contador (ve todo)
function ctaCteRestriccionUsuario() {
    $rol = $_SESSION['rol'] ?? '';
    $ve_todo = strcasecmp($rol, 'admin') === 0 || strcasecmp($rol, 'contador') === 0;
    return $ve_todo ? 0 : (int)($_SESSION['id'] ?? -1);
}
