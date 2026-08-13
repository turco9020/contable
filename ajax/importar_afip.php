<?php
// ajax/importar_afip.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/contable/config/database.php'; // Carga $conn y session_start()

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? NULL;

// Helper para convertir montos AFIP ("16546,85" -> 16546.85)
function parseMontoAFIP($val) {
    if (empty($val)) return 0.0;
    $val = str_replace('.', '', $val);
    $val = str_replace(',', '.', $val);
    return (float)$val;
}

if ($action === 'preview') {
    if (!isset($_FILES['archivo_afip']) || $_FILES['archivo_afip']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir el archivo CSV.']);
        exit;
    }

    $filePath = $_FILES['archivo_afip']['tmp_name'];
    $handle = fopen($filePath, 'r');
    
    if (!$handle) {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo abrir el archivo CSV.']);
        exit;
    }

    $header = fgetcsv($handle, 0, ';'); // Leer encabezado delimitado por ;
    
    $registros = [];
    $duplicadosCount = 0;
    $nuevosCount = 0;

    // Prepared statements en MySQLi
    $stmtProv = $conn->prepare("SELECT id FROM proveedores WHERE cuit = ? OR cuit = ? LIMIT 1");
    $stmtGasto = $conn->prepare("SELECT id FROM gastos WHERE numero_comprobante = ? LIMIT 1");

    while (($row = fgetcsv($handle, 0, ';')) !== FALSE) {
        if (count($row) < 10) continue;

        $fechaEmision      = trim($row[0], '" ');
        $tipoComprobanteId = (int)trim($row[1], '" ');
        $puntoVenta        = (int)trim($row[2], '" ');
        $numDesde          = (int)trim($row[3], '" ');
        
        // Formato unificado: 00022-00520527
        $numeroComprobanteFormatted = sprintf('%05d-%08d', $puntoVenta, $numDesde);

        $cuitEmisor  = trim($row[7], '" ');
        $razonSocial = trim($row[8], '" ');

        // Desglose de importes desde las columnas del CSV AFIP
        $neto         = parseMontoAFIP($row[24]); // Imp. Neto Gravado Total
        $retIIBB      = 0.00;                     // Retenciones si aplican
        $otrosTributos= parseMontoAFIP($row[27]); // Otros Tributos
        $totalIVA     = parseMontoAFIP($row[28]); // Total IVA
        $montoTotal   = parseMontoAFIP($row[29]); // Imp. Total

        // Buscar ID de proveedor por CUIT
        $cuitLimpio = str_replace('-', '', $cuitEmisor);
        $stmtProv->bind_param("ss", $cuitEmisor, $cuitLimpio);
        $stmtProv->execute();
        $resProv = $stmtProv->get_result();
        $provData = $resProv->fetch_assoc();
        $proveedorId = $provData ? $provData['id'] : NULL;

        // Verificar si el gasto ya existe por número de comprobante
        $stmtGasto->bind_param("s", $numeroComprobanteFormatted);
        $stmtGasto->execute();
        $resGasto = $stmtGasto->get_result();
        $existe = $resGasto->fetch_assoc();

        $esDuplicado = (bool)$existe;
        if ($esDuplicado) {
            $duplicadosCount++;
        } else {
            $nuevosCount++;
        }

        // Regla: Validación PENDIENTE si el Total es >= $800.000
        $requiereValidacion = ($montoTotal >= 800000);
        $estadoValidacion   = $requiereValidacion ? 'PENDIENTE' : 'APROBADO';

        $registros[] = [
            'fecha'               => $fechaEmision,
            'tipo_comprobante_id' => $tipoComprobanteId,
            'punto_venta'         => $puntoVenta,
            'numero_comprobante'  => $numeroComprobanteFormatted,
            'cuit_emisor'         => $cuitEmisor,
            'razon_social'        => $razonSocial,
            'proveedor_id'        => $proveedorId,
            'neto'                => $neto,
            'iva'                 => $totalIVA,
            'ret_iibb'            => $retIIBB,
            'otros_tributos'      => $otrosTributos,
            'monto_total'         => $montoTotal,
            'es_duplicado'        => $esDuplicado,
            'estado_validacion'   => $estadoValidacion
        ];
    }

    fclose($handle);
    $stmtProv->close();
    $stmtGasto->close();

    echo json_encode([
        'status' => 'success',
        'totales' => [
            'total'      => count($registros),
            'nuevos'     => $nuevosCount,
            'duplicados' => $duplicadosCount
        ],
        'data' => $registros
    ]);
    exit;
}

if ($action === 'confirm_import') {
    $items = json_decode($_POST['items'] ?? '[]', true);

    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'No hay comprobantes seleccionados para importar.']);
        exit;
    }

    $inserted = 0;
    $conn->begin_transaction();

    try {
        $stmtInsert = $conn->prepare("
            INSERT INTO gastos (
                fecha, tipo_comprobante_id, numero_comprobante, proveedor_id, 
                categoria_id, obra_id, caja_id, neto, iva, ret_iibb, 
                otros_tributos, total, detalle, usuario_id, estado_validacion
            ) VALUES (?, ?, ?, ?, NULL, NULL, NULL, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            if (!empty($item['es_duplicado'])) continue; // Omitir duplicados

            $detalle = "Importado AFIP - " . $item['razon_social'];
            
            // Preparar variables para bind_param
            $fecha           = $item['fecha'];
            $tipoCompId      = (int)$item['tipo_comprobante_id'];
            $numComp         = $item['numero_comprobante'];
            $provId          = $item['proveedor_id'] ? (int)$item['proveedor_id'] : NULL;
            $neto            = (float)$item['neto'];
            $iva             = (float)$item['iva'];
            $retIIBB         = (float)$item['ret_iibb'];
            $otrosTributos   = (float)$item['otros_tributos'];
            $total           = (float)$item['monto_total'];
            $estadoVal       = $item['estado_validacion'];

            $stmtInsert->bind_param(
                "sissdddddsis",
                $fecha,
                $tipoCompId,
                $numComp,
                $provId,
                $neto,
                $iva,
                $retIIBB,
                $otrosTributos,
                $total,
                $detalle,
                $usuario_id,
                $estadoVal
            );

            $stmtInsert->execute();
            $inserted++;
        }

        $conn->commit();
        $stmtInsert->close();

        echo json_encode(['status' => 'success', 'imported_count' => $inserted]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}