<?php
// C:\xampp\htdocs\contable\modules\presupuestos\ajax\tareas_acciones.php
session_start();
require_once '../../../config/database.php';

$db_conn = $conn ?? $conexion ?? $db ?? null;

// Obtener usuario activo
$user_id = $_SESSION['id'] ?? 0;
$usuario_actual = 'Sistema';

if ($db_conn && $user_id > 0) {
    $stmt_u = $db_conn->prepare("SELECT usuario FROM usuarios WHERE id = ?");
    if ($stmt_u) {
        $stmt_u->bind_param("i", $user_id);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();
        if ($row_u = $res_u->fetch_assoc()) {
            $usuario_actual = $row_u['usuario'];
        }
    }
}

// PROCESAMIENTO AJAX
if (isset($_GET['action'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    // 1. Obtener catálogo general de recursos
    if ($action === 'obtener_catalogo') {
        $catalogo = ['MATERIAL' => [], 'EQUIPO' => [], 'MO' => []];
        
        if ($db_conn) {
            // Materiales
            $res_mat = $db_conn->query("SELECT id, nombre, unidad_medida AS unidad, precio_unitario AS precio, DATE_FORMAT(fecha_actualizacion, '%d/%m/%Y') AS fecha_actualizacion FROM presupuesto_materiales WHERE activo = 1 ORDER BY nombre ASC");
            if ($res_mat) {
                while ($r = $res_mat->fetch_assoc()) { 
                    $r['fecha_actualizacion'] = $r['fecha_actualizacion'] ?? '-';
                    $catalogo['MATERIAL'][] = $r; 
                }
            }

            // Equipos
            $res_eq = $db_conn->query("SELECT id, nombre, unidad_medida AS unidad, precio, DATE_FORMAT(fecha_actualizacion, '%d/%m/%Y') AS fecha_actualizacion FROM presupuesto_equipos WHERE activo = 1 ORDER BY nombre ASC");
            if ($res_eq) {
                while ($r = $res_eq->fetch_assoc()) { 
                    $r['fecha_actualizacion'] = $r['fecha_actualizacion'] ?? '-';
                    $catalogo['EQUIPO'][] = $r; 
                }
            }
            
            // ----------------------------------------------------
            // MANO DE OBRA
            // ----------------------------------------------------
            // 1. Obtener la última fecha registrada en la tabla de auditoría
            $fecha_mo_ultima = '-';
            $res_mo_aud = $db_conn->query("SELECT DATE_FORMAT(MAX(fecha_actualizacion), '%d/%m/%Y') AS ultima_fecha FROM presupuesto_mo_auditoria");
            if ($res_mo_aud && $row_mo_aud = $res_mo_aud->fetch_assoc()) {
                if (!empty($row_mo_aud['ultima_fecha'])) {
                    $fecha_mo_ultima = $row_mo_aud['ultima_fecha'];
                }
            }

            // 2. Parámetros y cálculos de costos adicionales
            $vianda_diaria = $config['vianda_diaria'] ?? 7500;
            $costo_vianda_hora = $vianda_diaria / (160 / 22);

            $preocupacional_unitario = $config['preocupacional_unitario'] ?? 95000;
            $costo_preocup_hora = $preocupacional_unitario / (160 * 18);
            
            $costo_epp_hora = $config['costo_epp_hora'] ?? 0;

            // 3. Consulta de categorías asignando la fecha de auditoría a todas las categorías
            $res_cat = $db_conn->query("SELECT * FROM presupuesto_mo_categorias WHERE activo = 1 ORDER BY nombre ASC");
            if ($res_cat) {
                while ($cat = $res_cat->fetch_assoc()) {
                    $basico_hs = floatval($cat['valor_hora_basico']);
                    $horas_mes = intval($cat['horas_mes'] ?? 160);
                    $suma_no_rem = floatval($cat['suma_no_remunerativa'] ?? 0);
                    
                    $rem_mensual = $basico_hs * $horas_mes;
                    $presentismo = $rem_mensual * ($config['presentismo'] ?? 0.20);
                    $bruto_mensual = $rem_mensual + $presentismo;

                    $vacaciones = (0.5 / 12) * $bruto_mensual;
                    $feriados = (($config['feriados_anuales'] ?? 16) / 12 / 22) * $bruto_mensual;
                    $sac = (1 / 12) * $bruto_mensual;
                    $enfermedad = ($config['incidencia_enfermedad'] ?? 0.025) * $bruto_mensual;

                    $cuss = $bruto_mensual * ($config['cuss'] ?? 0.17);
                    $obra_social = $bruto_mensual * ($config['obra_social'] ?? 0.06);
                    $art = $bruto_mensual * ($config['art'] ?? 0.07);
                    $fondo_desempleo = $bruto_mensual * ($config['fondo_desempleo'] ?? 0.12);

                    $ieric = $fondo_desempleo * ($config['ieric'] ?? 0.02);
                    $fodeco = $fondo_desempleo * ($config['fodeco'] ?? 0.01);
                    $capacitacion = $fondo_desempleo * ($config['capacitacion'] ?? 0.01);
                    $extra_gremio = $bruto_mensual * ($config['extra_gremio'] ?? 0.00);

                    $jubilacion_obrero = $bruto_mensual * ($config['jubilacion_obrero'] ?? 0.11);
                    $pami_obrero = $bruto_mensual * ($config['pami_obrero'] ?? 0.03);
                    $os_obrero = $bruto_mensual * ($config['obra_social_obrero'] ?? 0.03);
                    $sindicato_obrero = $bruto_mensual * ($config['sindicato_obrero'] ?? 0.025);

                    $aporte_no_rem = $suma_no_rem * ($config['aporte_no_remunerativo'] ?? 0.07);

                    $subtotal_cargas = $vacaciones + $feriados + $sac + $enfermedad + $cuss + $obra_social + $art + $fondo_desempleo + $ieric + $fodeco + $capacitacion + $extra_gremio + $jubilacion_obrero + $pami_obrero + $os_obrero + $sindicato_obrero + $aporte_no_rem;

                    $total_mensual = $bruto_mensual + $suma_no_rem + $subtotal_cargas;

                    $costo_unitario_hs = ($horas_mes > 0) ? ($total_mensual / $horas_mes) : 0;
                    $costo_horario_final = $costo_unitario_hs + $costo_epp_hora + $costo_vianda_hora + $costo_preocup_hora;

                    $catalogo['MO'][] = [
                        'id' => $cat['id'],
                        'nombre' => $cat['nombre'],
                        'unidad' => 'HS',
                        'precio' => round($costo_horario_final, 2),
                        'fecha_actualizacion' => $fecha_mo_ultima
                    ];
                }
            }
        }

        echo json_encode(['success' => true, 'catalogo' => $catalogo]);
        exit;
    }

    // 2. Guardar / Actualizar Tarea (APU)
    if ($action === 'guardar_tarea' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['nombre'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos (Nombre de la tarea).']);
            exit;
        }

        $id = intval($input['id'] ?? 0);
        $nombre = trim($input['nombre']);
        $codigo = trim($input['codigo'] ?? '');
        $unidad = trim($input['unidad'] ?? 'GL');
        $rendimiento = floatval($input['rendimiento_unidad'] ?? 1);
        $observaciones = trim($input['observaciones'] ?? '');

        $c_mat = floatval($input['costo_materiales'] ?? 0);
        $c_mo = floatval($input['costo_mo'] ?? 0);
        $c_eq = floatval($input['costo_equipos'] ?? 0);
        $c_sub = floatval($input['costo_subcontratos'] ?? 0);
        $costo_total = $c_mat + $c_mo + $c_eq + $c_sub;

        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_tareas SET codigo=?, nombre=?, unidad=?, rendimiento_unidad=?, costo_materiales=?, costo_mo=?, costo_equipos=?, costo_subcontratos=?, costo_unitario_total=?, observaciones=?, usuario_nombre=? WHERE id=?");
            $stmt->bind_param("sssddddddssi", $codigo, $nombre, $unidad, $rendimiento, $c_mat, $c_mo, $c_eq, $c_sub, $costo_total, $observaciones, $usuario_actual, $id);
            $stmt->execute();

            $stmt_del = $db_conn->prepare("DELETE FROM presupuesto_tarea_detalles WHERE tarea_id = ?");
            $stmt_del->bind_param("i", $id);
            $stmt_del->execute();
            $tarea_id = $id;
        } else {
            $stmt = $db_conn->prepare("INSERT INTO presupuesto_tareas (codigo, nombre, unidad, rendimiento_unidad, costo_materiales, costo_mo, costo_equipos, costo_subcontratos, costo_unitario_total, observaciones, usuario_nombre) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssddddddss", $codigo, $nombre, $unidad, $rendimiento, $c_mat, $c_mo, $c_eq, $c_sub, $costo_total, $observaciones, $usuario_actual);
            $stmt->execute();
            $tarea_id = $db_conn->insert_id;
        }

        if (!empty($input['detalles']) && is_array($input['detalles'])) {
            $stmt_det = $db_conn->prepare("INSERT INTO presupuesto_tarea_detalles (tarea_id, tipo, recurso_id, descripcion, unidad, precio_unitario, cantidad, subtotal, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($input['detalles'] as $item) {
                $rec_id = !empty($item['recurso_id']) ? intval($item['recurso_id']) : null;
                $stmt_det->bind_param("isissddds", 
                    $tarea_id, 
                    $item['tipo'], $rec_id, 
                    $item['descripcion'], $item['unidad'], 
                    $item['precio_unitario'], $item['cantidad'], 
                    $item['subtotal'], $item['observaciones']
                );
                $stmt_det->execute();
            }
        }

        echo json_encode(['success' => true, 'message' => 'Tarea / APU guardada exitosamente.', 'tarea_id' => $tarea_id]);
        exit;
    }

    // 3. Obtener Tarea para Edición / Vista
    if ($action === 'obtener_tarea') {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $db_conn->prepare("SELECT * FROM presupuesto_tareas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $tarea = $res ? $res->fetch_assoc() : null;

        if ($tarea) {
            $stmt_det = $db_conn->prepare("SELECT * FROM presupuesto_tarea_detalles WHERE tarea_id = ? ORDER BY id ASC");
            $stmt_det->bind_param("i", $id);
            $stmt_det->execute();
            $res_det = $stmt_det->get_result();
            $detalles = [];
            while ($d = $res_det->fetch_assoc()) {
                $detalles[] = $d;
            }
            $tarea['detalles'] = $detalles;
            echo json_encode(['success' => true, 'tarea' => $tarea]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tarea no encontrada.']);
        }
        exit;
    }

    // 4. Eliminar Tarea
    if ($action === 'eliminar_tarea' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db_conn->prepare("DELETE FROM presupuesto_tareas WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $stmt_del = $db_conn->prepare("DELETE FROM presupuesto_tarea_detalles WHERE tarea_id = ?");
            $stmt_del->bind_param("i", $id);
            $stmt_del->execute();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
        }
        exit;
    }

    // 5. Listar Tareas
    if ($action === 'listar') {
        $res = $db_conn->query("SELECT * FROM presupuesto_tareas ORDER BY id DESC");
        $tareas = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) { $tareas[] = $r; }
        }
        echo json_encode(['data' => $tareas]);
        exit;
    }
}