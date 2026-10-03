<?php
// C:\xampp\htdocs\contable\modules\presupuestos\ajax\generar_pdf_tarea.php

require_once '../../../config/database.php';
require_once '../../../vendor/dompdf/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$tarea_id  = intval($_GET['id'] ?? 0);
$k_id      = intval($_GET['k_id'] ?? 0);
$k_visible = intval($_GET['k_visible'] ?? 0);
$con_logo  = intval($_GET['logo'] ?? 0);

// 1. Obtener la cabecera del APU
$stmt = $conn->prepare("SELECT * FROM presupuesto_tareas WHERE id = ?");
$stmt->bind_param("i", $tarea_id);
$stmt->execute();
$tarea = $stmt->get_result()->fetch_assoc();

if (!$tarea) { 
    die("Tarea no encontrada."); 
}

// 2. Obtener los renglones del desglose desde 'presupuesto_tarea_detalles'
$stmt_det = $conn->prepare("SELECT * FROM presupuesto_tarea_detalles WHERE tarea_id = ? ORDER BY id ASC");
$stmt_det->bind_param("i", $tarea_id);
$stmt_det->execute();
$res_det = $stmt_det->get_result();

// 3. Obtener Coeficiente K si se seleccionó plantilla
$k_mat = 1.000;
$k_mo  = 1.000;
$nombre_perfil = "Costo Neto Directo (Sin K)";

if ($k_id > 0) {
    $stmt_k = $conn->prepare("SELECT nombre, mat_k_resultante, mo_k_resultante FROM presupuesto_coeficientes_plantillas WHERE id = ?");
    $stmt_k->bind_param("i", $k_id);
    $stmt_k->execute();
    $perfil = $stmt_k->get_result()->fetch_assoc();
    if ($perfil) {
        $k_mat = floatval($perfil['mat_k_resultante']);
        $k_mo  = floatval($perfil['mo_k_resultante']);
        $nombre_perfil = $perfil['nombre'];
    }
}

// 4. Procesar y clasificar renglones APLICANDO SIEMPRE K al costo unitario
$detalles = [
    'MATERIAL' => [],
    'MO' => [],
    'EQUIPO' => [],
    'SUBCONTRATO' => []
];

$subtotales = [
    'MATERIAL' => 0,
    'MO' => 0,
    'EQUIPO' => 0,
    'SUBCONTRATO' => 0
];

// Mantenemos también el subtotal neto (sin K) solo para la fila informativa cuando K visible = 1
$subtotales_netos = [
    'MATERIAL' => 0,
    'MO' => 0,
    'EQUIPO' => 0,
    'SUBCONTRATO' => 0
];

while ($row = $res_det->fetch_assoc()) {
    $tipo = strtoupper($row['tipo']);
    
    if (isset($detalles[$tipo])) {
        $pu_base = floatval($row['precio_unitario']);
        $cant    = floatval($row['cantidad']);
        
        // Registrar costo neto directo
        $subtotales_netos[$tipo] += ($cant * $pu_base);

        // APLICACIÓN UNIFORME DE K (Tanto si K es visible como si no lo es)
        if ($tipo === 'MATERIAL') {
            $row['precio_unitario'] = $pu_base * $k_mat;
        } else {
            // MO, EQUIPO y SUBCONTRATO aplican K MO
            $row['precio_unitario'] = $pu_base * $k_mo;
        }

        $row['subtotal'] = $cant * $row['precio_unitario'];

        $detalles[$tipo][] = $row;
        $subtotales[$tipo] += floatval($row['subtotal']);
    }
}

// Totales generales
$costo_neto_total = array_sum($subtotales_netos);
$total_general_k  = array_sum($subtotales);

// Subtotales con K por tipo para el cuadro informativo
$total_mat_k = $subtotales['MATERIAL'];
$total_mo_k  = $subtotales['MO'] + $subtotales['EQUIPO'] + $subtotales['SUBCONTRATO'];

