<?php
// =====================================================================
// PDF DEL PRESUPUESTO (para el cliente)
// pdf.php?id=X             -> última versión guardada
// pdf.php?id=X&version=Y   -> la versión Y (ID del registro en presupuesto_versiones)
// &k=1                     -> muestra el coeficiente K: precios de los ítems sin el K
//                             final y abajo Subtotal costo directo x K = Total neto.
//                             Sin k (o k=0): precios con el K ya incluido, sin mostrarlo.
// &iva=0                   -> sin IVA: termina en el Total neto (por defecto suma IVA 21%)
// &logo=0                  -> sin el logo en el encabezado
// &ver=1                   -> muestra el N° de versión (por defecto no se muestra)
// Todo sale del snapshot_json de esa versión; no se usan los datos actuales
// del presupuesto (salvo el nombre/CUIT/dirección del cliente y el nombre
// de la obra, que el snapshot guarda solo por ID).
// =====================================================================

// Sin errores en pantalla para no romper el PDF
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../includes/auth.php'; // solo usuarios con sesión iniciada
require_once __DIR__ . '/includes/condiciones_html.php';
require_once __DIR__ . '/includes/pdf_comun.php';

const PDF_IVA_PORCENTAJE = 21.0; // IVA que se suma al final, sobre el total neto

$id = intval($_GET['id'] ?? 0);
$version_id = intval($_GET['version'] ?? 0);
$mostrar_k = ($_GET['k'] ?? '0') === '1';
$mostrar_iva = ($_GET['iva'] ?? '1') !== '0';
$mostrar_logo = ($_GET['logo'] ?? '1') !== '0';
$mostrar_version = ($_GET['ver'] ?? '0') === '1'; // por defecto el cliente no ve el N° de versión

// 1. Versión a imprimir y 2. datos generales tal como quedaron en esa versión
[$snap, $version_numero] = pdfCargarVersion($conn, $id, $version_id);
$rubros = $snap['rubros'] ?? [];
$condiciones_html = limpiarCondicionesHtml($snap['condiciones'] ?? '');
$p = pdfDatosGenerales($conn, $snap, $version_numero);

// 3. Coeficiente que se aplica a los precios netos de cada ítem
$k = pdfKFinal($p);

// 4. Precios para el cliente, redondeados a centavos para que el PDF sume exacto:
//    subtotal = cantidad x unitario, subtotal de rubro = suma de sus ítems.
//    - Sin K: el unitario ya lleva el K; total neto = suma de los rubros
//      (puede diferir en centavos del total del sistema).
//    - Con K: el unitario es el costo directo (solo K Base); total neto =
//      subtotal costo directo x K.
$factor_items = $mostrar_k ? 1 : $k;
$subtotal_costo = 0;
foreach ($rubros as &$rubro) {
    $rubro['subtotal'] = 0;
    foreach (($rubro['items'] ?? []) as $i => $it) {
        $cant = floatval($it['cantidad'] ?? 0);
        $unit = round(floatval($it['precio_unitario'] ?? 0) * $factor_items, 2);
        $sub  = round($cant * $unit, 2);
        $rubro['items'][$i]['pdf_cantidad'] = $cant;
        $rubro['items'][$i]['pdf_unitario'] = $unit;
        $rubro['items'][$i]['pdf_subtotal'] = $sub;
        $rubro['subtotal'] += $sub;
    }
    $subtotal_costo += $rubro['subtotal'];
}
unset($rubro);

$total_neto = $mostrar_k ? round($subtotal_costo * $k, 2) : $subtotal_costo;

$iva = round($total_neto * PDF_IVA_PORCENTAJE / 100, 2);
$total_final = $total_neto + $iva;

