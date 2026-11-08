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

require_once __DIR__ . '/../includes/condiciones_html.php';

$accion = $_GET['accion'] ?? '';
$usuario_logueado = $_SESSION['id'] ?? 0;

// ================= LISTAR PRESUPUESTOS =================
if ($accion === 'listar') {
    $desde = $_GET['desde'] ?? '';
    $hasta = $_GET['hasta'] ?? '';
    $cliente_id = intval($_GET['cliente_id'] ?? 0);
    $estado = $_GET['estado'] ?? '';
    $vista = $_GET['vista'] ?? 'activos'; // 'activos' = todo menos archivados | 'archivados'

    $where = [];
    $where[] = $vista === 'archivados' ? "p.estado = 'Archivado'" : "p.estado <> 'Archivado'";
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
            $sql_t = "SELECT t.id, t.codigo, t.nombre, t.unidad, t.costo_unitario_total, t.observaciones, t.rendimiento_unidad,
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
                        'costo_mo_neto' => $c_mo,
                        'observaciones' => $r['observaciones'] ?? '',
                        'rendimiento_unidad' => floatval($r['rendimiento_unidad'] ?? 1)
                    ];
                }
            }
        }

        // 4b. Detalle de insumos de cada APU (para el botón "Ver" y la pestaña Resumen)
        $apu_detalles = [];
        $chk_d = $conn->query("SHOW TABLES LIKE 'presupuesto_tarea_detalles'");
        if ($chk_d && $chk_d->num_rows > 0) {
            // Fecha de la última actualización de Mano de Obra (es una sola para todas las categorías)
            $fecha_mo = '';
            $res_mo = $conn->query("SELECT DATE_FORMAT(MAX(fecha_actualizacion), '%d/%m/%Y') AS f FROM presupuesto_mo_auditoria");
            if ($res_mo && ($row_mo = $res_mo->fetch_assoc())) {
                $fecha_mo = $row_mo['f'] ?? '';
            }

            // La fecha de materiales y equipos sale de su ficha, vinculada por recurso_id
            $res_d = $conn->query("SELECT d.tarea_id, d.tipo, d.descripcion, d.unidad, d.cantidad, d.precio_unitario, d.observaciones,
                                          DATE_FORMAT(CASE UPPER(d.tipo)
                                              WHEN 'MATERIAL' THEN m.fecha_actualizacion
                                              WHEN 'EQUIPO' THEN e.fecha_actualizacion
                                          END, '%d/%m/%Y') AS fecha_actualizacion
                                   FROM presupuesto_tarea_detalles d
                                   LEFT JOIN presupuesto_materiales m ON m.id = d.recurso_id AND UPPER(d.tipo) = 'MATERIAL'
                                   LEFT JOIN presupuesto_equipos e ON e.id = d.recurso_id AND UPPER(d.tipo) = 'EQUIPO'
                                   ORDER BY d.tarea_id ASC, FIELD(UPPER(d.tipo), 'MATERIAL', 'MO', 'EQUIPO', 'SUBCONTRATO'), d.id ASC");
            if ($res_d) {
                while ($r = $res_d->fetch_assoc()) {
                    $apu_detalles[$r['tarea_id']][] = [
                        'tipo' => strtoupper($r['tipo']),
                        'descripcion' => $r['descripcion'],
                        'unidad' => $r['unidad'],
                        'cantidad' => floatval($r['cantidad']),
                        'precio_unitario' => floatval($r['precio_unitario']),
                        'observaciones' => $r['observaciones'] ?? '',
                        'fecha_actualizacion' => strtoupper($r['tipo']) === 'MO' ? $fecha_mo : ($r['fecha_actualizacion'] ?? '')
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

        // 6. Condiciones Generales: todas las bases activas y el texto de la base por defecto
        $condiciones_texto = "";
        $condiciones_bases = [];
        $chk_cg = $conn->query("SHOW TABLES LIKE 'presupuesto_condiciones_generales'");
        if ($chk_cg && $chk_cg->num_rows > 0) {
            $res_cond_m = $conn->query("SELECT id, nombre, contenido, por_defecto
                                        FROM presupuesto_condiciones_generales
                                        WHERE activo = 1
                                        ORDER BY por_defecto DESC, nombre ASC");
            if ($res_cond_m) {
                while ($row_c = $res_cond_m->fetch_assoc()) {
                    $condiciones_bases[] = [
                        'id' => intval($row_c['id']),
                        'nombre' => $row_c['nombre'],
                        'contenido' => $row_c['contenido'],
                        'por_defecto' => intval($row_c['por_defecto'])
                    ];
                    if (intval($row_c['por_defecto']) === 1 && $condiciones_texto === "") {
                        $condiciones_texto = $row_c['contenido'];
                    }
                }
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
            'apu_detalles' => (object)$apu_detalles,
            'indirectos' => $indirectos,
            'condiciones_texto' => $condiciones_texto,
            'condiciones_bases' => $condiciones_bases,
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

    // Condiciones con formato: solo negrita, cursiva, subrayado y listas
    $post_data['condiciones'] = limpiarCondicionesHtml($post_data['condiciones'] ?? '');

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

    // Si se continúa desde una versión recuperada, se deja constancia en el comentario.
    // El dato no se guarda dentro del snapshot.
    $recuperada_de = intval($post_data['recuperada_de_version'] ?? 0);
    unset($post_data['recuperada_de_version']);

    // Todo el guardado va en una transacción: el presupuesto y su nueva versión se
    // graban juntos, y el número de versión se asigna con el presupuesto bloqueado
    // (SELECT ... FOR UPDATE) para que dos guardados simultáneos no repitan número.
    $conn->begin_transaction();
    try {
        if ($id > 0) {
            $res_lock = $conn->query("SELECT id FROM presupuestos WHERE id = $id FOR UPDATE");
            if (!$res_lock || $res_lock->num_rows === 0) {
                throw new Exception('El presupuesto no existe o fue eliminado.');
            }

            $sql = "UPDATE presupuestos SET
                    codigo='$codigo', fecha='$fecha', cliente_id=$cliente_id, obra_id=$obra_id,
                    titulo='$titulo', estado='$estado', total_neto=$total_neto,
                    coeficiente_k_general=$coeficiente_k_general, total_presupuestado=$total_presupuestado
                    WHERE id=$id";
            if (!$conn->query($sql)) throw new Exception($conn->error);
            $presupuesto_id = $id;

            // Siempre correlativo al último existente (no al número de la versión recuperada)
            $res_v = $conn->query("SELECT COALESCE(MAX(version_numero), 0) + 1 AS proxima FROM presupuesto_versiones WHERE presupuesto_id = $presupuesto_id");
            $ver_num = ($res_v && $r_v = $res_v->fetch_assoc()) ? intval($r_v['proxima']) : 1;
        } else {
            $sql = "INSERT INTO presupuestos
                    (codigo, fecha, cliente_id, obra_id, titulo, estado, total_neto, coeficiente_k_general, total_presupuestado, usuario_id)
                    VALUES ('$codigo', '$fecha', $cliente_id, $obra_id, '$titulo', '$estado', $total_neto, $coeficiente_k_general, $total_presupuestado, $usuario_logueado)";
            if (!$conn->query($sql)) throw new Exception($conn->error);
            $presupuesto_id = $conn->insert_id;
            $ver_num = 1;
        }

        $post_data['id'] = (string)$presupuesto_id; // el snapshot queda con el ID real
        $snapshot_json = $conn->real_escape_string(json_encode($post_data, JSON_UNESCAPED_UNICODE));

        if ($recuperada_de > 0) {
            $comentario_txt = "Recuperación de versión $recuperada_de - Se continúa trabajando desde esta versión";
        } else {
            $comentario_txt = $post_data['comentario_version'] ?? ($id > 0 ? "Actualización de versión $ver_num" : "Creación inicial versión 1");
        }
        $comentario = $conn->real_escape_string(mb_substr($comentario_txt, 0, 255));

        $sql_version = "INSERT INTO presupuesto_versiones (presupuesto_id, version_numero, snapshot_json, comentario, usuario_id)
                        VALUES ($presupuesto_id, $ver_num, '$snapshot_json', '$comentario', $usuario_logueado)";
        if (!$conn->query($sql_version)) throw new Exception($conn->error);

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'No se pudo guardar: ' . $e->getMessage()]);
        exit;
    }

    echo json_encode(['success' => true, 'id' => $presupuesto_id, 'version' => $ver_num]);
    exit;
}

// ================= CAMBIAR ESTADO DESDE EL LISTADO =================
// Crea una versión nueva, copia de la última pero con el estado nuevo, para que
// quede registrado quién lo cambió y cuándo, y para que editar después respete el
// estado. Las versiones anteriores no se modifican.
if ($accion === 'cambiar_estado') {
    $estados_validos = ['Borrador', 'Enviado', 'Aprobado', 'Rechazado', 'Archivado'];
    $id = intval($_POST['id'] ?? 0);
    $estado_nuevo = $_POST['estado'] ?? '';

    if ($id <= 0 || !in_array($estado_nuevo, $estados_validos, true)) {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $conn->begin_transaction();
    try {
        // Bloquea el presupuesto: serializa con otros guardados y cambios de estado
        $res_p = $conn->query("SELECT estado FROM presupuestos WHERE id = $id FOR UPDATE");
        if (!$res_p || !($row_p = $res_p->fetch_assoc())) {
            throw new Exception('El presupuesto no existe o fue eliminado.');
        }
        $estado_anterior = $row_p['estado'];

        if ($estado_anterior === $estado_nuevo) {
            $conn->rollback();
            echo json_encode(['success' => true, 'sin_cambios' => true]);
            exit;
        }

        $res_v = $conn->query("SELECT snapshot_json, version_numero FROM presupuesto_versiones
                               WHERE presupuesto_id = $id ORDER BY version_numero DESC LIMIT 1");
        $ultima = $res_v ? $res_v->fetch_assoc() : null;
        if (!$ultima) {
            throw new Exception('El presupuesto no tiene versiones guardadas.');
        }

        $snapshot = json_decode($ultima['snapshot_json'], true) ?: [];
        $snapshot['estado'] = $estado_nuevo;
        $ver_num = intval($ultima['version_numero']) + 1;

        $snapshot_sql = $conn->real_escape_string(json_encode($snapshot, JSON_UNESCAPED_UNICODE));
        $comentario = $conn->real_escape_string("Cambio de estado: $estado_anterior → $estado_nuevo");
        $estado_sql = $conn->real_escape_string($estado_nuevo);

        if (!$conn->query("INSERT INTO presupuesto_versiones (presupuesto_id, version_numero, snapshot_json, comentario, usuario_id)
                           VALUES ($id, $ver_num, '$snapshot_sql', '$comentario', $usuario_logueado)")) {
            throw new Exception($conn->error);
        }
        if (!$conn->query("UPDATE presupuestos SET estado = '$estado_sql' WHERE id = $id")) {
            throw new Exception($conn->error);
        }

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'No se pudo cambiar el estado: ' . $e->getMessage()]);
        exit;
    }

    echo json_encode(['success' => true, 'version' => $ver_num, 'estado' => $estado_nuevo]);
    exit;
}

// ================= BORRAR UNA VERSIÓN =================
// Borrado definitivo de una versión del historial. Reglas:
// - No se puede borrar la única versión (para eso está Eliminar presupuesto).
// - Si se borra la última, el presupuesto vuelve a los datos de la versión anterior,
//   para que el listado y Editar coincidan.
// - Los números no se renumeran (pueden quedar huecos: v1, v2, v4).
if ($accion === 'borrar_version') {
    $id = intval($_POST['id'] ?? 0);
    $version_id = intval($_POST['version_id'] ?? 0);
    if ($id <= 0 || $version_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $conn->begin_transaction();
    try {
        $res_p = $conn->query("SELECT id FROM presupuestos WHERE id = $id FOR UPDATE");
        if (!$res_p || $res_p->num_rows === 0) {
            throw new Exception('El presupuesto no existe o fue eliminado.');
        }

        $res_v = $conn->query("SELECT version_numero FROM presupuesto_versiones WHERE presupuesto_id = $id AND id = $version_id");
        $ver = $res_v ? $res_v->fetch_assoc() : null;
        if (!$ver) {
            throw new Exception('La versión no pertenece a este presupuesto.');
        }

        $res_c = $conn->query("SELECT COUNT(*) AS total, MAX(version_numero) AS ultima FROM presupuesto_versiones WHERE presupuesto_id = $id");
        $info = $res_c->fetch_assoc();
        if (intval($info['total']) <= 1) {
            throw new Exception('Es la única versión del presupuesto. Para borrarla, eliminá el presupuesto completo.');
        }
        $era_ultima = intval($ver['version_numero']) === intval($info['ultima']);

        if (!$conn->query("DELETE FROM presupuesto_versiones WHERE id = $version_id AND presupuesto_id = $id")) {
            throw new Exception($conn->error);
        }

        // Si era la última, el presupuesto toma los datos de la que queda como última
        $nueva_ultima = null;
        if ($era_ultima) {
            $res_n = $conn->query("SELECT snapshot_json, version_numero FROM presupuesto_versiones
                                   WHERE presupuesto_id = $id ORDER BY version_numero DESC LIMIT 1");
            $fila_n = $res_n->fetch_assoc();
            $s = json_decode($fila_n['snapshot_json'], true) ?: [];
            $nueva_ultima = intval($fila_n['version_numero']);

            $codigo  = $conn->real_escape_string($s['codigo'] ?? '');
            $fecha   = $conn->real_escape_string(($s['fecha'] ?? '') ?: date('Y-m-d'));
            $cli     = intval($s['cliente_id'] ?? 0) ?: 'NULL';
            $obra    = intval($s['obra_id'] ?? 0) ?: 'NULL';
            $titulo  = $conn->real_escape_string($s['titulo'] ?? '');
            $estado  = $conn->real_escape_string($s['estado'] ?? 'Borrador');
            $neto    = floatval($s['total_neto'] ?? 0);
            $k       = floatval($s['coeficiente_k_general'] ?? 1);
            $total   = floatval($s['total_presupuestado'] ?? 0);

            $sql = "UPDATE presupuestos SET codigo='$codigo', fecha='$fecha', cliente_id=$cli, obra_id=$obra,
                    titulo='$titulo', estado='$estado', total_neto=$neto, coeficiente_k_general=$k,
                    total_presupuestado=$total WHERE id=$id";
            if (!$conn->query($sql)) throw new Exception($conn->error);
        }

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }

    echo json_encode(['success' => true, 'era_ultima' => $era_ultima, 'nueva_ultima' => $nueva_ultima]);
    exit;
}

// ================= HISTORIAL DE VERSIONES (bajo demanda) =================
// Solo trae los datos para la lista; el JSON completo se pide al ver una versión.
if ($accion === 'historial') {
    $id = intval($_GET['id'] ?? 0);
    $versiones = [];
    $cabecera = null; // código y título, por si el presupuesto no está en la pestaña visible del listado
    if ($id > 0) {
        $res_c = $conn->query("SELECT codigo, titulo FROM presupuestos WHERE id = $id");
        $cabecera = $res_c ? $res_c->fetch_assoc() : null;

        $res = $conn->query("SELECT v.id, v.version_numero,
                                    DATE_FORMAT(v.fecha_version, '%d/%m/%Y %H:%i') AS fecha,
                                    v.comentario,
                                    IFNULL(u.usuario, '-') AS usuario,
                                    JSON_UNQUOTE(JSON_EXTRACT(v.snapshot_json, '$.total_presupuestado')) AS total_presupuestado
                             FROM presupuesto_versiones v
                             LEFT JOIN usuarios u ON u.id = v.usuario_id
                             WHERE v.presupuesto_id = $id
                             ORDER BY v.version_numero DESC");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $r['id'] = intval($r['id']);
                $r['version_numero'] = intval($r['version_numero']);
                $r['total_presupuestado'] = floatval($r['total_presupuestado']);
                $versiones[] = $r;
            }
        }
    }
    echo json_encode(['success' => true, 'versiones' => $versiones, 'presupuesto' => $cabecera]);
    exit;
}

// ================= OBTENER DETALLE =================
if ($accion === 'obtener_detalle') {
    $id = intval($_GET['id'] ?? 0);
    $version = intval($_GET['version'] ?? 0);        // número de versión
    $version_id = intval($_GET['version_id'] ?? 0);  // ID del registro de la versión (historial)

    $campos = "v.id, v.version_numero, DATE_FORMAT(v.fecha_version, '%d/%m/%Y %H:%i') AS fecha, v.comentario, IFNULL(u.usuario, '-') AS usuario, v.snapshot_json";
    $desde = "FROM presupuesto_versiones v LEFT JOIN usuarios u ON u.id = v.usuario_id";
    if ($version_id > 0) {
        $sql = "SELECT $campos $desde WHERE v.presupuesto_id = $id AND v.id = $version_id";
    } elseif ($version > 0) {
        $sql = "SELECT $campos $desde WHERE v.presupuesto_id = $id AND v.version_numero = $version";
    } else {
        $sql = "SELECT $campos $desde WHERE v.presupuesto_id = $id ORDER BY v.version_numero DESC LIMIT 1";
    }

    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        $res_ult = $conn->query("SELECT MAX(version_numero) AS ultima FROM presupuesto_versiones WHERE presupuesto_id = $id");
        $ultima = ($res_ult && $r_u = $res_ult->fetch_assoc()) ? intval($r_u['ultima']) : 0;

        // Las condiciones se devuelven limpias (solo formato permitido): las versiones
        // anteriores al editor pueden tener texto plano. No se modifica lo guardado.
        $detalle = json_decode($row['snapshot_json'], true) ?: [];
        $detalle['condiciones'] = limpiarCondicionesHtml($detalle['condiciones'] ?? '');

        echo json_encode([
            'success' => true,
            'detalle' => $detalle,
            'version' => [
                'id' => intval($row['id']),
                'numero' => intval($row['version_numero']),
                'fecha' => $row['fecha'],
                'comentario' => $row['comentario'],
                'usuario' => $row['usuario'],
                'es_ultima' => intval($row['version_numero']) === $ultima
            ]
        ]);
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