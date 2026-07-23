<?php
// Iniciamos la sesión para auditar usuarios y verificar roles jerárquicos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// Captura segura de datos de sesión
$usuario = $_SESSION['id'] ?? 0;
$rol = $_SESSION['rol'] ?? 'user';

switch ($accion) {
    
    case 'listar':
        header('Content-Type: application/json');
        $pestana = $_GET['pestana'] ?? 'RECIBIDOS';
        $data = [];

        // --- 1. FILTRADO DE LA TABLA PRINCIPAL ---
        // Definición de las condiciones base por estado
        if ($pestana === 'RECIBIDOS') {
            $condicionEstado = "estado = 'RECIBIDO'";
        } elseif ($pestana === 'EMITIDOS') {
            $condicionEstado = "estado = 'EMITIDO'";
        } elseif ($pestana === 'ENDOSADO') {
            $condicionEstado = "estado = 'ENDOSADO'";
        } else {
            $condicionEstado = "estado IN ('COBRADO', 'PAGADO')";
        }

        $sql = "SELECT * FROM cheques WHERE $condicionEstado";

        // Si NO es Admin ni Contador, limitamos la visualización a sus propios registros
        if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
            $sql .= " AND usuario_id = $usuario";
        }

        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
        }

        // --- 2. FILTRADO DE CONTADORES SUPERIORES ---
        $contadores = [
            'recibidos'   => 0,
            'emitidos'    => 0,
            'cedidos'     => 0,
            'finalizados' => 0
        ];

        $sqlCounts = "SELECT estado, COUNT(*) as cant FROM cheques";
        
        // Aplicamos la misma restricción a los contadores globales de las pestañas
        if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
            $sqlCounts .= " WHERE usuario_id = $usuario";
        }
        
        $sqlCounts .= " GROUP BY estado";

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
        header('Content-Type: application/json');
        
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

        // Lógica para el archivo adjunto
        $nombre_archivo = null;
        
        if (!empty($id)) {
            $qActual = $conn->query("SELECT archivo FROM cheques WHERE id = $id");
            if ($qActual && $rAct = $qActual->fetch_assoc()) {
                $nombre_archivo = $rAct['archivo'];
            }
        }

        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $dir_subida = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/cheques/';
            
            if (!file_exists($dir_subida)) {
                mkdir($dir_subida, 0777, true);
            }

            $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = 'chk_' . time() . '_' . uniqid() . '.' . $ext;
            $ruta_completa = $dir_subida . $nombre_archivo;

            move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_completa);
        }

        if (empty($id)) {
            // INSERTAR NUEVO (Agregamos la columna usuario_id a la consulta y al tipado "i")
            $stmt = $conn->prepare("INSERT INTO cheques (tipo, estado, nro_cheque, fecha_emision, fecha_pago, importe, beneficiario, observaciones, archivo, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdsssi", $tipo, $estado, $nro_cheque, $fecha_emision, $fecha_pago, $importe, $beneficiario, $observaciones, $nombre_archivo, $usuario);
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
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? '';
        
        if (!empty($id)) {
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
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acción no permitida']);
        break;
}