<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    
    case 'listar':
        $pestana = $_GET['pestana'] ?? 'RECIBIDOS';
        $data = [];

        if ($pestana === 'RECIBIDOS') {
            $sql = "SELECT * FROM cheques WHERE estado = 'RECIBIDO'";
        } elseif ($pestana === 'EMITIDOS') {
            $sql = "SELECT * FROM cheques WHERE estado = 'EMITIDO'";
        } elseif ($pestana === 'ENDOSADO') {
            $sql = "SELECT * FROM cheques WHERE estado = 'ENDOSADO'";
        } else {
            $sql = "SELECT * FROM cheques WHERE estado IN ('COBRADO', 'PAGADO')";
        }

        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
        }

        $contadores = [
            'recibidos'   => 0,
            'emitidos'    => 0,
            'cedidos'     => 0,
            'finalizados' => 0
        ];

        $sqlCounts = "SELECT estado, COUNT(*) as cant FROM cheques GROUP BY estado";
        $resCounts = $conn->query($sqlCounts);
        if ($resCounts) {
            while ($c = $resCounts->fetch_assoc()) {
                if ($c['estado'] === 'RECIBIDO')  $contadores['recibidos']   += $c['cant'];
                if ($c['estado'] === 'EMITIDO')   $contadores['emitidos']    += $c['cant'];
                if ($c['estado'] === 'ENDOSADO')  $contadores['cedidos']     += $c['cant'];
                if (in_array($c['estado'], ['COBRADO', 'PAGADO'])) {
                    $contadores['finalizados'] += $c['cant'];
                }
            }
        }

        echo json_encode([
            'data' => $data,
            'contadores' => $contadores
        ]);
        break;

    case 'guardar':
        $id            = $_POST['id'] ?? '';
        $tipo          = $_POST['tipo'] ?? '';
        $estado        = $_POST['estado'] ?? '';
        $nro_cheque    = $_POST['nro_cheque'] ?? '';
        $fecha_emision = $_POST['fecha_emision'] ?? '';
        $fecha_pago    = $_POST['fecha_pago'] ?? '';
        $importe       = $_POST['importe'] ?? 0.00;
        $beneficiario  = $_POST['beneficiario'] ?? '';
        $observaciones = $_POST['observaciones'] ?? '';

        if ($estado === 'RECIBIDO') {
            $beneficiario = 'RECURSOS GLOBALES';
        }

        // Lógica para el archivo adjunto (Igual que en Caja)
        $nombre_archivo = null;
        
        // Si estamos editando, recuperamos primero el nombre del archivo actual por si no se cambia
        if (!empty($id)) {
            $qActual = $conn->query("SELECT archivo FROM cheques WHERE id = $id");
            if ($qActual && $rAct = $qActual->fetch_assoc()) {
                $nombre_archivo = $rAct['archivo'];
            }
        }

        // Procesamos el nuevo archivo si fue subido
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $dir_subida = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/cheques/';
            
            // Creamos la carpeta si no existe
            if (!file_exists($dir_subida)) {
                mkdir($dir_subida, 0777, true);
            }

            $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
            // Generamos un nombre único usando timestamp y caracteres aleatorios
            $nombre_archivo = 'chk_' . time() . '_' . uniqid() . '.' . $ext;
            $ruta_completa = $dir_subida . $nombre_archivo;

            move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_completa);
        }

        if (empty($id)) {
            // INSERTAR NUEVO
            $stmt = $conn->prepare("INSERT INTO cheques (tipo, estado, nro_cheque, fecha_emision, fecha_pago, importe, beneficiario, observaciones, archivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdsss", $tipo, $estado, $nro_cheque, $fecha_emision, $fecha_pago, $importe, $beneficiario, $observaciones, $nombre_archivo);
        } else {
            // ACTUALIZAR EXISTENTE
            $stmt = $conn->prepare("UPDATE cheques SET tipo = ?, estado = ?, nro_cheque = ?, fecha_emision = ?, fecha_pago = ?, importe = ?, beneficiario = ?, observaciones = ?, archivo = ? WHERE id = ?");
            $stmt->bind_param("sssssdsssi", $tipo, $estado, $nro_cheque, $fecha_emision, $fecha_pago, $importe, $beneficiario, $observaciones, $nombre_archivo, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
        break;

    case 'eliminar':
        $id = $_POST['id'] ?? '';
        
        if (!empty($id)) {
            // Opcional: Borrar el archivo físico del disco al eliminar de la BD
            $qActual = $conn->query("SELECT archivo FROM cheques WHERE id = $id");
            if ($qActual && $rAct = $qActual->fetch_assoc()) {
                if ($rAct['archivo']) {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/cheques/' . $rAct['archivo']);
                }
            }

            $stmt = $conn->prepare("DELETE FROM cheques WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
        }
        break;

    default:
        echo json_encode(['error' => 'Acción no permitida']);
        break;
}