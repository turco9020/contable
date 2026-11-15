<?php
// Iniciamos la sesión para poder auditar el usuario y validar roles
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// Captura de datos de sesión actuales
$usuario = $_SESSION['id'] ?? 0;
$rol = $_SESSION['rol'] ?? 'user';

$es_admin_o_contador = strcasecmp($rol, 'admin') === 0 || strcasecmp($rol, 'contador') === 0;

// =====================================================================
// COBROS Y SALDO DE LAS FACTURAS
// Saldo = Total - Cobros - Retenciones. Con cobros o retenciones cargados,
// el estado se calcula solo: DEBE / PARCIAL / PAGADO. Sin ninguno, se
// respeta el estado elegido a mano (DEBE, PAGADO o VER).
// =====================================================================
const FAC_TOLERANCIA = 0.01;

function facturaImportes($conn, $factura_id) {
    $factura_id = (int)$factura_id;
    $res = $conn->query("SELECT f.total,
                                (SELECT COALESCE(SUM(importe), 0) FROM cobros_venta WHERE factura_id = f.id) AS cobrado,
                                (SELECT COALESCE(SUM(importe), 0) FROM retenciones_venta WHERE factura_id = f.id) AS retenido
                         FROM facturas_venta f WHERE f.id = $factura_id");
    $r = $res ? $res->fetch_assoc() : null;
    if (!$r) return null;
    $total = (float)$r['total'];
    $cobrado = (float)$r['cobrado'];
    $retenido = (float)$r['retenido'];
    return ['total' => $total, 'cobrado' => $cobrado, 'retenido' => $retenido, 'saldo' => round($total - $cobrado - $retenido, 2)];
}

// $tras_borrar = true cuando se acaba de borrar un cobro o una retención: si ya no
// queda ninguno, la factura vuelve a DEBE (antes estaba pagada por esos importes)
function facturaRecalcularEstado($conn, $factura_id, $tras_borrar = false) {
    $imp = facturaImportes($conn, $factura_id);
    if (!$imp) return;
    if ($imp['cobrado'] <= 0 && $imp['retenido'] <= 0) {
        if ($tras_borrar) {
            $conn->query("UPDATE facturas_venta SET estado = 'DEBE' WHERE id = " . (int)$factura_id . " AND estado IN ('PARCIAL', 'PAGADO')");
        }
        return; // sin cobros: queda el estado manual
    }

    if ($imp['saldo'] <= FAC_TOLERANCIA) $estado = 'PAGADO';
    else $estado = 'PARCIAL';
    $conn->query("UPDATE facturas_venta SET estado = '$estado' WHERE id = " . (int)$factura_id);
}

// La factura existe y el usuario puede operarla (admin / contador: todas; el resto: las propias)
function facturaAccesible($conn, $factura_id, $usuario, $es_admin_o_contador) {
    $factura_id = (int)$factura_id;
    $res = $conn->query("SELECT f.id, f.punto_venta, f.nro_factura, f.usuario_id, IFNULL(c.nombre, '') AS cliente
                         FROM facturas_venta f LEFT JOIN clientes c ON c.id = f.cliente_id WHERE f.id = $factura_id");
    $f = $res ? $res->fetch_assoc() : null;
    if (!$f) return null;
    if (!$es_admin_o_contador && (int)$f['usuario_id'] !== (int)$usuario) return null;
    return $f;
}

// =======================
// LISTAR FACTURAS
// =======================
if($accion == 'listar'){
    header('Content-Type: application/json');

    // Construcción de la condición base según rol
    $where = "WHERE 1=1";
    if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
        $where .= " AND f.usuario_id = $usuario";
    }

    // Consulta limpia adaptada con f.obra_id incorporado
    $sql = "
        SELECT 
            f.id,
            f.fecha, 
            f.punto_venta,
            f.nro_factura,
            f.fecha_vencimiento,
            f.detalle,
            f.neto,
            f.iva,
            f.total,
            f.observaciones,
            f.archivo,
            f.cliente_id,
            f.tipo_comprobante_id,
            f.centro_costo_id,
            f.obra_id,
            f.iibb_actividad_id,
            f.iibb_alicuota,
            ia.codigo AS iibb_codigo,
            ia.actividad AS iibb_actividad,
            ia.tratamiento AS iibb_tratamiento,
            f.estado,
            (SELECT COALESCE(SUM(cv.importe), 0) FROM cobros_venta cv WHERE cv.factura_id = f.id) AS cobrado,
            (SELECT COALESCE(SUM(rv.importe), 0) FROM retenciones_venta rv WHERE rv.factura_id = f.id) AS retenido,
            f.usuario_id,
            IFNULL(u.usuario, 'Sistema') AS usuario_nombre,
            IFNULL(c.nombre, 'SIN CLIENTE') AS cliente_nombre,
            IFNULL(tc.nombre, 'SIN TIPO') AS tipo_comprobante_nombre,
            IFNULL(cc.nombre, 'SIN CENTRO') AS centro_costo_nombre
        FROM facturas_venta f
        LEFT JOIN clientes c ON c.id = f.cliente_id
        LEFT JOIN tipos_comprobante tc ON tc.id = f.tipo_comprobante_id
        LEFT JOIN centros_costos cc ON cc.id = f.centro_costo_id
        LEFT JOIN usuarios u ON u.id = f.usuario_id
        LEFT JOIN config_iibb_actividades ia ON ia.id = f.iibb_actividad_id
        $where
        ORDER BY f.fecha DESC, f.id DESC
    ";
    
    $res = $conn->query($sql);
    
    if(!$res){
        echo json_encode(["error" => "Error SQL: " . $conn->error]);
        exit;
    }
    
    $data = [];
    while($row = $res->fetch_assoc()){
        // Saldo pendiente de la factura
        $row['saldo'] = round((float)$row['total'] - (float)$row['cobrado'] - (float)$row['retenido'], 2);
        $data[] = $row;
    }

    echo json_encode(['data' => $data]);
    exit;
}

// =======================
// GUARDAR / EDITAR FACTURA
// =======================
if($accion == 'guardar'){
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? '';
    $fecha = $_POST['fecha'];
    $tipo_comprobante_id = (int)$_POST['tipo_comprobante_id'];
    $cliente_id = (int)$_POST['cliente_id'];
    $punto_venta = (int)$_POST['punto_venta'];
    $nro_factura = (int)$_POST['nro_factura'];
    $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? "'".$_POST['fecha_vencimiento']."'" : "NULL";
    $detalle = $conn->real_escape_string(trim($_POST['detalle']));
    $neto = (float)$_POST['neto'];
    $iva = (float)$_POST['iva'];
    $total = (float)$_POST['total'];
    $observaciones = $conn->real_escape_string(trim($_POST['observaciones']));
    $centro_costo_id = (int)$_POST['centro_costo_id'];
    $estado = $conn->real_escape_string(trim($_POST['estado'] ?? 'DEBE'));
    
    // CAPTURA Y PROCESAMIENTO DEL CAMPO NUEVO (OBRA)
    $obra_id = !empty($_POST['obra_id']) ? (int)$_POST['obra_id'] : "NULL";

    // ALÍCUOTA IIBB: se guarda una copia del porcentaje al momento de facturar,
    // así los reportes no cambian si después se actualiza la alícuota en Configuración
    $iibb_actividad_id = !empty($_POST['iibb_actividad_id']) ? (int)$_POST['iibb_actividad_id'] : 0;
    $iibb_alicuota = "NULL";
    if($iibb_actividad_id){
        // Si se edita sin cambiar de actividad, se conserva la alícuota original
        if($id){
            $res_ant = $conn->query("SELECT iibb_actividad_id, iibb_alicuota FROM facturas_venta WHERE id=" . (int)$id);
            $ant = $res_ant ? $res_ant->fetch_assoc() : null;
            if($ant && (int)$ant['iibb_actividad_id'] === $iibb_actividad_id && $ant['iibb_alicuota'] !== null){
                $iibb_alicuota = (float)$ant['iibb_alicuota'];
            }
        }
        if($iibb_alicuota === "NULL"){
            $res_ali = $conn->query("SELECT alicuota FROM config_iibb_actividades WHERE id=$iibb_actividad_id");
            $ali = $res_ali ? $res_ali->fetch_assoc() : null;
            if($ali && $ali['alicuota'] !== null){
                $iibb_alicuota = (float)$ali['alicuota'];
            }
        }
    }
    $iibb_actividad_sql = $iibb_actividad_id ? $iibb_actividad_id : "NULL";

    // Procesar archivo
    $archivo_nombre = null;
    if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0){
        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $archivo_nombre = 'FAC_VTA_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $ruta_destino = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/facturacion/';
        
        if (!file_exists($ruta_destino)) {
            mkdir($ruta_destino, 0777, true);
        }
        
        move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino . $archivo_nombre);
    }

    if($id){
        // EDITAR
        $sql = "UPDATE facturas_venta SET 
                    fecha='$fecha', 
                    tipo_comprobante_id=$tipo_comprobante_id, 
                    cliente_id=$cliente_id, 
                    punto_venta=$punto_venta, 
                    nro_factura=$nro_factura, 
                    fecha_vencimiento=$fecha_vencimiento,
                    detalle='$detalle',
                    neto=$neto, 
                    iva=$iva, 
                    total=$total,
                    iibb_actividad_id=$iibb_actividad_sql,
                    iibb_alicuota=$iibb_alicuota,
                    observaciones='$observaciones',
                    centro_costo_id=$centro_costo_id,
                    obra_id=$obra_id,
                    estado='$estado'";

        if($archivo_nombre){
            $sql .= ", archivo='$archivo_nombre'";
        }
        
        $sql .= " WHERE id=$id";
        
        // 🛡️ SEGURIDAD EDITAR: Si no es admin ni contador, sólo puede editar sus propias facturas
        if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
            $sql .= " AND usuario_id = $usuario";
        }
        
    } else {
        // NUEVO 
        $sql = "INSERT INTO facturas_venta (fecha, tipo_comprobante_id, cliente_id, punto_venta, nro_factura, fecha_vencimiento, detalle, neto, iva, total, iibb_actividad_id, iibb_alicuota, observaciones, centro_costo_id, obra_id, archivo, estado, usuario_id)
                VALUES ('$fecha', $tipo_comprobante_id, $cliente_id, $punto_venta, $nro_factura, $fecha_vencimiento, '$detalle', $neto, $iva, $total, $iibb_actividad_sql, $iibb_alicuota, '$observaciones', $centro_costo_id, $obra_id, " . ($archivo_nombre ? "'$archivo_nombre'" : "NULL") . ", '$estado', $usuario)";
    }
    
    if($conn->query($sql)){
        // Con cobros o retenciones, el estado sale del saldo (por si cambió el total)
        facturaRecalcularEstado($conn, $id ? (int)$id : (int)$conn->insert_id);
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => $conn->error]);
    }
    exit;
}