// 5. Logo y Dompdf
$logo_base64 = $mostrar_logo ? pdfLogoBase64() : '';
$dompdf = pdfCrearDompdf();

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto <?= pdfTexto($p['codigo']) ?></title>
    <style>
        @page { margin: 30px 35px 40px 35px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; }

        .encabezado { width: 100%; border-bottom: 2px solid #333; padding-bottom: 6px; margin-bottom: 12px; }
        .encabezado h2 { margin: 0; font-size: 17px; text-transform: uppercase; }
        .encabezado .sub { margin: 3px 0 0 0; color: #555; font-size: 11px; }

        .info-box { border: 1px solid #ccc; background-color: #f9f9f9; padding: 8px; margin-bottom: 14px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 3px; vertical-align: top; }
        .lbl { font-weight: bold; width: 13%; }

        .items { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .items th, .items td { border: 1px solid #bbb; padding: 5px 6px; font-size: 10px; }
        .items th { background-color: #212529; color: #fff; text-align: left; }
        .rubro td { background-color: #e2e3e5; font-weight: bold; font-size: 11px; }
        .sub-rubro td { background-color: #f8f9fa; font-weight: bold; }

        .text-center { text-align: center; }
        .text-end { text-align: right; }

        .totales { width: 45%; margin-left: 55%; border-collapse: collapse; }
        .totales td { padding: 6px 8px; border: 1px solid #ccc; font-size: 11px; }
        .totales .final td { background-color: #212529; color: #fff; font-weight: bold; font-size: 12px; }

        .condiciones { margin-top: 18px; font-size: 10px; color: #333; page-break-inside: auto; }
        .condiciones h4 { margin: 0 0 6px 0; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #999; padding-bottom: 3px; }
        .condiciones p { margin: 0 0 4px 0; }
        .condiciones ul, .condiciones ol { margin: 2px 0 6px 0; padding-left: 18px; }
    </style>
</head>
<body>

<table class="encabezado">
    <tr>
        <td style="vertical-align: middle;">
            <h2>Presupuesto <?= pdfTexto($p['codigo']) ?></h2>
            <p class="sub">Fecha: <?= $p['fecha'] ? date('d/m/Y', strtotime($p['fecha'])) : '-' ?><?php if ($mostrar_version): ?> &nbsp;·&nbsp; Versión <?= $p['version_numero'] ?><?php endif; ?></p>
        </td>
        <?php if ($logo_base64): ?>
        <td style="vertical-align: middle; text-align: right; width: 150px;">
            <img src="<?= $logo_base64 ?>" style="max-height: 55px; max-width: 140px;">
        </td>
        <?php endif; ?>
    </tr>
</table>

<div class="info-box">
    <table class="info-table">
        <tr>
            <td class="lbl">Cliente:</td>
            <td style="width: 47%;"><?= pdfTexto($p['cliente_nombre']) ?></td>
            <td class="lbl">CUIT:</td>
            <td><?= pdfTexto($p['cliente_cuit'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="lbl">Obra:</td>
            <td><?= pdfTexto($p['obra_nombre']) ?></td>
            <td class="lbl">Dirección:</td>
            <td><?= pdfTexto($p['cliente_direccion'] ?: '-') ?></td>
        </tr>
        <?php if (!empty($p['titulo'])): ?>
        <tr>
            <td class="lbl">Proyecto:</td>
            <td colspan="3" style="font-weight: bold;"><?= pdfTexto($p['titulo']) ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<table class="items">
    <thead>
        <tr>
            <th style="width: 7%;" class="text-center">Ítem</th>
            <th>Descripción</th>
            <th style="width: 8%;" class="text-center">Unidad</th>
            <th style="width: 9%;" class="text-end">Cant.</th>
            <th style="width: 15%;" class="text-end">P. Unitario</th>
            <th style="width: 16%;" class="text-end">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($rubros)): ?>
            <tr><td colspan="6" class="text-center" style="padding: 12px;">El presupuesto no tiene ítems guardados.</td></tr>
        <?php endif; ?>

        <?php foreach ($rubros as $rubro): ?>
            <?php $items = $rubro['items'] ?? []; ?>
            <tr class="rubro">
                <td class="text-center"><?= pdfTexto($rubro['numero'] ?? '') ?></td>
                <td colspan="5"><?= pdfTexto($rubro['titulo'] ?? '') ?></td>
            </tr>
            <?php foreach ($items as $it):
                $cant = $it['pdf_cantidad'];
                $unit = $it['pdf_unitario'];
                $sub  = $it['pdf_subtotal'];
            ?>
            <tr>
                <td class="text-center"><?= pdfTexto($it['item_num'] ?? '') ?></td>
                <td><?= nl2br(pdfTexto($it['detalle'] ?? '')) ?></td>
                <td class="text-center"><?= pdfTexto($it['unidad'] ?? '') ?></td>
                <td class="text-end"><?= pdfCantidad($cant) ?></td>
                <td class="text-end"><?= pdfMoneda($unit) ?></td>
                <td class="text-end"><?= pdfMoneda($sub) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="sub-rubro">
                <td colspan="5" class="text-end">Subtotal <?= pdfTexto($rubro['titulo'] ?? '') ?></td>
                <td class="text-end"><?= pdfMoneda($rubro['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="totales">
    <?php if ($mostrar_k): ?>
    <tr>
        <td>Subtotal costo directo</td>
        <td class="text-end"><?= pdfMoneda($subtotal_costo) ?></td>
    </tr>
    <tr>
        <td>Coeficiente K</td>
        <td class="text-end"><?= number_format($k, 2, ',', '.') ?></td>
    </tr>
    <?php endif; ?>
    <?php if ($mostrar_iva): ?>
    <tr>
        <td>Total neto</td>
        <td class="text-end"><?= pdfMoneda($total_neto) ?></td>
    </tr>
    <tr>
        <td>IVA <?= number_format(PDF_IVA_PORCENTAJE, 0) ?>%</td>
        <td class="text-end"><?= pdfMoneda($iva) ?></td>
    </tr>
    <tr class="final">
        <td>TOTAL FINAL</td>
        <td class="text-end"><?= pdfMoneda($total_final) ?></td>
    </tr>
    <?php else: ?>
    <tr class="final">
        <td>TOTAL NETO</td>
        <td class="text-end"><?= pdfMoneda($total_neto) ?></td>
    </tr>
    <?php endif; ?>
</table>

<?php if ($condiciones_html !== ''): ?>
<div class="condiciones">
    <h4>Condiciones generales</h4>
    <?= $condiciones_html /* HTML ya limpio: solo negrita, cursiva, subrayado, párrafos y listas */ ?>
</div>
<?php endif; ?>

</body>
</html>
<?php
$html = ob_get_clean();
pdfEnviar($dompdf, $html, pdfNombreArchivo($p, $id, '', $mostrar_version)); // con número de página al pie
