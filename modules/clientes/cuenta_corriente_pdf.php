<?php
// =====================================================================
// PDF de la cuenta corriente de un cliente (para enviarle el estado de cuenta)
// Parámetros: ?id=CLIENTE &desde=AAAA-MM-DD &hasta=AAAA-MM-DD (opcionales)
// =====================================================================
require_once '../../config/database.php';
require_once '../../includes/auth.php'; // solo usuarios con sesión iniciada
require_once __DIR__ . '/../../includes/cuenta_corriente.php';
require_once __DIR__ . '/../presupuestos/includes/pdf_comun.php'; // Dompdf, logo y pie de la empresa

$cc = ctaCteCalcular($conn, (int)($_GET['id'] ?? 0), $_GET['desde'] ?? '', $_GET['hasta'] ?? '', ctaCteRestriccionUsuario());
if (!$cc) {
    die('Cliente no encontrado.');
}

$cli = $cc['cliente'];
$f = function($d) { return $d ? date('d/m/Y', strtotime($d)) : '-'; };
$periodo = ($cc['desde'] || $cc['hasta'])
    ? 'Período: ' . ($cc['desde'] ? $f($cc['desde']) : 'inicio') . ' al ' . ($cc['hasta'] ? $f($cc['hasta']) : $f(date('Y-m-d')))
    : 'Todos los movimientos al ' . $f(date('Y-m-d'));
$logo_base64 = pdfLogoBase64();
$dompdf = pdfCrearDompdf();

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuenta corriente - <?= pdfTexto($cli['nombre']) ?></title>
    <style>
        @page { margin: 30px 35px 50px 35px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #222; margin: 0; }
        .encabezado { width: 100%; border-bottom: 2px solid #333; padding-bottom: 6px; margin-bottom: 10px; }
        .encabezado h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .encabezado .sub { margin: 3px 0 0 0; color: #555; font-size: 10px; }
        .info-box { border: 1px solid #ccc; background-color: #f9f9f9; padding: 6px 8px; margin-bottom: 10px; }
        .tabla { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .tabla th, .tabla td { border: 1px solid #bbb; padding: 4px 5px; }
        .tabla th { background-color: #212529; color: #fff; text-align: left; font-size: 9px; }
        .text-end { text-align: right; }
        .fila-saldo td { background-color: #f1f1f1; font-weight: bold; }
        .resumen { width: 50%; margin-left: 50%; border-collapse: collapse; margin-bottom: 14px; }
        .resumen td { padding: 5px 8px; border: 1px solid #ccc; }
        .resumen .final td { background-color: #212529; color: #fff; font-weight: bold; font-size: 11px; }
        h4 { margin: 8px 0 5px 0; font-size: 11px; text-transform: uppercase; }
    </style>
</head>
<body>

<?= pdfPieEmpresa() /* datos de contacto de la empresa al pie de cada página */ ?>

<table class="encabezado">
    <tr>
        <td style="vertical-align: middle;">
            <h2>Estado de cuenta corriente</h2>
            <p class="sub"><?= pdfTexto($periodo) ?></p>
        </td>
        <?php if ($logo_base64): ?>
        <td style="vertical-align: middle; text-align: right; width: 150px;">
            <img src="<?= $logo_base64 ?>" style="max-height: 55px; max-width: 140px;">
        </td>
        <?php endif; ?>
    </tr>
</table>

<div class="info-box">
    <strong>Cliente:</strong> <?= pdfTexto($cli['nombre']) ?>
    <?php if (!empty($cli['cuit'])): ?> &nbsp;·&nbsp; <strong>CUIT:</strong> <?= pdfTexto($cli['cuit']) ?><?php endif; ?>
    <?php if (!empty($cli['direccion'])): ?> &nbsp;·&nbsp; <?= pdfTexto($cli['direccion']) ?><?php endif; ?>
</div>

<table class="tabla">
    <thead>
        <tr>
            <th style="width: 11%;">Fecha</th>
            <th style="width: 13%;">Tipo</th>
            <th style="width: 20%;">Comprobante</th>
            <th>Detalle</th>
            <th class="text-end" style="width: 12%;">Debe</th>
            <th class="text-end" style="width: 12%;">Haber</th>
            <th class="text-end" style="width: 12%;">Saldo</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($cc['desde']): ?>
        <tr class="fila-saldo">
            <td colspan="6">Saldo anterior al <?= $f($cc['desde']) ?></td>
            <td class="text-end"><?= pdfMoneda($cc['saldo_inicial']) ?></td>
        </tr>
        <?php endif; ?>
        <?php foreach ($cc['movimientos'] as $m): ?>
        <tr>
            <td><?= $f($m['fecha']) ?></td>
            <td><?= pdfTexto($m['tipo']) ?></td>
            <td><?= pdfTexto($m['comprobante']) ?></td>
            <td><?= pdfTexto($m['detalle']) ?></td>
            <td class="text-end"><?= $m['debe'] ? pdfMoneda($m['debe']) : '' ?></td>
            <td class="text-end"><?= $m['haber'] ? pdfMoneda($m['haber']) : '' ?></td>
            <td class="text-end"><?= pdfMoneda($m['saldo']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$cc['movimientos']): ?>
        <tr><td colspan="7" style="text-align: center; color: #777;">Sin movimientos en el período.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<table class="resumen">
    <?php if ($cc['desde']): ?><tr><td>Saldo anterior</td><td class="text-end"><?= pdfMoneda($cc['saldo_inicial']) ?></td></tr><?php endif; ?>
    <tr><td>Facturado (Debe)</td><td class="text-end"><?= pdfMoneda($cc['totales']['debe']) ?></td></tr>
    <tr><td>Cobrado y retenido (Haber)</td><td class="text-end"><?= pdfMoneda($cc['totales']['haber']) ?></td></tr>
    <tr class="final"><td>SALDO A COBRAR</td><td class="text-end"><?= pdfMoneda($cc['totales']['saldo']) ?></td></tr>
</table>

<?php if ($cc['pendientes']): ?>
<h4>Facturas con saldo pendiente</h4>
<table class="tabla">
    <thead>
        <tr>
            <th>Comprobante</th>
            <th style="width: 14%;">Fecha</th>
            <th style="width: 14%;">Vencimiento</th>
            <th class="text-end" style="width: 16%;">Total</th>
            <th class="text-end" style="width: 16%;">Saldo</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cc['pendientes'] as $p): ?>
        <tr>
            <td><?= pdfTexto($p['comprobante']) ?></td>
            <td><?= $f($p['fecha']) ?></td>
            <td><?= $f($p['vencimiento']) ?></td>
            <td class="text-end"><?= pdfMoneda($p['total']) ?></td>
            <td class="text-end"><strong><?= pdfMoneda($p['saldo']) ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

</body>
</html>
<?php
$html = ob_get_clean();
pdfEnviar($dompdf, $html, 'cuenta_corriente_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $cli['nombre']) . '.pdf');
