<?php
// =====================================================================
// Partes comunes de los PDF del presupuesto (pdf.php y apu_pdf.php):
// versión a imprimir, datos generales, logo, Dompdf y formatos.
// =====================================================================

// Versión a imprimir: la pedida (y que sea de este presupuesto) o la última.
// Corta con un mensaje si no existe.
function pdfCargarVersion($conn, $id, $version_id) {
    if ($id <= 0) {
        die("ID de presupuesto inválido.");
    }
    if ($version_id > 0) {
        $stmt_v = $conn->prepare("SELECT snapshot_json, version_numero FROM presupuesto_versiones
                                  WHERE presupuesto_id = ? AND id = ?");
        $stmt_v->bind_param("ii", $id, $version_id);
    } else {
        $stmt_v = $conn->prepare("SELECT snapshot_json, version_numero FROM presupuesto_versiones
                                  WHERE presupuesto_id = ? ORDER BY version_numero DESC LIMIT 1");
        $stmt_v->bind_param("i", $id);
    }
    $stmt_v->execute();
    $version = $stmt_v->get_result()->fetch_assoc();
    if (!$version) {
        die($version_id > 0 ? "La versión solicitada no pertenece a este presupuesto." : "El presupuesto no tiene versiones guardadas.");
    }
    return [json_decode($version['snapshot_json'], true) ?: [], intval($version['version_numero'])];
}

// Datos generales tal como quedaron en la versión. El nombre/CUIT/dirección del
// cliente y el nombre de la obra se leen actuales (el snapshot los guarda solo por ID).
function pdfDatosGenerales($conn, array $snap, $version_numero) {
    $p = [
        'codigo'                => $snap['codigo'] ?? '',
        'fecha'                 => $snap['fecha'] ?? '',
        'titulo'                => $snap['titulo'] ?? '',
        'total_neto'            => floatval($snap['total_neto'] ?? 0),
        'total_presupuestado'   => floatval($snap['total_presupuestado'] ?? 0),
        'coeficiente_k_general' => floatval($snap['coeficiente_k_general'] ?? 1),
        'version_numero'        => $version_numero,
        'cliente_nombre'        => '-',
        'cliente_cuit'          => '',
        'cliente_direccion'     => '',
        'obra_nombre'           => '-',
    ];

    $cliente_id = intval($snap['cliente_id'] ?? 0);
    if ($cliente_id > 0) {
        $stmt_c = $conn->prepare("SELECT nombre, cuit, direccion FROM clientes WHERE id = ?");
        $stmt_c->bind_param("i", $cliente_id);
        $stmt_c->execute();
        if ($c = $stmt_c->get_result()->fetch_assoc()) {
            $p['cliente_nombre'] = $c['nombre'] ?? '-';
            $p['cliente_cuit'] = $c['cuit'] ?? '';
            $p['cliente_direccion'] = $c['direccion'] ?? '';
        }
    }
    $obra_id = intval($snap['obra_id'] ?? 0);
    if ($obra_id > 0) {
        $stmt_o = $conn->prepare("SELECT nombre FROM obras WHERE id = ?");
        $stmt_o->bind_param("i", $obra_id);
        $stmt_o->execute();
        if ($o = $stmt_o->get_result()->fetch_assoc()) {
            $p['obra_nombre'] = $o['nombre'] ?? '-';
        }
    }
    return $p;
}

// Coeficiente que lleva los precios netos (con K Base) al precio final.
// - Versiones con K de 2 decimales (desde que el total se calcula como Costo neto x K):
//   se usa ese K tal cual.
// - Versiones anteriores (K guardado con 4 decimales y total calculado con el K sin
//   redondear): Total presupuestado / Total neto, para que el PDF siga dando lo enviado.
function pdfKFinal(array $p) {
    $k = $p['coeficiente_k_general'];
    if ($k > 0 && abs(round($k, 2) - $k) < 0.000001) {
        return round($k, 2);
    }
    return $p['total_neto'] > 0 ? $p['total_presupuestado'] / $p['total_neto'] : ($k ?: 1);
}

function pdfLogoBase64() {
    $ruta_logo = __DIR__ . '/../../../assets/img/logo-pdf.jpg';
    return file_exists($ruta_logo) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($ruta_logo)) : '';
}

function pdfCrearDompdf() {
    $dompdfPath = __DIR__ . '/../../../vendor/dompdf/autoload.inc.php';
    if (!file_exists($dompdfPath)) {
        die("Error: No se encontró Dompdf en " . $dompdfPath);
    }
    require_once $dompdfPath;
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', false);
    $options->set('isHtml5ParserEnabled', true);
    return new \Dompdf\Dompdf($options);
}

// Renderiza, numera las páginas y envía el PDF al navegador
function pdfEnviar($dompdf, $html, $nombre_archivo) {
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $canvas = $dompdf->getCanvas();
    $fuente = $dompdf->getFontMetrics()->getFont('Helvetica');
    $canvas->page_text($canvas->get_width() - 110, $canvas->get_height() - 28, 'Página {PAGE_NUM} de {PAGE_COUNT}', $fuente, 8, [0.4, 0.4, 0.4]);

    $dompdf->stream($nombre_archivo, ['Attachment' => false]);
}

function pdfNombreArchivo(array $p, $id, $sufijo = '') {
    return preg_replace('/[^A-Za-z0-9_-]/', '_', $p['codigo'] ?: ('presupuesto_' . $id)) . $sufijo . '_v' . $p['version_numero'] . '.pdf';
}

function pdfMoneda($n) {
    return '$ ' . number_format((float)$n, 2, ',', '.');
}
function pdfCantidad($n) {
    $n = (float)$n;
    return number_format($n, ($n == floor($n)) ? 0 : 2, ',', '.');
}
// Cantidades de insumos de APU: hasta 4 decimales, sin ceros de más a la derecha
function pdfCantidadInsumo($n) {
    $n = (float)$n;
    if ($n == floor($n)) return number_format($n, 0, ',', '.');
    return rtrim(rtrim(number_format($n, 4, ',', '.'), '0'), ',');
}
function pdfTexto($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