// Función auxiliar para renderizar los renglones
function renderFilasRubro($items) {
    if (empty($items)) {
        return '<tr><td colspan="5" style="color: #777; font-style: italic;">Sin ítems en este rubro</td></tr>';
    }
    $html = '';
    foreach ($items as $item) {
        $obs = !empty($item['observaciones']) ? ' <small style="color: #666;">(' . htmlspecialchars($item['observaciones']) . ')</small>' : '';
        $html .= '<tr>
            <td>' . htmlspecialchars($item['descripcion']) . $obs . '</td>
            <td class="text-center">' . htmlspecialchars($item['unidad']) . '</td>
            <td class="text-end">$ ' . number_format($item['precio_unitario'], 2, ',', '.') . '</td>
            <td class="text-end">' . number_format($item['cantidad'], 4, ',', '.') . '</td>
            <td class="text-end">$ ' . number_format($item['subtotal'], 2, ',', '.') . '</td>
        </tr>';
    }
    return $html;
}

// Pre-procesamiento del Logo (usando JPG)
$logo_html = '';
if ($con_logo === 1) {
    $ruta_logo = $_SERVER['DOCUMENT_ROOT'] . '/contable/assets/img/logo-pdf.jpg';
    $logo_base64 = '';
    
    if (file_exists($ruta_logo)) {
        $logo_data = file_get_contents($ruta_logo);
        $logo_base64 = 'data:image/jpeg;base64,' . base64_encode($logo_data);
    }

    $logo_html = '
    <table style="width: 100%; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 5px;">
        <tr>
            <td style="vertical-align: middle; text-align: left;">
                <h2 style="margin: 0; font-size: 16px; text-transform: uppercase;">ANÁLISIS DE PRECIO UNITARIO (APU)</h2>
                <p style="margin: 2px 0 0 0; color: #666; font-size: 10px;">Sistema de Gestión de Presupuestos</p>
            </td>
            <td style="vertical-align: middle; text-align: right; width: 140px;">';
    if (!empty($logo_base64)) {
        $logo_html .= '<img src="' . $logo_base64 . '" style="max-height: 50px; max-width: 130px;">';
    }
    $logo_html .= '
            </td>
        </tr>
    </table>';
} else {
    $logo_html = '
    <div class="header-title">
        <h2>ANÁLISIS DE PRECIO UNITARIO</h2>
        <p>Sistema de Gestión de Presupuestos</p>
    </div>';
}

