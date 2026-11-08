<?php
// =====================================================================
// PDF DE APU DEL PRESUPUESTO (para el cliente): una hoja por APU usada
// apu_pdf.php?id=X             -> última versión guardada
// apu_pdf.php?id=X&version=Y   -> la versión Y (ID del registro en presupuesto_versiones)
//
// - El precio de cada insumo ya tiene el K Base aplicado (K Mat en materiales,
//   K MO en mano de obra, equipos y subcontratos). El K Base no se muestra.
// - Al pie: costo unitario x K final = precio unitario. El K final sí se muestra.
// - Los insumos salen de las APU guardadas en la versión ("apus" del snapshot).
//   Las versiones anteriores a ese guardado usan las APU vigentes del catálogo
//   y la hoja lo aclara.
// =====================================================================

// Sin errores en pantalla para no romper el PDF
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../includes/auth.php'; // solo usuarios con sesión iniciada
require_once __DIR__ . '/includes/pdf_comun.php';
require_once __DIR__ . '/includes/apu_snapshot.php';

const APU_TIPOS = [
    'MATERIAL'    => 'Materiales',
    'MO'          => 'Mano de obra',
    'EQUIPO'      => 'Equipos',
    'SUBCONTRATO' => 'Subcontratos',
];

$id = intval($_GET['id'] ?? 0);
$version_id = intval($_GET['version'] ?? 0);

[$snap, $version_numero] = pdfCargarVersion($conn, $id, $version_id);
$p = pdfDatosGenerales($conn, $snap, $version_numero);
$k_final = pdfKFinal($p);

// 1. APU usadas, en el orden en que aparecen, con los ítems donde se usan
$usos = []; // tarea_id => [item_num, ...]
foreach (($snap['rubros'] ?? []) as $rubro) {
    foreach (($rubro['items'] ?? []) as $it) {
        $tid = intval($it['tarea_id'] ?? 0);
        if ($tid > 0) {
            $usos[$tid][] = trim((string)($it['item_num'] ?? ''));
        }
    }
}

// 2. Insumos: los guardados en la versión o, si no los tiene, los vigentes del catálogo
$apus_version = is_array($snap['apus'] ?? null) ? $snap['apus'] : [];
$desde_catalogo = empty($apus_version) && !empty($usos);
if ($desde_catalogo) {
    $k_base = apuKBase($conn, $snap['coeficiente_k_base'] ?? '');
    $apus_version = [];
    foreach (apuCargarActuales($conn, array_keys($usos)) as $tid => $apu) {
        $apus_version[$tid] = $apu + ['k_mat' => $k_base['mat'], 'k_mo' => $k_base['mo']];
    }
}

