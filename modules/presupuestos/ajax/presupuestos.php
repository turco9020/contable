<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$db_path = $_SERVER['DOCUMENT_ROOT'] . '/contable/config/database.php';
if (!file_exists($db_path)) {
    echo json_encode(['success' => false, 'error' => "No se encontró el archivo de conexión: $db_path"]);
    exit;
}
include $db_path;

if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$accion = $_GET['accion'] ?? '';
$usuario_logueado = $_SESSION['id'] ?? 0;

// ================= LISTAR PRESUPUESTOS =================
if ($accion === 'listar') {
    $desde = $_GET['desde'] ?? '';
    $hasta = $_GET['hasta'] ?? '';
    $cliente_id = intval($_GET['cliente_id'] ?? 0);
    $estado = $_GET['estado'] ?? '';

    $where = [];
    if ($desde) $where[] = "p.fecha >= '" . $conn->real_escape_string($desde) . "'";
    if ($hasta) $where[] = "p.fecha <= '" . $conn->real_escape_string($hasta) . "'";
    if ($cliente_id > 0) $where[] = "p.cliente_id = $cliente_id";
    if ($estado) $where[] = "p.estado = '" . $conn->real_escape_string($estado) . "'";

    $sql_where = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT p.*, c.nombre AS cliente_nombre, o.nombre AS obra_nombre, u.usuario AS usuario_nombre
            FROM presupuestos p
            LEFT JOIN clientes c ON c.id = p.cliente_id
            LEFT JOIN obras o ON o.id = p.obra_id
            LEFT JOIN usuarios u ON u.id = p.usuario_id
            $sql_where
            ORDER BY p.id DESC";

    $res = $conn->query($sql);
    $data = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode(["data" => $data]);
    exit;
}

