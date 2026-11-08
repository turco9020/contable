<?php
// =====================================================================
// PDF DEL RESUMEN DE RECURSOS (uso interno: lo que hace falta para ejecutar la obra)
// resumen_pdf.php?id=X             -> última versión guardada
// resumen_pdf.php?id=X&version=Y   -> la versión Y (ID del registro en presupuesto_versiones)
// &costos=1                        -> con costos netos (para el arquitecto);
//                                     sin costos o costos=0: solo cantidades (para el capataz)
// &logo=0                          -> sin el logo en el encabezado
// &ver=1                           -> muestra el N° de versión
//
// Igual que la pestaña Resumen: suma los insumos de cada APU x la cantidad del
// ítem, agrupados por tipo y por descripción + unidad. Los costos son netos
// (precio del insumo sin K Base ni K final).
// Diferencia con la pestaña: acá se usan las APU guardadas en la versión
// ("apus" del snapshot); las versiones anteriores a ese guardado usan las APU
// vigentes del catálogo. La pestaña usa lo que está en pantalla.
// =====================================================================

// Sin errores en pantalla para no romper el PDF
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../includes/auth.php'; // solo usuarios con sesión iniciada
require_once __DIR__ . '/includes/pdf_comun.php';
require_once __DIR__ . '/includes/apu_snapshot.php';

// Mismo orden que la pestaña Resumen
const RES_TIPOS = [
    'MO'          => 'Mano de obra',
    'MATERIAL'    => 'Materiales',
    'EQUIPO'      => 'Equipos',
    'SUBCONTRATO' => 'Subcontratos',
];

$id = intval($_GET['id'] ?? 0);
$version_id = intval($_GET['version'] ?? 0);
$mostrar_costos = ($_GET['costos'] ?? '0') === '1';
$mostrar_logo = ($_GET['logo'] ?? '1') !== '0';
$mostrar_version = ($_GET['ver'] ?? '0') === '1';

[$snap, $version_numero] = pdfCargarVersion($conn, $id, $version_id);
$p = pdfDatosGenerales($conn, $snap, $version_numero);

// 1. APU de la versión (o del catálogo si la versión no las guardó)
$tarea_ids = [];
foreach (($snap['rubros'] ?? []) as $rubro) {
    foreach (($rubro['items'] ?? []) as $it) {
        $tid = intval($it['tarea_id'] ?? 0);
        if ($tid > 0) $tarea_ids[$tid] = true;
    }
}
$apus = is_array($snap['apus'] ?? null) ? $snap['apus'] : [];
if (empty($apus) && !empty($tarea_ids)) {
    $apus = apuCargarActuales($conn, array_keys($tarea_ids));
}

// 2. Acumulado por tipo y por descripción + unidad
$acum = array_fill_keys(array_keys(RES_TIPOS), []);
$items_con_apu = 0;
$items_sin_apu = 0;
foreach (($snap['rubros'] ?? []) as $rubro) {
    foreach (($rubro['items'] ?? []) as $it) {
        $tid = intval($it['tarea_id'] ?? 0);
        if ($tid <= 0) {
            // Ítem de detalle libre (sin APU): se avisa si tiene contenido
            if (trim((string)($it['detalle'] ?? '')) !== '') $items_sin_apu++;
            continue;
        }
        $apu = $apus[$tid] ?? $apus[(string)$tid] ?? null;
        $detalles = $apu['detalles'] ?? [];
        if (empty($detalles)) { $items_sin_apu++; continue; }
        $items_con_apu++;

        $cant_item = floatval($it['cantidad'] ?? 0);
        foreach ($detalles as $d) {
            $tipo = strtoupper($d['tipo'] ?? '');
            if (!isset($acum[$tipo])) continue;
            $desc = trim((string)($d['descripcion'] ?? ''));
            $unidad = mb_strtoupper(trim((string)($d['unidad'] ?? '')), 'UTF-8');
            $clave = mb_strtoupper($desc, 'UTF-8') . '|' . $unidad;
            if (!isset($acum[$tipo][$clave])) {
                $acum[$tipo][$clave] = ['descripcion' => $desc, 'unidad' => $unidad, 'cantidad' => 0, 'costo' => 0];
            }
            $cant_insumo = floatval($d['cantidad'] ?? 0);
            $acum[$tipo][$clave]['cantidad'] += $cant_insumo * $cant_item;
            $acum[$tipo][$clave]['costo'] += $cant_insumo * floatval($d['precio_unitario'] ?? 0) * $cant_item;
        }
    }
}