// 3. Hojas: precios con K Base redondeados a centavos para que cada hoja sume exacto
$hojas = [];
$sin_detalle = []; // APU usadas que ya no existen en el catálogo ni en la versión
foreach ($usos as $tid => $items) {
    $apu = $apus_version[$tid] ?? $apus_version[(string)$tid] ?? null;
    if (!$apu) {
        $sin_detalle[] = implode(', ', array_filter($items));
        continue;
    }
    $k_mat = floatval($apu['k_mat'] ?? 1) ?: 1;
    $k_mo  = floatval($apu['k_mo'] ?? 1) ?: 1;

    $grupos = [];
    $detalles = $apu['detalles'] ?? [];
    if (empty($detalles)) {
        // APU sin insumos cargados: una sola línea con su costo unitario total
        $detalles = [[
            'tipo' => 'MATERIAL',
            'descripcion' => 'Costo unitario total (sin desglose de insumos)',
            'unidad' => $apu['unidad'] ?? '',
            'cantidad' => 1,
            'precio_unitario' => floatval($apu['costo_unitario_total'] ?? 0),
        ]];
    }
    foreach ($detalles as $d) {
        $tipo = strtoupper($d['tipo'] ?? 'MATERIAL');
        if (!isset(APU_TIPOS[$tipo])) $tipo = 'MATERIAL';
        $cant = floatval($d['cantidad'] ?? 0);
        $unit = round(floatval($d['precio_unitario'] ?? 0) * ($tipo === 'MATERIAL' ? $k_mat : $k_mo), 2);
        $sub  = round($cant * $unit, 2);
        $grupos[$tipo]['filas'][] = [
            'descripcion' => $d['descripcion'] ?? '',
            'unidad' => $d['unidad'] ?? '',
            'cantidad' => $cant,
            'unitario' => $unit,
            'subtotal' => $sub,
        ];
        $grupos[$tipo]['subtotal'] = ($grupos[$tipo]['subtotal'] ?? 0) + $sub;
    }

    // Orden fijo: materiales, mano de obra, equipos, subcontratos
    $grupos_ordenados = [];
    foreach (array_keys(APU_TIPOS) as $tipo) {
        if (isset($grupos[$tipo])) $grupos_ordenados[$tipo] = $grupos[$tipo];
    }

    $costo_unitario = array_sum(array_column($grupos_ordenados, 'subtotal'));
    $hojas[] = [
        'codigo' => $apu['codigo'] ?? '',
        'nombre' => $apu['nombre'] ?? '',
        'unidad' => $apu['unidad'] ?? '',
        'items' => array_values(array_unique(array_filter($items))),
        'grupos' => $grupos_ordenados,
        'costo_unitario' => $costo_unitario,
        'precio_unitario' => round($costo_unitario * $k_final, 2),
    ];
}

