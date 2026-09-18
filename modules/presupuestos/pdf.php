<?php
// Desactivar salida de errores en pantalla para no corruptor la descarga del PDF
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/database.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID de presupuesto inválido.");
}

// Cargar Presupuesto usando MySQLi ($conn)
$stmt = $conn->prepare("
    SELECT p.*, 
           IFNULL(c.nombre, '-') AS cliente_nombre, 
           IFNULL(c.cuit, '') AS cliente_cuit, 
           IFNULL(c.direccion, '') AS cliente_direccion, 
           IFNULL(o.nombre, 'N/A') AS obra_nombre
    FROM presupuestos p
    LEFT JOIN clientes c ON p.cliente_id = c.id
    LEFT JOIN obras o ON p.obra_id = o.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) {
    die("Presupuesto no encontrado.");
}

// Cargar detalles del presupuesto
$stmtDet = $conn->prepare("SELECT * FROM presupuesto_detalles WHERE presupuesto_id = ? ORDER BY item_orden ASC");
$stmtDet->bind_param("i", $id);
$stmtDet->execute();
$items = $stmtDet->get_result()->fetch_all(MYSQLI_ASSOC);

// Cargar Dompdf desde la descarga manual
$dompdfPath = __DIR__ . '/../../vendor/dompdf/autoload.inc.php';

if (!file_exists($dompdfPath)) {
    // Probar sin extensión .php por si el archivo se llama solo 'autoload.inc'
    $dompdfPath = __DIR__ . '/../../vendor/dompdf/autoload.inc';
}

if (!file_exists($dompdfPath)) {
    die("Error: No se encontró el archivo autoload en " . $dompdfPath);
}

require_once $dompdfPath;

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);

// Convertir hoja membretada a base64
$pathMembrete = '../../assets/img/hoja_membretada.png';
$bgBase64 = '';
if (file_exists($pathMembrete)) {
    $type = pathinfo($pathMembrete, PATHINFO_EXTENSION);
    $data = file_get_contents($pathMembrete);
    $bgBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto <?= htmlspecialchars($p['numero']) ?></title>
    <style>
        @page { margin: 0px; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0px;
            padding: 0px;
            font-size: 12px;
            color: #333;
        }
        .bg-membrete {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: -1000;
        }
        .container {
            padding-top: 140px;
            padding-left: 50px;
            padding-right: 50px;
            padding-bottom: 80px;
        }
        .header-box {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #111;
        }
        .table-info {
            width: 100%;
            margin-bottom: 20px;
        }
        .table-info td {
            padding: 4px 0;
            vertical-align: top;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-items th {
            background-color: #2c2f33;
            color: #ffffff;
            padding: 8px;
            font-size: 11px;
            text-align: left;
        }
        .table-items td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
        }
        .totales-box {
            width: 40%;
            float: right;
            margin-top: 10px;
        }
        .totales-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .totales-box td {
            padding: 6px;
            text-align: right;
        }
        .total-row {
            font-size: 14px;
            font-weight: bold;
            background-color: #f4f6f9;
            border-top: 2px solid #333;
        }
        .condiciones {
            clear: both;
            padding-top: 20px;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

<?php if ($bgBase64): ?>
    <img src="<?= $bgBase64 ?>" class="bg-membrete">
<?php endif; ?>

<div class="container">
    <div class="header-box">
        <table width="100%">
            <tr>
                <td>
                    <div class="title">PRESUPUESTO <?= htmlspecialchars($p['numero']) ?></div>
                    <small>FECHA: <?= date('d/m/Y', strtotime($p['fecha'])) ?></small>
                </td>
            </tr>
        </table>
    </div>

    <table class="table-info">
        <tr>
            <td width="15%"><strong>CLIENTE:</strong></td>
            <td width="45%"><?= htmlspecialchars($p['cliente_nombre']) ?></td>
            <td width="15%"><strong>OBRA:</strong></td>
            <td width="25%"><?= htmlspecialchars($p['obra_nombre']) ?></td>
        </tr>
        <tr>
            <td><strong>PROYECTO:</strong></td>
            <td colspan="3"><?= htmlspecialchars($p['titulo']) ?></td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr>
                <th width="8%">ÍTEM</th>
                <th width="52%">DESCRIPCIÓN</th>
                <th width="10%" style="text-align: center;">UNIDAD</th>
                <th width="10%" style="text-align: right;">CANT.</th>
                <th width="20%" style="text-align: right;">PRECIO TOTAL ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            foreach ($items as $it): 
                $precioUnitarioCliente = $it['costo_unitario'] * $p['coeficiente'];
                $subtotalCliente = $it['cantidad'] * $precioUnitarioCliente;
            ?>
            <tr>
                <td style="text-align: center;"><?= $i++ ?></td>
                <td><?= nl2br(htmlspecialchars($it['descripcion'])) ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($it['unidad']) ?></td>
                <td style="text-align: right;"><?= number_format($it['cantidad'], 2, ',', '.') ?></td>
                <td style="text-align: right;">$ <?= number_format($subtotalCliente, 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totales-box">
        <table>
            <tr class="total-row">
                <td>TOTAL GENERAL:</td>
                <td>$ <?= number_format($p['monto_total'], 2, ',', '.') ?></td>
            </tr>
        </table>
    </div>

    <?php if (!empty($p['condiciones'])): ?>
    <div class="condiciones">
        <strong>CONDICIONES COMERCIALES / NOTAS:</strong><br>
        <?= nl2br(htmlspecialchars($p['condiciones'])) ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// --- 1. GUARDAR EN EL SERVIDOR (uploads/presupuestos) ---
$dirUploads = __DIR__ . '/../../uploads/presupuestos/';
if (!file_exists($dirUploads)) {
    mkdir($dirUploads, 0777, true);
}

$nombreArchivo = $p['numero'] . '.pdf';
$rutaCompleta = $dirUploads . $nombreArchivo;

// Guardar el PDF generado en el disco
file_put_contents($rutaCompleta, $dompdf->output());

// --- 2. MOSTRAR EN EL NAVEGADOR ---
$dompdf->stream($nombreArchivo, ["Attachment" => false]);
exit();