// =======================
// ELIMINAR FACTURA
// =======================
if($accion == 'eliminar'){
    header('Content-Type: application/json');
    $id = (int)$_POST['id'];
    
    // 🛡️ SEGURIDAD ELIMINAR: Modificamos las consultas para validar el dueño si no es admin/contador
    $restriccion_usuario = "";
    if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
        $restriccion_usuario = " AND usuario_id = $usuario";
    }

    // Buscamos el archivo asegurándonos de que tenga permisos sobre el registro
    $res = $conn->query("SELECT archivo FROM facturas_venta WHERE id=$id $restriccion_usuario");
    if($row = $res->fetch_assoc()){
        if(!empty($row['archivo'])){
            $path = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/facturacion/' . $row['archivo'];
            if(file_exists($path)) @unlink($path);
        }
        
        // Cobros de la factura: se borran sus ingresos de Caja y sus adjuntos
        // (las filas de cobros_venta se van en cascada con la factura)
        $res_cob = $conn->query("SELECT movimiento_caja_id, archivo FROM cobros_venta WHERE factura_id = $id");
        if ($res_cob) {
            while ($cob = $res_cob->fetch_assoc()) {
                if (!empty($cob['movimiento_caja_id'])) {
                    $conn->query("DELETE FROM movimientos_caja WHERE id = " . (int)$cob['movimiento_caja_id'] . " AND origen = 'COBRO'");
                }
                if (!empty($cob['archivo'])) {
                    $p = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/cobros/' . basename($cob['archivo']);
                    if (file_exists($p)) @unlink($p);
                }
            }
        }

        // Procedemos al borrado físico únicamente si pasó la validación de propiedad
        $conn->query("DELETE FROM facturas_venta WHERE id=$id $restriccion_usuario");
        echo json_encode(['status' => 'OK']);
    } else {
        echo json_encode(['status' => 'ERROR', 'msg' => 'No autorizado o registro no encontrado.']);
    }
    exit;
}