// 5. Estructura HTML para Dompdf
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 0; }
        .header-title { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 5px; }
        .header-title h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header-title p { margin: 2px 0 0 0; color: #666; font-size: 10px; }

        .info-box { width: 100%; border: 1px solid #ccc; background-color: #f9f9f9; padding: 8px; margin-bottom: 15px; border-radius: 4px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 3px; vertical-align: top; }
        .fw-bold { font-weight: bold; }

        .table-desglose { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-desglose th, .table-desglose td { border: 1px solid #bbb; padding: 5px 6px; font-size: 10px; }
        .table-desglose th { background-color: #212529; color: #ffffff; text-align: left; }
        
        .rubro-header { background-color: #e2e3e5; font-weight: bold; }
        .rubro-header td { padding: 6px; font-size: 11px; }

        .text-center { text-align: center; }
        .text-end { text-align: right; }

        .totales-box { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .totales-box td { padding: 6px; border: 1px solid #ccc; }
        .bg-dark-row { background-color: #212529; color: #fff; font-weight: bold; }
        .bg-k-row { background-color: #f8f9fa; }
    </style>
</head>
<body>

    ' . $logo_html . '

    <!-- DATOS GENERALES -->
    <div class="info-box">
        <table class="info-table">
            <tr>
                <td style="width: 12%;" class="fw-bold">Código:</td>
                <td style="width: 20%;">' . htmlspecialchars($tarea['codigo'] ?: '-') . '</td>
                <td style="width: 12%;" class="fw-bold">Unidad:</td>
                <td style="width: 18%;">' . htmlspecialchars($tarea['unidad']) . '</td>
            </tr>
            <tr>
                <td class="fw-bold">Tarea:</td>
                <td colspan="5" style="font-size: 12px;" class="fw-bold">' . htmlspecialchars($tarea['nombre']) . '</td>
            </tr>
        </table>
    </div>

    <!-- TABLA DESGLOSE -->
    <table class="table-desglose">
        <thead>
            <tr>
                <th>Recurso / Descripción</th>
                <th style="width: 50px;" class="text-center">Unidad</th>
                <th style="width: 80px;" class="text-end">Costo Unit ($)</th>
                <th style="width: 65px;" class="text-end">Cantidad</th>
                <th style="width: 90px;" class="text-end">Subtotal ($)</th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. MATERIALES -->
            <tr class="rubro-header">
                <td colspan="4">1. MATERIALES</td>
                <td class="text-end">$ ' . number_format($subtotales['MATERIAL'], 2, ',', '.') . '</td>
            </tr>
            ' . renderFilasRubro($detalles['MATERIAL']) . '

            <!-- 2. MANO DE OBRA -->
            <tr class="rubro-header">
                <td colspan="4">2. MANO DE OBRA</td>
                <td class="text-end">$ ' . number_format($subtotales['MO'], 2, ',', '.') . '</td>
            </tr>
            ' . renderFilasRubro($detalles['MO']) . '

            <!-- 3. EQUIPOS -->
            <tr class="rubro-header">
                <td colspan="4">3. EQUIPOS Y HERRAMIENTAS</td>
                <td class="text-end">$ ' . number_format($subtotales['EQUIPO'], 2, ',', '.') . '</td>
            </tr>
            ' . renderFilasRubro($detalles['EQUIPO']) . '

            <!-- 4. SUBCONTRATOS -->
            <tr class="rubro-header">
                <td colspan="4">4. SUBCONTRATOS / OTROS</td>
                <td class="text-end">$ ' . number_format($subtotales['SUBCONTRATO'], 2, ',', '.') . '</td>
            </tr>
            ' . renderFilasRubro($detalles['SUBCONTRATO']) . '
        </tbody>
    </table>

    <!-- TOTALES Y COEFICIENTES K -->
    <table class="totales-box">';

if ($k_visible === 1) {
    $html .= '
        <tr class="bg-k-row">
            <td colspan="4" class="text-end">COSTO UNITARIO DIRECTO (NETO APU):</td>
            <td style="width: 120px;" class="text-end">$ ' . number_format($costo_neto_total, 2, ',', '.') . '</td>
        </tr>
        <tr class="bg-k-row">
            <td colspan="5">
                <strong>Perfil Coeficiente K Aplicado:</strong> ' . htmlspecialchars($nombre_perfil) . '<br>
                <span style="color: #555;">
                    Factores: 
                    K Mat = ' . number_format($k_mat, 3, ',', '.') . ' ($ ' . number_format($total_mat_k, 2, ',', '.') . ') | 
                    K MO/Eq/Sub = ' . number_format($k_mo, 3, ',', '.') . ' ($ ' . number_format($total_mo_k, 2, ',', '.') . ')
                </span>
            </td>
        </tr>
        <tr class="bg-dark-row">
            <td colspan="4" class="text-end">TOTAL PRESUPUESTADO (APU × K):</td>
            <td style="width: 120px;" class="text-end">$ ' . number_format($total_general_k, 2, ',', '.') . '</td>
        </tr>';
} else {
    $html .= '
        <tr class="bg-dark-row">
            <td colspan="4" class="text-end">TOTAL PRESUPUESTADO :</td>
            <td style="width: 120px;" class="text-end">$ ' . number_format($total_general_k, 2, ',', '.') . '</td>
        </tr>';
}

$html .= '
    </table>

    <!-- OBSERVACIONES -->
    ' . (!empty($tarea['observaciones']) ? '
    <div style="margin-top: 15px;">
        <strong>Observaciones / Especificaciones Técnicas:</strong>
        <p style="margin: 4px 0; font-size: 10px; color: #444;">' . nl2br(htmlspecialchars($tarea['observaciones'])) . '</p>
    </div>
    ' : '') . '

</body>
</html>';

// 6. Generación del documento PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("APU_" . ($tarea['codigo'] ?: $tarea_id) . ".pdf", ["Attachment" => false]);