// ================= CARGAR DATOS INICIALES Y MAESTROS =================
if ($accion === 'cargar_maestros') {
    try {
        // 1. Clientes
        $clientes = [];
        $res_c = $conn->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
        if ($res_c) {
            while ($r = $res_c->fetch_assoc()) { $clientes[] = $r; }
        }

        // 2. Obras
        $obras = [];
        $chk_col = $conn->query("SHOW COLUMNS FROM obras LIKE 'cliente_id'");
        $tiene_cliente_id = ($chk_col && $chk_col->num_rows > 0);

        $sql_obras = "SELECT id, nombre " . ($tiene_cliente_id ? ", cliente_id" : ", NULL AS cliente_id") . " FROM obras ORDER BY nombre ASC";
        $res_o = $conn->query($sql_obras);
        if ($res_o) {
            while ($r = $res_o->fetch_assoc()) { $obras[] = $r; }
        }

        // 3. Coeficientes K Plantillas
        $coeficientes = [];
        $chk_k = $conn->query("SHOW TABLES LIKE 'presupuesto_coeficientes_plantillas'");
        if ($chk_k && $chk_k->num_rows > 0) {
            $res_k = $conn->query("SELECT id, nombre, mat_k_resultante, mo_k_resultante FROM presupuesto_coeficientes_plantillas ORDER BY id ASC");
            if ($res_k) {
                while ($r = $res_k->fetch_assoc()) {
                    $coeficientes[] = [
                        'id' => $r['id'],
                        'nombre' => $r['nombre'],
                        'mat_k_resultante' => floatval($r['mat_k_resultante'] ?? 1.0000),
                        'mo_k_resultante' => floatval($r['mo_k_resultante'] ?? 1.0000)
                    ];
                }
            }
        }

        // 4. Tareas APU con desglose de costos netos de Material y Mano de Obra/Otros
        $tareas = [];
        $chk_t = $conn->query("SHOW TABLES LIKE 'presupuesto_tareas'");
        if ($chk_t && $chk_t->num_rows > 0) {
            $sql_t = "SELECT t.id, t.codigo, t.nombre, t.unidad, t.costo_unitario_total,
                        COALESCE(SUM(CASE WHEN UPPER(d.tipo) = 'MATERIAL' THEN (d.cantidad * d.precio_unitario) ELSE 0 END), 0) AS costo_material_neto,
                        COALESCE(SUM(CASE WHEN UPPER(d.tipo) IN ('MO', 'EQUIPO', 'SUBCONTRATO') THEN (d.cantidad * d.precio_unitario) ELSE 0 END), 0) AS costo_mo_neto
                      FROM presupuesto_tareas t
                      LEFT JOIN presupuesto_tarea_detalles d ON d.tarea_id = t.id
                      GROUP BY t.id
                      ORDER BY t.codigo ASC, t.nombre ASC";
            
            $res_t = $conn->query($sql_t);
            if ($res_t) {
                while ($r = $res_t->fetch_assoc()) {
                    $c_mat = floatval($r['costo_material_neto']);
                    $c_mo = floatval($r['costo_mo_neto']);
                    $c_total = floatval($r['costo_unitario_total']);

                    if ($c_mat == 0 && $c_mo == 0 && $c_total > 0) {
                        $c_mat = $c_total;
                    }

                    $tareas[] = [
                        'id' => $r['id'],
                        'codigo' => $r['codigo'],
                        'nombre' => $r['nombre'],
                        'unidad' => $r['unidad'],
                        'costo_unitario_total' => $c_total,
                        'costo_material_neto' => $c_mat,
                        'costo_mo_neto' => $c_mo
                    ];
                }
            }
        }

        // 5. Indirectos
        $indirectos = [];
        $chk_i = $conn->query("SHOW TABLES LIKE 'presupuesto_indirectos_plantilla'");
        if ($chk_i && $chk_i->num_rows > 0) {
            // Consulta SQL ajustada con LEFT JOIN para traer el nombre de la categoría
            $sql_ind = "SELECT p.*, c.nombre AS categoria_nombre 
                        FROM presupuesto_indirectos_plantilla p 
                        LEFT JOIN presupuesto_indirectos_categorias c ON c.codigo = p.categoria_codigo 
                        ORDER BY p.id ASC";
            
            $res_ind = $conn->query($sql_ind);
            if ($res_ind) {
                while ($r = $res_ind->fetch_assoc()) {
                    $indirectos[] = [
                        'id' => $r['id'],
                        'categoria_codigo' => $r['categoria_codigo'] ?? '',
                        'categoria_nombre' => $r['categoria_nombre'] ?? 'Sin Categoría', // Metadato de la categoría
                        'item_codigo' => $r['item_codigo'] ?? '',
                        'descripcion' => $r['descripcion'] ?? '',
                        'unidad' => $r['unidad'] ?? 'UN',
                        'cantidad_defecto' => $r['cantidad_defecto'] ?? $r['cantidad'] ?? 1,
                        'unitario_defecto' => $r['unitario_defecto'] ?? $r['precio_unitario'] ?? 0,
                        'afectacion_defecto' => $r['afectacion_defecto'] ?? 1.00,
                        'depende_operarios' => intval($r['depende_operarios'] ?? 0),     // Metadato operarios
                        'depende_tiempo' => intval($r['depende_tiempo'] ?? 0),           // Metadato tiempo
                        'activo' => intval($r['activo'] ?? 1)
                    ];
                }
            }
        }

        // 6. Condiciones Generales
        $condiciones_texto = "";
        $chk_cg = $conn->query("SHOW TABLES LIKE 'presupuesto_condiciones_generales'");
        if ($chk_cg && $chk_cg->num_rows > 0) {
            $res_cond_m = $conn->query("SELECT contenido FROM presupuesto_condiciones_generales WHERE por_defecto = 1 LIMIT 1");
            if ($res_cond_m && $row_c = $res_cond_m->fetch_assoc()) {
                $condiciones_texto = $row_c['contenido'];
            }
        }

        $anio = date('Y');
        $res_cod = $conn->query("SELECT COUNT(*)+1 AS proximo FROM presupuestos WHERE YEAR(fecha) = '$anio'");
        $num = ($res_cod && $r_cod = $res_cod->fetch_assoc()) ? $r_cod['proximo'] : 1;
        $codigo_sugerido = sprintf("PRE-%s-%03d", $anio, $num);

        echo json_encode([
            'success' => true,
            'clientes' => $clientes,
            'obras' => $obras,
            'coeficientes' => $coeficientes,
            'tareas' => $tareas,
            'indirectos' => $indirectos,
            'condiciones_texto' => $condiciones_texto,
            'codigo_sugerido' => $codigo_sugerido,
            'fecha_hoy' => date('Y-m-d')
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error en servidor: ' . $e->getMessage()]);
    }
    exit;
}

// ================= GUARDAR PRESUPUESTO =================
if ($accion === 'guardar') {
    $raw_input = file_get_contents('php://input');
    $post_data = json_decode($raw_input, true);

    if (!$post_data) {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $id = intval($post_data['id'] ?? 0);
    $codigo = $conn->real_escape_string($post_data['codigo'] ?? '');
    $fecha = $conn->real_escape_string($post_data['fecha'] ?? date('Y-m-d'));
    $cliente_id = intval($post_data['cliente_id'] ?? 0) ?: 'NULL';
    $obra_id = intval($post_data['obra_id'] ?? 0) ?: 'NULL';
    $titulo = $conn->real_escape_string($post_data['titulo'] ?? '');
    $estado = $conn->real_escape_string($post_data['estado'] ?? 'Borrador');
    $total_neto = floatval($post_data['total_neto'] ?? 0);
    $coeficiente_k_general = floatval($post_data['coeficiente_k_general'] ?? 1);
    $total_presupuestado = floatval($post_data['total_presupuestado'] ?? 0);

    if ($id > 0) {
        $sql = "UPDATE presupuestos SET 
                codigo='$codigo', fecha='$fecha', cliente_id=$cliente_id, obra_id=$obra_id,
                titulo='$titulo', estado='$estado', total_neto=$total_neto, 
                coeficiente_k_general=$coeficiente_k_general, total_presupuestado=$total_presupuestado
                WHERE id=$id";
        $conn->query($sql);
        $presupuesto_id = $id;

        $res_v = $conn->query("SELECT MAX(version_numero)+1 AS proxima FROM presupuesto_versiones WHERE presupuesto_id = $presupuesto_id");
        $ver_num = ($res_v && $r_v = $res_v->fetch_assoc()) ? intval($r_v['proxima']) : 1;
    } else {
        $sql = "INSERT INTO presupuestos 
                (codigo, fecha, cliente_id, obra_id, titulo, estado, total_neto, coeficiente_k_general, total_presupuestado, usuario_id)
                VALUES ('$codigo', '$fecha', $cliente_id, $obra_id, '$titulo', '$estado', $total_neto, $coeficiente_k_general, $total_presupuestado, $usuario_logueado)";
        $conn->query($sql);
        $presupuesto_id = $conn->insert_id;
        $ver_num = 1;
    }

    $snapshot_json = $conn->real_escape_string(json_encode($post_data, JSON_UNESCAPED_UNICODE));
    $comentario = $conn->real_escape_string($post_data['comentario_version'] ?? ($id > 0 ? "Actualización de versión $ver_num" : "Creación inicial versión 1"));

    $sql_version = "INSERT INTO presupuesto_versiones (presupuesto_id, version_numero, snapshot_json, comentario, usuario_id)
                    VALUES ($presupuesto_id, $ver_num, '$snapshot_json', '$comentario', $usuario_logueado)";
    $conn->query($sql_version);

    echo json_encode(['success' => true, 'id' => $presupuesto_id, 'version' => $ver_num]);
    exit;
}

// ================= OBTENER DETALLE =================
if ($accion === 'obtener_detalle') {
    $id = intval($_GET['id'] ?? 0);
    $version = intval($_GET['version'] ?? 0);

    if ($version > 0) {
        $sql = "SELECT snapshot_json FROM presupuesto_versiones WHERE presupuesto_id = $id AND version_numero = $version";
    } else {
        $sql = "SELECT snapshot_json FROM presupuesto_versiones WHERE presupuesto_id = $id ORDER BY version_numero DESC LIMIT 1";
    }

    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        echo json_encode(['success' => true, 'detalle' => json_decode($row['snapshot_json'], true)]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se encontró la versión solicitada']);
    }
    exit;
}

// ================= ELIMINAR =================
if ($accion === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $conn->query("DELETE FROM presupuesto_versiones WHERE presupuesto_id = $id");
        $conn->query("DELETE FROM presupuestos WHERE id = $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'ID no válido']);
    }
    exit;
}