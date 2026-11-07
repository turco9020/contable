<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// Captura de variables de sesión del usuario logueado
$usuario_logueado = $_SESSION['id'] ?? 0;
$rol = $_SESSION['rol'] ?? 'user';

// Función auxiliar recursiva para limpiar y borrar directorios
function eliminarDirectorioCompleto($dir) {
    if (!file_exists($dir)) return;
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        $ruta = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($ruta) ? eliminarDirectorioCompleto($ruta) : unlink($ruta);
    }
    return rmdir($dir);
}

// ================= LISTAR OBRAS =================
if($accion == 'listar'){
    $sql = "SELECT o.*, c.nombre as cliente, u.usuario as usuario_nombre
            FROM obras o
            LEFT JOIN clientes c ON c.id = o.cliente_id
            LEFT JOIN usuarios u ON u.id = o.usuario_id";

    $res = $conn->query($sql);
    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(["data"=>$data]);
    exit;
}

// ================= GUARDAR / ACTUALIZAR =================
if($accion == 'guardar'){
    $id = $_POST['id'] ?? '';

    $nombre = $conn->real_escape_string($_POST['nombre']);
    $cliente_id = $_POST['cliente_id'] ? intval($_POST['cliente_id']) : 'NULL';
    $responsable = $conn->real_escape_string($_POST['responsable']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $nro_oc = $conn->real_escape_string($_POST['nro_oc']);
    $fecha_inicio = $conn->real_escape_string($_POST['fecha_inicio']);
    $fecha_fin = $_POST['fecha_fin'] ? "'".$conn->real_escape_string($_POST['fecha_fin'])."'" : 'NULL';
    $tipo_obra = $conn->real_escape_string($_POST['tipo_obra']);
    $detalle = $conn->real_escape_string($_POST['detalle']);
    $estado = $conn->real_escape_string($_POST['estado']);
    $facturacion = $conn->real_escape_string($_POST['facturacion']);

    $usuario_id_db = ($usuario_logueado > 0) ? intval($usuario_logueado) : 'NULL';

    if($id){
        $sql = "UPDATE obras SET
            nombre='$nombre',
            cliente_id=$cliente_id,
            responsable='$responsable',
            direccion='$direccion',
            nro_oc='$nro_oc',
            fecha_inicio='$fecha_inicio',
            fecha_fin=$fecha_fin,
            tipo_obra='$tipo_obra',
            detalle='$detalle',
            estado='$estado',
            facturacion='$facturacion'
        WHERE id=$id";
        
        $conn->query($sql);
        $obra_id = $id;
    } else {
        $sql = "INSERT INTO obras (nombre, cliente_id, responsable, direccion, nro_oc, fecha_inicio, fecha_fin, tipo_obra, detalle, estado, facturacion, usuario_id)
        VALUES ('$nombre', $cliente_id, '$responsable', '$direccion', '$nro_oc', '$fecha_inicio', $fecha_fin, '$tipo_obra', '$detalle', '$estado', '$facturacion', $usuario_id_db)";
        
        $conn->query($sql);
        $obra_id = $conn->insert_id;
    }

    // Carpetas físicas dinámicas
    $carpeta_obra = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/obras/' . $obra_id . '/';
    $carpeta_presupuestos = $carpeta_obra . 'presupuestos/';
    $carpeta_repositorio = $carpeta_obra . 'repositorio/';
    
    if(!is_dir($carpeta_obra)) mkdir($carpeta_obra, 0777, true);
    if(!is_dir($carpeta_presupuestos)) mkdir($carpeta_presupuestos, 0777, true);
    if(!is_dir($carpeta_repositorio)) mkdir($carpeta_repositorio, 0777, true);

    // --- PROCESAMIENTO DEL PRESUPUESTO ---
    if(isset($_FILES['presupuesto_archivo']) && $_FILES['presupuesto_archivo']['error'] == UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['presupuesto_archivo']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = 'presupuesto_' . uniqid() . '.' . $ext;
        $ruta_destino = $carpeta_presupuestos . $nombre_archivo;
        
        if(move_uploaded_file($_FILES['presupuesto_archivo']['tmp_name'], $ruta_destino)) {
            $ruta_db_presupuesto = $obra_id . '/presupuestos/' . $nombre_archivo;
            $conn->query("UPDATE obras SET presupuesto_archivo = '$ruta_db_presupuesto' WHERE id = $obra_id");
        }
    }

    // --- PROCESAMIENTO DEL REPOSITORIO ---
    if(isset($_FILES['archivos_repositorio']['name']) && is_array($_FILES['archivos_repositorio']['name'])) {
        $total_archivos = count($_FILES['archivos_repositorio']['name']);
        
        for($i = 0; $i < $total_archivos; $i++) {
            if($_FILES['archivos_repositorio']['error'][$i] == UPLOAD_ERR_OK) {
                $nombre_original = $conn->real_escape_string($_FILES['archivos_repositorio']['name'][$i]);
                $ext = pathinfo($_FILES['archivos_repositorio']['name'][$i], PATHINFO_EXTENSION);
                
                $nombre_sistema = 'doc_' . uniqid() . '_' . $i . '.' . $ext;
                $ruta_repo = $carpeta_repositorio . $nombre_sistema;
                
                if(move_uploaded_file($_FILES['archivos_repositorio']['tmp_name'][$i], $ruta_repo)) {
                    $ruta_relativa = $obra_id . '/repositorio/' . $nombre_sistema;
                    $conn->query("INSERT INTO obra_archivos (obra_id, archivo, nombre_original) 
                                  VALUES ($obra_id, '$ruta_relativa', '$nombre_original')");
                }
            }
        }
    }

    echo "OK";
    exit;
}

// ================= TRAER ADJUNTOS DEL REPOSITORIO =================
if($accion == 'listar_archivos') {
    $obra_id = intval($_GET['obra_id'] ?? 0);
    $archivos = [];

    if($obra_id > 0) {
        $res = $conn->query("SELECT id, archivo, nombre_original FROM obra_archivos WHERE obra_id = $obra_id ORDER BY id DESC");
        while($row = $res->fetch_assoc()) {
            $archivos[] = $row;
        }
    }
    echo json_encode(["success" => true, "archivos" => $archivos]);
    exit;
}

// ================= TRAER FACTURAS VENTA ASOCIADAS A LA OBRA =================
if($accion == 'listar_facturas') {
    $obra_id = intval($_GET['obra_id'] ?? 0);
    $facturas = [];

    if($obra_id > 0) {
        $res = $conn->query("SELECT id, nro_factura, total, fecha, archivo FROM facturas_venta WHERE obra_id = $obra_id ORDER BY fecha DESC");
        while($row = $res->fetch_assoc()) {
            $facturas[] = $row;
        }
    }
    echo json_encode(["success" => true, "facturas" => $facturas]);
    exit;
}

// ================= PRESUPUESTOS VINCULADOS A LA OBRA =================
// Solo datos de la tabla presupuestos y el número de la última versión
// (no se leen los snapshots).
if($accion == 'listar_presupuestos') {
    $obra_id = intval($_GET['obra_id'] ?? 0);
    $presupuestos = [];

    if($obra_id > 0) {
        $res = $conn->query("SELECT p.id, p.codigo, p.titulo, p.fecha, p.estado, p.total_presupuestado,
                                    (SELECT MAX(v.version_numero) FROM presupuesto_versiones v WHERE v.presupuesto_id = p.id) AS ultima_version
                             FROM presupuestos p
                             WHERE p.obra_id = $obra_id
                             ORDER BY p.fecha DESC, p.id DESC");
        if($res) {
            while($row = $res->fetch_assoc()) {
                $presupuestos[] = $row;
            }
        }
    }
    echo json_encode(["success" => true, "presupuestos" => $presupuestos]);
    exit;
}

// ================= NUEVO: TRAER GASTOS ASOCIADOS A LA OBRA =================
if($accion == 'listar_gastos') {
    $obra_id = intval($_GET['obra_id'] ?? 0);
    $gastos = [];

    if($obra_id > 0) {
        $sql = "SELECT g.id, g.fecha, g.numero_comprobante, g.detalle, g.total, g.archivo, 
                       p.nombre AS proveedor, cat.nombre AS categoria
                FROM gastos g
                LEFT JOIN proveedores p ON p.id = g.proveedor_id
                LEFT JOIN categorias cat ON cat.id = g.categoria_id
                WHERE g.obra_id = $obra_id
                ORDER BY g.fecha DESC";
                
        $res = $conn->query($sql);
        while($row = $res->fetch_assoc()) {
            $gastos[] = $row;
        }
    }
    echo json_encode(["success" => true, "gastos" => $gastos]);
    exit;
}

// ================= ELIMINAR UN ARCHIVO INDIVIDUAL =================
if($accion == 'eliminar_archivo') {
    $id = intval($_POST['id'] ?? 0);
    
    $res = $conn->query("SELECT archivo FROM obra_archivos WHERE id = $id");
    if($row = $res->fetch_assoc()) {
        $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/obras/' . $row['archivo'];
        if(file_exists($ruta_fisica)) {
            unlink($ruta_fisica);
        }
        $conn->query("DELETE FROM obra_archivos WHERE id = $id");
    }
    echo json_encode(["success" => true]);
    exit;
}

// ================= PÓLIZAS DE CAUCIÓN DE LA OBRA =================
$acciones_polizas = ['listar_polizas', 'guardar_poliza', 'eliminar_poliza'];
if(in_array($accion, $acciones_polizas)) {
    header('Content-Type: application/json');
    if($usuario_logueado <= 0) {
        echo json_encode(["success" => false, "msg" => "Sesión expirada"]);
        exit;
    }
}

if($accion == 'listar_polizas') {
    $obra_id = intval($_GET['obra_id'] ?? 0);
    $polizas = [];

    if($obra_id > 0) {
        $stmt = $conn->prepare("SELECT id, tipo, aseguradora, nro_poliza, vigencia_desde, vigencia_hasta, monto, liberada, archivo, nombre_original, observaciones
                                FROM obra_polizas WHERE obra_id = ?
                                ORDER BY liberada ASC, vigencia_hasta IS NULL, vigencia_hasta ASC, id DESC");
        $stmt->bind_param('i', $obra_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while($row = $res->fetch_assoc()) {
            $polizas[] = $row;
        }
    }
    echo json_encode(["success" => true, "polizas" => $polizas]);
    exit;
}

if($accion == 'guardar_poliza') {
    $id = intval($_POST['poliza_id'] ?? 0);
    $obra_id = intval($_POST['poliza_obra_id'] ?? 0);
    $tipos_validos = ['OFERTA','CONTRATO','AMPLIACION','FONDO_REPARO','ANTICIPO','OTRA'];
    $tipo = $_POST['tipo'] ?? '';
    $aseguradora = trim($_POST['aseguradora'] ?? '');
    $nro_poliza = trim($_POST['nro_poliza'] ?? '');
    $vigencia_desde = !empty($_POST['vigencia_desde']) ? $_POST['vigencia_desde'] : null;
    $vigencia_hasta = !empty($_POST['vigencia_hasta']) ? $_POST['vigencia_hasta'] : null;
    $monto = (float)($_POST['monto'] ?? 0);
    $liberada = isset($_POST['liberada']) ? 1 : 0;
    $observaciones = trim($_POST['observaciones'] ?? '');

    if($obra_id <= 0 || !in_array($tipo, $tipos_validos)) {
        echo json_encode(["success" => false, "msg" => "Faltan datos de la póliza."]);
        exit;
    }

    // Adjunto (opcional): se guarda en la carpeta de la obra
    $archivo_db = null;
    $nombre_original = null;
    if(isset($_FILES['poliza_archivo']) && $_FILES['poliza_archivo']['error'] == UPLOAD_ERR_OK) {
        $carpeta_polizas = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/obras/' . $obra_id . '/polizas/';
        if(!is_dir($carpeta_polizas)) mkdir($carpeta_polizas, 0777, true);

        $ext = pathinfo($_FILES['poliza_archivo']['name'], PATHINFO_EXTENSION);
        $nombre_sistema = 'poliza_' . uniqid() . '.' . $ext;
        if(move_uploaded_file($_FILES['poliza_archivo']['tmp_name'], $carpeta_polizas . $nombre_sistema)) {
            $archivo_db = $obra_id . '/polizas/' . $nombre_sistema;
            $nombre_original = $_FILES['poliza_archivo']['name'];
        }
    }

    if($id) {
        // Si se sube un archivo nuevo, se borra el anterior
        if($archivo_db) {
            $stmt = $conn->prepare("SELECT archivo FROM obra_polizas WHERE id = ? AND obra_id = ?");
            $stmt->bind_param('ii', $id, $obra_id);
            $stmt->execute();
            $ant = $stmt->get_result()->fetch_assoc();
            if($ant && !empty($ant['archivo'])) {
                $ruta_ant = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/obras/' . $ant['archivo'];
                if(file_exists($ruta_ant)) unlink($ruta_ant);
            }
        }

        $stmt = $conn->prepare("UPDATE obra_polizas SET tipo=?, aseguradora=?, nro_poliza=?, vigencia_desde=?, vigencia_hasta=?, monto=?, liberada=?, observaciones=?,
                                    archivo=COALESCE(?, archivo), nombre_original=COALESCE(?, nombre_original)
                                WHERE id=? AND obra_id=?");
        $stmt->bind_param('sssssdisssii', $tipo, $aseguradora, $nro_poliza, $vigencia_desde, $vigencia_hasta, $monto, $liberada, $observaciones, $archivo_db, $nombre_original, $id, $obra_id);
    } else {
        $usuario_id_db = $usuario_logueado > 0 ? intval($usuario_logueado) : null;
        $stmt = $conn->prepare("INSERT INTO obra_polizas (obra_id, tipo, aseguradora, nro_poliza, vigencia_desde, vigencia_hasta, monto, liberada, archivo, nombre_original, observaciones, usuario_id)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssssdisssi', $obra_id, $tipo, $aseguradora, $nro_poliza, $vigencia_desde, $vigencia_hasta, $monto, $liberada, $archivo_db, $nombre_original, $observaciones, $usuario_id_db);
    }

    if($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "msg" => $conn->error]);
    }
    exit;
}

if($accion == 'eliminar_poliza') {
    $id = intval($_POST['id'] ?? 0);

    $stmt = $conn->prepare("SELECT archivo FROM obra_polizas WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if($row = $stmt->get_result()->fetch_assoc()) {
        if(!empty($row['archivo'])) {
            $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/obras/' . $row['archivo'];
            if(file_exists($ruta_fisica)) unlink($ruta_fisica);
        }
        $stmt = $conn->prepare("DELETE FROM obra_polizas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    echo json_encode(["success" => true]);
    exit;
}

// ================= ELIMINAR OBRA COMPLETA =================
if($accion == 'eliminar'){
    $id = intval($_POST['id']);

    $carpeta_obra = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/obras/' . $id . '/';
    if(is_dir($carpeta_obra)) {
        eliminarDirectorioCompleto($carpeta_obra);
    }

    $conn->query("DELETE FROM obras WHERE id=$id");

    echo "OK";
    exit;
}
?>