// 3. Orden alfabético, costos redondeados a centavos para que los totales sumen exacto
$grupos = [];
$costo_total = 0;
foreach ($acum as $tipo => $filas) {
    uasort($filas, function($a, $b) { return strcasecmp($a['descripcion'], $b['descripcion']); });
    $costo = 0;
    $por_unidad = [];
    foreach ($filas as $k => $f) {
        $filas[$k]['costo'] = round($f['costo'], 2);
        $costo += $filas[$k]['costo'];
        $por_unidad[$f['unidad']] = ($por_unidad[$f['unidad']] ?? 0) + $f['cantidad'];
    }
    $costo_total += $costo;
    $grupos[$tipo] = ['filas' => array_values($filas), 'costo' => $costo, 'por_unidad' => $por_unidad];
}

// "120 HS · 4 DIA": total de cantidades por unidad, como en la pestaña
function resTotalesPorUnidad(array $por_unidad) {
    $partes = [];
    foreach ($por_unidad as $u => $c) $partes[] = trim(pdfCantidadInsumo(round($c, 4)) . ' ' . $u);
    return $partes ? implode(' · ', $partes) : '0';
}

$tarjetas = [
    'MO'          => resTotalesPorUnidad($grupos['MO']['por_unidad']),
    'MATERIAL'    => count($grupos['MATERIAL']['filas']) . (count($grupos['MATERIAL']['filas']) === 1 ? ' insumo' : ' insumos'),
    'EQUIPO'      => resTotalesPorUnidad($grupos['EQUIPO']['por_unidad']),
    'SUBCONTRATO' => count($grupos['SUBCONTRATO']['filas']) . (count($grupos['SUBCONTRATO']['filas']) === 1 ? ' ítem' : ' ítems'),
];