$logo_base64 = pdfLogoBase64();
$dompdf = pdfCrearDompdf();

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>APU - Presupuesto <?= pdfTexto($p['codigo']) ?></title>
    <style>
        @page { margin: 30px 35px 40px 35px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; }

        .hoja { page-break-after: always; }
        .hoja.ultima { page-break-after: auto; }

        .encabezado { width: 100%; border-bottom: 2px solid #333; padding-bottom: 6px; margin-bottom: 12px; }
        .encabezado h2 { margin: 0; font-size: 17px; text-transform: uppercase; }
        .encabezado .sub { margin: 3px 0 0 0; color: #555; font-size: 11px; }

        .info-box { border: 1px solid #ccc; background-color: #f9f9f9; padding: 8px; margin-bottom: 12px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 3px; vertical-align: top; }
        .lbl { font-weight: bold; width: 13%; }

        .apu-titulo { border: 1px solid #212529; margin-bottom: 12px; }
        .apu-titulo td { padding: 6px 8px; }
        .apu-titulo .nombre { background-color: #212529; color: #fff; font-weight: bold; font-size: 12px; }
        .apu-titulo .datos { font-size: 10px; }

        .items { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .items th, .items td { border: 1px solid #bbb; padding: 4px 6px; font-size: 10px; }
        .items th { background-color: #e2e3e5; text-align: left; }
        .grupo td { background-color: #212529; color: #fff; font-weight: bold; font-size: 10px; }
        .sub-grupo td { background-color: #f8f9fa; font-weight: bold; }

        .text-center { text-align: center; }
        .text-end { text-align: right; }

        .totales { width: 50%; margin-left: 50%; border-collapse: collapse; }
        .totales td { padding: 6px 8px; border: 1px solid #ccc; font-size: 11px; }
        .totales .final td { background-color: #212529; color: #fff; font-weight: bold; font-size: 12px; }

        .nota { margin-top: 10px; font-size: 9px; color: #666; }
    </style>
</head>
<body>

<?php if (empty($hojas)): ?>
    <div class="info-box">Este presupuesto no tiene ítems con APU disponibles en la versión <?= $p['version_numero'] ?>.</div>
    <?php if (!empty($sin_detalle)): ?>
    <p class="nota">Sin análisis disponible para el/los ítem(s): <?= pdfTexto(implode(', ', $sin_detalle)) ?>.</p>
    <?php endif; ?>
<?php endif; ?>

<?php foreach ($hojas as $n => $h): ?>
<div class="hoja<?= $n === count($hojas) - 1 ? ' ultima' : '' ?>">

    <table class="encabezado">
        <tr>
            <td style="vertical-align: middle;">
                <h2>Análisis de Precio Unitario</h2>
                <p class="sub">Presupuesto <?= pdfTexto($p['codigo']) ?> &nbsp;·&nbsp; Fecha: <?= $p['fecha'] ? date('d/m/Y', strtotime($p['fecha'])) : '-' ?> &nbsp;·&nbsp; Versión <?= $p['version_numero'] ?></p>
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
                <td class="lbl">Obra:</td>
                <td><?= pdfTexto($p['obra_nombre']) ?></td>
            </tr>
            <?php if (!empty($p['titulo'])): ?>
            <tr>
                <td class="lbl">Proyecto:</td>
                <td colspan="3" style="font-weight: bold;"><?= pdfTexto($p['titulo']) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <table class="apu-titulo" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td class="nombre" colspan="3"><?= $h['codigo'] !== '' ? '[' . pdfTexto($h['codigo']) . '] ' : '' ?><?= pdfTexto($h['nombre']) ?></td>
        </tr>
        <tr class="datos">
            <td><b>Unidad:</b> <?= pdfTexto($h['unidad'] ?: '-') ?></td>
            <td><b>Ítem<?= count($h['items']) > 1 ? 's' : '' ?> del presupuesto:</b> <?= pdfTexto(implode(', ', $h['items']) ?: '-') ?></td>
            <td class="text-end"><b>Hoja</b> <?= $n + 1 ?> de <?= count($hojas) ?></td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Descripción</th>
                <th style="width: 9%;" class="text-center">Unidad</th>
                <th style="width: 11%;" class="text-end">Cantidad</th>
                <th style="width: 16%;" class="text-end">P. Unitario</th>
                <th style="width: 16%;" class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($h['grupos'] as $tipo => $g): ?>
                <tr class="grupo"><td colspan="5"><?= pdfTexto(APU_TIPOS[$tipo]) ?></td></tr>
                <?php foreach ($g['filas'] as $f): ?>
                <tr>
                    <td><?= pdfTexto($f['descripcion']) ?></td>
                    <td class="text-center"><?= pdfTexto($f['unidad']) ?></td>
                    <td class="text-end"><?= pdfCantidadInsumo($f['cantidad']) ?></td>
                    <td class="text-end"><?= pdfMoneda($f['unitario']) ?></td>
                    <td class="text-end"><?= pdfMoneda($f['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="sub-grupo">
                    <td colspan="4" class="text-end">Subtotal <?= pdfTexto(APU_TIPOS[$tipo]) ?></td>
                    <td class="text-end"><?= pdfMoneda($g['subtotal']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td>Costo unitario (por <?= pdfTexto($h['unidad'] ?: 'unidad') ?>)</td>
            <td class="text-end"><?= pdfMoneda($h['costo_unitario']) ?></td>
        </tr>
        <tr>
            <td>Coeficiente K</td>
            <td class="text-end"><?= number_format($k_final, 2, ',', '.') ?></td>
        </tr>
        <tr class="final">
            <td>PRECIO UNITARIO</td>
            <td class="text-end"><?= pdfMoneda($h['precio_unitario']) ?></td>
        </tr>
    </table>

    <?php if ($desde_catalogo): ?>
    <p class="nota">Valores de la APU vigente al <?= date('d/m/Y') ?>.</p>
    <?php endif; ?>
    <?php if ($n === count($hojas) - 1 && !empty($sin_detalle)): ?>
    <p class="nota">Sin análisis disponible para el/los ítem(s): <?= pdfTexto(implode(', ', $sin_detalle)) ?>.</p>
    <?php endif; ?>
</div>
<?php endforeach; ?>

</body>
</html>
<?php
$html = ob_get_clean();
pdfEnviar($dompdf, $html, pdfNombreArchivo($p, $id, '_APU'));