// ==========================================
// NUEVO: GUARDAR RETENCIÓN
// ==========================================
if($accion == 'guardar_retencion'){
    header('Content-Type: application/json');
    
    $factura_id = (int)($_POST['ret_factura_id'] ?? 0);
    $tipo_retencion_id = (int)($_POST['tipo_retencion_id'] ?? 0);
    $nro_certificado = $conn->real_escape_string(trim($_POST['nro_certificado'] ?? ''));
    $importe = (float)($_POST['importe_retencion'] ?? 0);
    $fecha_retencion = $_POST['fecha_retencion'] ?? '';

    if ($factura_id === 0 || $tipo_retencion_id === 0 || empty($fecha_retencion)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    // Procesar archivo adjunto del certificado
    $archivo_nombre = null;
    if (isset($_FILES['ret_archivo']) && $_FILES['ret_archivo']['error'] == 0) {
        $ext = pathinfo($_FILES['ret_archivo']['name'], PATHINFO_EXTENSION);
        $archivo_nombre = 'RET_' . $factura_id . '_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $ruta_destino = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/retenciones/';
        
        if (!file_exists($ruta_destino)) {
            mkdir($ruta_destino, 0777, true);
        }
        
        move_uploaded_file($_FILES['ret_archivo']['tmp_name'], $ruta_destino . $archivo_nombre);
    }

    $sql = "INSERT INTO retenciones_venta (factura_id, tipo_retencion_id, nro_certificado, importe, fecha_retencion, archivo, usuario_id) 
            VALUES ($factura_id, $tipo_retencion_id, '$nro_certificado', $importe, '$fecha_retencion', " . ($archivo_nombre ? "'$archivo_nombre'" : "NULL") . ", $usuario)";

    if($conn->query($sql)){
        facturaRecalcularEstado($conn, $factura_id); // la retención también descuenta del saldo
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}

// ==========================================
// NUEVO: LISTAR RETENCIONES DE UNA FACTURA
// ==========================================
if($accion == 'listar_retenciones'){
    header('Content-Type: application/json');
    $factura_id = (int)($_GET['factura_id'] ?? 0);

    $sql = "SELECT r.id, r.nro_certificado, r.importe, r.fecha_retencion, r.archivo, t.nombre AS tipo_nombre 
            FROM retenciones_venta r
            LEFT JOIN tipos_retenciones t ON r.tipo_retencion_id = t.id
            WHERE r.factura_id = $factura_id 
            ORDER BY r.id DESC";

    $res = $conn->query($sql);
    
    if(!$res){
        echo json_encode(["success" => false, "message" => $conn->error]);
        exit;
    }
    
    $data = [];
    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// ==========================================
// NUEVO: ELIMINAR RETENCIÓN
// ==========================================
if($accion == 'eliminar_retencion'){
    header('Content-Type: application/json');
    $id = (int)$_POST['id'];

    // Buscamos si la retención tiene un archivo físico asociado para eliminarlo
    $res = $conn->query("SELECT archivo, factura_id FROM retenciones_venta WHERE id=$id");
    if($row = $res->fetch_assoc()){
        if(!empty($row['archivo'])){
            $path = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/retenciones/' . $row['archivo'];
            if(file_exists($path)) @unlink($path);
        }

        $conn->query("DELETE FROM retenciones_venta WHERE id=$id");
        facturaRecalcularEstado($conn, (int)$row['factura_id'], true);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado.']);
    }
    exit;
}

// =====================================================================
// COBROS DE UNA FACTURA (pagos parciales o totales del cliente)
// Cada cobro puede registrar su ingreso en una caja: el movimiento queda
// con origen 'COBRO' y referencia_id = id del cobro, y se borra junto con él.
// =====================================================================

// =======================
// LISTAR COBROS + RESUMEN DE SALDO
// =======================
if($accion == 'listar_cobros'){
    header('Content-Type: application/json');
    $factura_id = (int)($_GET['factura_id'] ?? 0);

    if (!facturaAccesible($conn, $factura_id, $usuario, $es_admin_o_contador)) {
        echo json_encode(['success' => false, 'message' => 'Factura no encontrada o sin permisos.']);
        exit;
    }

    $data = [];
    $res = $conn->query("SELECT cv.id, cv.fecha, cv.importe, cv.comprobante, cv.observaciones, cv.archivo,
                                IFNULL(mp.nombre, '') AS medio_pago, IFNULL(cj.nombre, '') AS caja,
                                IFNULL(u.usuario, '') AS usuario
                         FROM cobros_venta cv
                         LEFT JOIN medios_pago mp ON mp.id = cv.medio_pago_id
                         LEFT JOIN cajas cj ON cj.id = cv.caja_id
                         LEFT JOIN usuarios u ON u.id = cv.usuario_id
                         WHERE cv.factura_id = $factura_id
                         ORDER BY cv.fecha ASC, cv.id ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data, 'resumen' => facturaImportes($conn, $factura_id)]);
    exit;
}

// =======================
// GUARDAR COBRO
// =======================
if($accion == 'guardar_cobro'){
    header('Content-Type: application/json');
    require_once $_SERVER['DOCUMENT_ROOT'].'/contable/includes/caja_saldo.php';

    $factura_id = (int)($_POST['cob_factura_id'] ?? 0);
    $fecha = trim($_POST['cob_fecha'] ?? '');
    $importe = round((float)($_POST['cob_importe'] ?? 0), 2);
    $medio_pago_id = (int)($_POST['cob_medio_pago_id'] ?? 0);
    $caja_id = (int)($_POST['cob_caja_id'] ?? 0);
    $comprobante = trim($_POST['cob_comprobante'] ?? '');
    $observaciones = trim($_POST['cob_observaciones'] ?? '');

    $factura = facturaAccesible($conn, $factura_id, $usuario, $es_admin_o_contador);
    if (!$factura) {
        echo json_encode(['success' => false, 'message' => 'Factura no encontrada o sin permisos.']);
        exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || $importe <= 0) {
        echo json_encode(['success' => false, 'message' => 'Indicá la fecha y un importe mayor a 0.']);
        exit;
    }
    $imp = facturaImportes($conn, $factura_id);
    if ($importe > $imp['saldo'] + FAC_TOLERANCIA) {
        echo json_encode(['success' => false, 'message' => 'El importe supera el saldo pendiente de la factura ($ ' . number_format($imp['saldo'], 2, ',', '.') . ').']);
        exit;
    }

    // Caja destino (opcional): debe existir y estar activa; los usuarios que no son
    // admin / contador solo pueden usar sus propias cajas y no las que están en solo lectura
    if ($caja_id > 0) {
        $res_c = $conn->query("SELECT id, nombre, activa, usuario_id, permite_movimientos FROM cajas WHERE id = $caja_id");
        $caja = $res_c ? $res_c->fetch_assoc() : null;
        if (!$caja || (int)$caja['activa'] !== 1) {
            echo json_encode(['success' => false, 'message' => 'La caja elegida no existe o no está activa.']);
            exit;
        }
        if (!$es_admin_o_contador && ((int)$caja['usuario_id'] !== (int)$usuario || (int)($caja['permite_movimientos'] ?? 1) === 0)) {
            echo json_encode(['success' => false, 'message' => 'No podés registrar movimientos en la caja "' . $caja['nombre'] . '".']);
            exit;
        }
    }

    // Comprobante del cobro (opcional): solo PDF o imagen
    $archivo_nombre = null;
    if (!empty($_FILES['cob_archivo']['name']) && $_FILES['cob_archivo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cob_archivo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            echo json_encode(['success' => false, 'message' => 'El comprobante debe ser PDF, JPG o PNG.']);
            exit;
        }
        $dir = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/cobros/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $archivo_nombre = 'COB_' . $factura_id . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($_FILES['cob_archivo']['tmp_name'], $dir . $archivo_nombre)) $archivo_nombre = null;
    }

    $conn->begin_transaction();
    try {
        $medio_sql = $medio_pago_id > 0 ? $medio_pago_id : null;
        $caja_sql = $caja_id > 0 ? $caja_id : null;
        $usuario_sql = $usuario ? (int)$usuario : null;
        $stmt = $conn->prepare("INSERT INTO cobros_venta (factura_id, fecha, importe, medio_pago_id, caja_id, comprobante, observaciones, archivo, usuario_id)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isdiisssi', $factura_id, $fecha, $importe, $medio_sql, $caja_sql, $comprobante, $observaciones, $archivo_nombre, $usuario_sql);
        if (!$stmt->execute()) throw new Exception($conn->error);
        $cobro_id = (int)$conn->insert_id;

        // Ingreso en la caja elegida
        if ($caja_id > 0) {
            $nro = str_pad($factura['punto_venta'], 5, '0', STR_PAD_LEFT) . '-' . str_pad($factura['nro_factura'], 8, '0', STR_PAD_LEFT);
            $concepto = mb_substr('Cobro factura ' . $nro . ($factura['cliente'] !== '' ? ' - ' . $factura['cliente'] : ''), 0, 255);
            $stmt_m = $conn->prepare("INSERT INTO movimientos_caja (fecha, caja_id, tipo, concepto, comprobante, importe, observaciones, origen, referencia_id, usuario_id)
                                      VALUES (?, ?, 'INGRESO', ?, ?, ?, ?, 'COBRO', ?, ?)");
            $stmt_m->bind_param('sissdsii', $fecha, $caja_id, $concepto, $comprobante, $importe, $observaciones, $cobro_id, $usuario_sql);
            if (!$stmt_m->execute()) throw new Exception($conn->error);
            $mov_id = (int)$conn->insert_id;
            $conn->query("UPDATE cobros_venta SET movimiento_caja_id = $mov_id WHERE id = $cobro_id");
        }

        facturaRecalcularEstado($conn, $factura_id);
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        if ($archivo_nombre) @unlink($_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/cobros/' . $archivo_nombre);
        echo json_encode(['success' => false, 'message' => 'No se pudo guardar el cobro: ' . $e->getMessage()]);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

// =======================
// ELIMINAR COBRO (y su ingreso en Caja)
// =======================
if($accion == 'eliminar_cobro'){
    header('Content-Type: application/json');
    require_once $_SERVER['DOCUMENT_ROOT'].'/contable/includes/caja_saldo.php';
    $id = (int)($_POST['id'] ?? 0);

    $res = $conn->query("SELECT factura_id, caja_id, movimiento_caja_id, archivo FROM cobros_venta WHERE id = $id");
    $cobro = $res ? $res->fetch_assoc() : null;
    if (!$cobro || !facturaAccesible($conn, $cobro['factura_id'], $usuario, $es_admin_o_contador)) {
        echo json_encode(['success' => false, 'message' => 'Cobro no encontrado o sin permisos.']);
        exit;
    }

    $conn->begin_transaction();
    // Quitar el ingreso baja el saldo de la caja: se respeta "permite saldo negativo"
    $saldos_antes = $cobro['caja_id'] ? cajaSaldos($conn, [(int)$cobro['caja_id']]) : [];
    if (!empty($cobro['movimiento_caja_id'])) {
        $conn->query("DELETE FROM movimientos_caja WHERE id = " . (int)$cobro['movimiento_caja_id'] . " AND origen = 'COBRO'");
    }
    $conn->query("DELETE FROM cobros_venta WHERE id = $id");
    $error = $saldos_antes ? cajaControlarNegativo($conn, $saldos_antes) : null;
    if ($error) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar el cobro: ' . $error]);
        exit;
    }
    facturaRecalcularEstado($conn, (int)$cobro['factura_id'], true);
    $conn->commit();

    if (!empty($cobro['archivo'])) {
        $p = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/cobros/' . basename($cobro['archivo']);
        if (file_exists($p)) @unlink($p);
    }

    echo json_encode(['success' => true]);
    exit;
}