$logo_base64 = $mostrar_logo ? pdfLogoBase64() : '';
$dompdf = pdfCrearDompdf();

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen de recursos - Presupuesto <?= pdfTexto($p['codigo']) ?></title>
    <style>
        @page { margin: 30px 35px 40px 35px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; }

        .encabezado { width: 100%; border-bottom: 2px solid #333; padding-bottom: 6px; margin-bottom: 12px; }
        .encabezado h2 { margin: 0; font-size: 17px; text-transform: uppercase; }
        .encabezado .sub { margin: 3px 0 0 0; color: #555; font-size: 11px; }

        .info-box { border: 1px solid #ccc; background-color: #f9f9f9; padding: 8px; margin-bottom: 12px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 3px; vertical-align: top; }
        .lbl { font-weight: bold; width: 13%; }

        .tarjetas { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 14px -6px; }
        .tarjetas td { width: 25%; border: 1px solid #bbb; padding: 6px; text-align: center; vertical-align: top; }
        .tarjetas .t-titulo { font-size: 9px; font-weight: bold; color: #555; text-transform: uppercase; }
        .tarjetas .t-valor { font-size: 12px; font-weight: bold; margin-top: 3px; }
        .tarjetas .t-sub { font-size: 9px; color: #555; margin-top: 2px; }

        .items { width: 100%; border-collapse: collapse; margin-bottom: 14px; page-break-inside: auto; }
        .items th, .items td { border: 1px solid #bbb; padding: 4px 6px; font-size: 10px; }
        .items th { background-color: #e2e3e5; text-align: left; }
        .items tr { page-break-inside: avoid; }
        .grupo td { background-color: #212529; color: #fff; font-weight: bold; font-size: 11px; }
        .sub-grupo td { background-color: #f8f9fa; font-weight: bold; }
        .check { width: 6%; }

        .text-center { text-align: center; }
        .text-end { text-align: right; }

        .totales { width: 50%; margin-left: 50%; border-collapse: collapse; }
        .totales .final td { background-color: #212529; color: #fff; font-weight: bold; font-size: 12px; padding: 6px 8px; }

        .aviso { border: 1px solid #e0b252; background-color: #fff6e0; padding: 6px 8px; margin-bottom: 12px; font-size: 10px; }
        .nota { margin-top: 10px; font-size: 9px; color: #666; }
    </style>
</head>
<body>

<table class="encabezado">
    <tr>
        <td style="vertical-align: middle;">
            <h2>Resumen de recursos</h2>
            <p class="sub">Presupuesto <?= pdfTexto($p['codigo']) ?> &nbsp;·&nbsp; Fecha: <?= $p['fecha'] ? date('d/m/Y', strtotime($p['fecha'])) : '-' ?><?php if ($mostrar_version): ?> &nbsp;·&nbsp; Versión <?= $p['version_numero'] ?><?php endif; ?></p>
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

<?php if ($items_con_apu === 0): ?>
    <div class="info-box">Este presupuesto no tiene ítems con APU e insumos cargados, por lo que no hay recursos para resumir.</div>
<?php else: ?>

    <?php if ($items_sin_apu > 0): ?>
    <div class="aviso"><?= $items_sin_apu ?> ítem(s) no tienen una APU con insumos cargados (detalle libre o APU sin desglose), por lo que no se incluyen en este resumen.</div>
    <?php endif; ?>

    <table class="tarjetas">
        <tr>
            <?php foreach (RES_TIPOS as $tipo => $titulo): ?>
            <td>
                <div class="t-titulo"><?= pdfTexto($titulo) ?></div>
                <div class="t-valor"><?= pdfTexto($tarjetas[$tipo]) ?></div>
                <?php if ($mostrar_costos): ?>
                <div class="t-sub"><?= pdfMoneda($grupos[$tipo]['costo']) ?> neto</div>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
        </tr>
    </table>

    <?php foreach (RES_TIPOS as $tipo => $titulo):
        $g = $grupos[$tipo];
        if (empty($g['filas'])) continue;
        // Sin costos, la última columna queda vacía para tildar lo que se va consiguiendo
    ?>
    <table class="items">
        <thead>
            <tr class="grupo"><td colspan="4"><?= pdfTexto($titulo) ?></td></tr>
            <tr>
                <th>Descripción</th>
                <th style="width: 10%;" class="text-center">Unidad</th>
                <th style="width: 14%;" class="text-end">Cant. total</th>
                <?php if ($mostrar_costos): ?>
                <th style="width: 18%;" class="text-end">Costo neto</th>
                <?php else: ?>
                <th class="check text-center">OK</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($g['filas'] as $f): ?>
            <tr>
                <td><?= pdfTexto($f['descripcion']) ?></td>
                <td class="text-center"><?= pdfTexto($f['unidad']) ?></td>
                <td class="text-end"><?= pdfCantidadInsumo(round($f['cantidad'], 4)) ?></td>
                <?php if ($mostrar_costos): ?>
                <td class="text-end"><?= pdfMoneda($f['costo']) ?></td>
                <?php else: ?>
                <td></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <tr class="sub-grupo">
                <td colspan="2" class="text-end">Total <?= pdfTexto($titulo) ?></td>
                <td class="text-end"><?= pdfTexto(resTotalesPorUnidad($g['por_unidad'])) ?></td>
                <?php if ($mostrar_costos): ?>
                <td class="text-end"><?= pdfMoneda($g['costo']) ?></td>
                <?php else: ?>
                <td></td>
                <?php endif; ?>
            </tr>
        </tbody>
    </table>
    <?php endforeach; ?>

    <?php if ($mostrar_costos): ?>
    <table class="totales">
        <tr class="final">
            <td>COSTO DIRECTO NETO (sin K)</td>
            <td class="text-end"><?= pdfMoneda($costo_total) ?></td>
        </tr>
    </table>
    <?php endif; ?>

    <p class="nota">Cantidades calculadas con las APU de la versión guardada del presupuesto (insumos de cada APU × cantidad del ítem).</p>
<?php endif; ?>

</body>
</html>
<?php
$html = ob_get_clean();
pdfEnviar($dompdf, $html, pdfNombreArchivo($p, $id, '_Resumen', $mostrar_version));
