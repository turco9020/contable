<?php
session_start();
require_once '../../config/database.php';

$db_conn = $conn ?? $conexion ?? $db ?? null;

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

    // Listar EPP
    if ($action === 'listar_epp') {
        $data = [];
        if ($db_conn) {
            $res = $db_conn->query("SELECT * FROM presupuesto_mo_epp ORDER BY id ASC");
            if ($res) {
                $data = $res->fetch_all(MYSQLI_ASSOC);
            }
        }
        echo json_encode(['data' => $data]);
        exit;
    }

    // Guardar / Editar EPP Modal (Creación o edición de un solo ítem)
    if ($action === 'guardar_epp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio_unitario'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 1);
        $meses = intval($_POST['meses_reposicion'] ?? 6);

        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_mo_epp SET descripcion=?, precio_unitario=?, cantidad=?, meses_reposicion=? WHERE id=?");
            $stmt->bind_param("sdiii", $descripcion, $precio, $cantidad, $meses, $id);
        } else {
            $stmt = $db_conn->prepare("INSERT INTO presupuesto_mo_epp (descripcion, precio_unitario, cantidad, meses_reposicion) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdii", $descripcion, $precio, $cantidad, $meses);
        }
        $ok = $stmt ? $stmt->execute() : false;
        
        if ($ok) {
            $db_conn->query("UPDATE presupuesto_mo_auditoria SET fecha_actualizacion = NOW() WHERE id = 1");
        }
        echo json_encode(['success' => $ok]);
        exit;
    }

    // GUARDAR EPP LOTE (Edición Rápida desde Grilla)
    if ($action === 'guardar_epp_lote' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($db_conn && isset($_POST['epp']) && is_array($_POST['epp'])) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_mo_epp SET precio_unitario=?, cantidad=?, meses_reposicion=? WHERE id=?");
            foreach ($_POST['epp'] as $id => $datos) {
                $p = floatval($datos['precio_unitario'] ?? 0);
                $c = intval($datos['cantidad'] ?? 1);
                $m = intval($datos['meses_reposicion'] ?? 6);
                $id_i = intval($id);
                $stmt->bind_param("diii", $p, $c, $m, $id_i);
                $stmt->execute();
            }
            $db_conn->query("UPDATE presupuesto_mo_auditoria SET fecha_actualizacion = NOW() WHERE id = 1");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // Eliminar EPP
    if ($action === 'eliminar_epp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db_conn->prepare("DELETE FROM presupuesto_mo_epp WHERE id=?");
            $stmt->bind_param("i", $id);
            $ok = $stmt->execute();
            if ($ok) {
                $db_conn->query("UPDATE presupuesto_mo_auditoria SET fecha_actualizacion = NOW() WHERE id = 1");
            }
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // Actualizar Básico, Horas y Suma No Remunerativa por Categoría
    if ($action === 'guardar_categoria' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $valor_hora = floatval($_POST['valor_hora_basico'] ?? 0);
        $suma_no_rem = floatval($_POST['suma_no_remunerativa'] ?? 0);
        $horas_mes = intval($_POST['horas_mes'] ?? 160);

        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_mo_categorias SET valor_hora_basico=?, suma_no_remunerativa=?, horas_mes=? WHERE id=?");
            $stmt->bind_param("ddii", $valor_hora, $suma_no_rem, $horas_mes, $id);
            $ok = $stmt->execute();
            if ($ok) {
                $db_conn->query("UPDATE presupuesto_mo_auditoria SET fecha_actualizacion = NOW() WHERE id = 1");
            }
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // Actualizar Configuración / Porcentajes
    if ($action === 'guardar_configuracion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($db_conn && isset($_POST['config']) && is_array($_POST['config'])) {
            // Guarda o inserta dinámicamente si la clave no existía previamente
            $stmt = $db_conn->prepare("INSERT INTO presupuesto_mo_configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
            foreach ($_POST['config'] as $clave => $valor) {
                $val_f = floatval($valor);
                $stmt->bind_param("sd", $clave, $val_f);
                $stmt->execute();
            }
            $db_conn->query("UPDATE presupuesto_mo_auditoria SET fecha_actualizacion = NOW() WHERE id = 1");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // Adjuntar Acuerdo UOCRA
    if ($action === 'subir_acuerdo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['adjunto_uocra']) && $_FILES['adjunto_uocra']['error'] === UPLOAD_ERR_OK) {
            $dir_dest = '../../uploads/presupuestos/';
            if (!is_dir($dir_dest)) {
                mkdir($dir_dest, 0777, true);
            }

            $res_old = $db_conn->query("SELECT ruta_archivo FROM presupuesto_mo_adjuntos WHERE id = 1");
            if ($res_old && $r_old = $res_old->fetch_assoc()) {
                if (file_exists($r_old['ruta_archivo'])) {
                    unlink($r_old['ruta_archivo']);
                }
            }

            $nombre_original = basename($_FILES['adjunto_uocra']['name']);
            $ext = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_final = 'acuerdo_uocra_' . time() . '.' . $ext;
            $ruta_completa = $dir_dest . $nombre_final;

            if (move_uploaded_file($_FILES['adjunto_uocra']['tmp_name'], $ruta_completa)) {
                $stmt = $db_conn->prepare("INSERT INTO presupuesto_mo_adjuntos (id, nombre_archivo, ruta_archivo, fecha_subida) VALUES (1, ?, ?, NOW()) ON DUPLICATE KEY UPDATE nombre_archivo=?, ruta_archivo=?, fecha_subida=NOW()");
                $stmt->bind_param("ssss", $nombre_original, $ruta_completa, $nombre_original, $ruta_completa);
                $stmt->execute();
                $db_conn->query("UPDATE presupuesto_mo_auditoria SET fecha_actualizacion = NOW() WHERE id = 1");
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'error' => 'No se pudo subir el archivo.']);
        exit;
    }
}

// FECHA ÚLTIMA ACTUALIZACIÓN
$fecha_actualizacion = date('d/m/Y H:i');
if ($db_conn) {
    $res_aud = $db_conn->query("SELECT fecha_actualizacion FROM presupuesto_mo_auditoria WHERE id = 1");
    if ($res_aud && $row_aud = $res_aud->fetch_assoc()) {
        $fecha_actualizacion = date('d/m/Y H:i', strtotime($row_aud['fecha_actualizacion']));
    }
}

// ADJUNTO UOCRA
$adjunto_uocra = null;
if ($db_conn) {
    $res_adj = $db_conn->query("SELECT * FROM presupuesto_mo_adjuntos WHERE id = 1");
    if ($res_adj) {
        $adjunto_uocra = $res_adj->fetch_assoc();
    }
}

// CONFIGURACIONES Y CATEGORÍAS
$config = [];
if ($db_conn) {
    $res_cfg = $db_conn->query("SELECT clave, valor FROM presupuesto_mo_configuracion");
    if ($res_cfg) {
        while ($r = $res_cfg->fetch_assoc()) {
            $config[$r['clave']] = floatval($r['valor']);
        }
    }
}

$categorias = [];
if ($db_conn) {
    $res_cat = $db_conn->query("SELECT * FROM presupuesto_mo_categorias WHERE activo = 1 ORDER BY id ASC");
    if ($res_cat) {
        $categorias = $res_cat->fetch_all(MYSQLI_ASSOC);
    }
}

$epp_items = [];
if ($db_conn) {
    $res_epp = $db_conn->query("SELECT * FROM presupuesto_mo_epp ORDER BY id ASC");
    if ($res_epp) {
        $epp_items = $res_epp->fetch_all(MYSQLI_ASSOC);
    }
}

// CALCULOS AUXILIARES EPP, VIANDAS, PREOCUPACIONAL
$costo_epp_hora = 0;
foreach ($epp_items as $e) {
    $hs_repo = 160 * $e['meses_reposicion'];
    if ($hs_repo > 0) {
        $costo_epp_hora += ($e['precio_unitario'] * $e['cantidad']) / $hs_repo;
    }
}

$vianda_diaria = $config['vianda_diaria'] ?? 7500;
$costo_vianda_hora = $vianda_diaria / (160 / 22);

$preocupacional_unitario = $config['preocupacional_unitario'] ?? 95000;
$costo_preocup_hora = $preocupacional_unitario / (160 * 18);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-calculator-fill text-secondary me-2"></i> Cálculo Costo Mano de Obra (UOCRA)
        </h4>
        <span class="badge bg-dark fs-6" title="Fecha del último cambio realizado">
            <i class="bi bi-clock-history me-1"></i> Actualizado: <?= $fecha_actualizacion ?>
        </span>
    </div>

    <!-- Pestañas de Navegación con IDs identificables -->
    <ul class="nav nav-tabs mb-3" id="tabManoObra" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold text-dark" id="tab-resumen" data-bs-toggle="tab" data-bs-target="#panel-resumen" type="button" role="tab">
                <i class="bi bi-table me-1 text-secondary"></i> Planilla Detallada
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold text-dark" id="tab-categorias" data-bs-toggle="tab" data-bs-target="#panel-categorias" type="button" role="tab">
                <i class="bi bi-people me-1 text-secondary"></i> Básicos, Suma No Rem. y Acuerdo UOCRA
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold text-dark" id="tab-epp" data-bs-toggle="tab" data-bs-target="#panel-epp" type="button" role="tab">
                <i class="bi bi-shield-check me-1 text-secondary"></i> EPP, Viandas y Preocupacional
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold text-dark" id="tab-porcentajes" data-bs-toggle="tab" data-bs-target="#panel-porcentajes" type="button" role="tab">
                <i class="bi bi-percent me-1 text-secondary"></i> Porcentajes, Cargas y Aportes
            </button>
        </li>
    </ul>

    <div class="tab-content" id="tabManoObraContent">

        <!-- PESTAÑA 1: PLANILLA DETALLADA -->
        <div class="tab-pane fade show active" id="panel-resumen" role="tabpanel">
            <div class="card shadow-sm border-0 p-3 mb-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-cash-stack me-2"></i>Desglose Completo de Costos por Categoría</h5>
                <div class="table-responsive">
                    <table id="tablaResumenDetallada" class="table table-bordered table-striped align-middle dt-responsive nowrap w-100">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Categoría</th>
                                <th>Básico ($/hs)</th>
                                <th>Rem. Mensual</th>
                                <th>Presentismo</th>
                                <th>Rem. Bruta</th>
                                <th>Suma No Rem.</th>
                                <th>Vacaciones</th>
                                <th>Feriados</th>
                                <th>S.A.C.</th>
                                <th>Enfermedad</th>
                                <th>C.U.S.S.</th>
                                <th>Obra Social Pat.</th>
                                <th>A.R.T.</th>
                                <th>Fondo Cese</th>
                                <th>IERIC</th>
                                <th>FODECO</th>
                                <th>Capacitación</th>
                                <th>Extra/Gremio</th>
                                <th>Jubilación Obrero</th>
                                <th>PAMI Obrero</th>
                                <th>Obra Soc. Obrero</th>
                                <th>Sindical Obrero</th>
                                <th>Aporte s/ No Rem</th>
                                <th>Subtotal Cargas</th>
                                <th>Total Mensual</th>
                                <th>Costo Unit. ($/hs)</th>
                                <th>EPP ($/hs)</th>
                                <th>Vianda ($/hs)</th>
                                <th>Preocup. ($/hs)</th>
                                <th class="bg-primary text-white">COSTO HS TOTAL</th>
                                <th class="bg-success text-white">COSTO DIARIO (8 hs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($categorias as $cat): 
                                $basico_hs = $cat['valor_hora_basico'];
                                $suma_no_rem = $cat['suma_no_remunerativa'] ?? 0;
                                $rem_mensual = $basico_hs * $cat['horas_mes'];
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

                                $costo_unitario_hs = ($cat['horas_mes'] > 0) ? ($total_mensual / $cat['horas_mes']) : 0;
                                $costo_horario_final = $costo_unitario_hs + $costo_epp_hora + $costo_vianda_hora + $costo_preocup_hora;
                                $costo_diario_final = $costo_horario_final * 8;
                            ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($cat['nombre']) ?></td>
                                <td class="text-end">$ <?= number_format($basico_hs, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($rem_mensual, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($presentismo, 2, ',', '.') ?></td>
                                <td class="text-end fw-semibold">$ <?= number_format($bruto_mensual, 2, ',', '.') ?></td>
                                <td class="text-end ">$ <?= number_format($suma_no_rem, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($vacaciones, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($feriados, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($sac, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($enfermedad, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($cuss, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($obra_social, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($art, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($fondo_desempleo, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($ieric, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($fodeco, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($capacitacion, 2, ',', '.') ?></td>
                                <td class="text-end">$ <?= number_format($extra_gremio, 2, ',', '.') ?></td>
                                <td class="text-end text-secondary">$ <?= number_format($jubilacion_obrero, 2, ',', '.') ?></td>
                                <td class="text-end text-secondary">$ <?= number_format($pami_obrero, 2, ',', '.') ?></td>
                                <td class="text-end text-secondary">$ <?= number_format($os_obrero, 2, ',', '.') ?></td>
                                <td class="text-end text-secondary">$ <?= number_format($sindicato_obrero, 2, ',', '.') ?></td>
                                <td class="text-end ">$ <?= number_format($aporte_no_rem, 2, ',', '.') ?></td>
                                <td class="text-end fw-semibold">$ <?= number_format($subtotal_cargas, 2, ',', '.') ?></td>
                                <td class="text-end fw-semibold">$ <?= number_format($total_mensual, 2, ',', '.') ?></td>
                                <td class="text-end fw-semibold">$ <?= number_format($costo_unitario_hs, 2, ',', '.') ?></td>
                                <td class="text-end text-muted">$ <?= number_format($costo_epp_hora, 2, ',', '.') ?></td>
                                <td class="text-end text-muted">$ <?= number_format($costo_vianda_hora, 2, ',', '.') ?></td>
                                <td class="text-end text-muted">$ <?= number_format($costo_preocup_hora, 2, ',', '.') ?></td>
                                <td class="text-end fw-bold text-primary fs-6">$ <?= number_format($costo_horario_final, 2, ',', '.') ?></td>
                                <td class="text-end fw-bold text-success fs-6">$ <?= number_format($costo_diario_final, 2, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- SECCIÓN APARTE: ESTIMACIÓN DE INGRESOS DE BOLSILLO DEL TRABAJADOR -->
            <div class="card shadow-sm border-0 p-3 mb-4 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-wallet2 text-success me-2"></i> Estimación de Ingreso Neto Obrero (Sueldo de Bolsillo)
                    </h5>
                    <span class="badge bg-secondary fs-8">
                        Factor aplicado: <?= number_format($config['factor_bolsillo'] ?? 0.435, 3, ',', '.') ?> | Base: <?= $config['dias_quincena'] ?? 11 ?> días/quincena
                    </span>
                </div>
                <p class="small text-muted mb-3">
                    Cálculo estimado del dinero neto que percibe el trabajador según el valor del Costo Unitario/Hs (Costo Empresa sin EPP, Vianda ni Preocupacional).
                </p>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle bg-white mb-0">
                        <thead class="table-secondary text-center">
                            <tr>
                                <th class="text-start">Categoría</th>
                                <th>Valor Hora Neto ($)</th>
                                <th>Jornal Diario (8 hs) ($)</th>
                                <th>Estimado Quincena (<?= $config['dias_quincena'] ?? 11 ?> días) ($)</th>
                                <th>Estimado Mensual ($)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $factor_bolsillo = $config['factor_bolsillo'] ?? 0.435;
                            $dias_quincena = $config['dias_quincena'] ?? 11;

                            foreach ($categorias as $cat): 
                                // 1. Recalcular Costo Unitario Hs igual que en la tabla superior
                                $basico_hs = $cat['valor_hora_basico'];
                                $suma_no_rem = $cat['suma_no_remunerativa'] ?? 0;
                                $rem_mensual = $basico_hs * $cat['horas_mes'];
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
                                $costo_unitario_hs = ($cat['horas_mes'] > 0) ? ($total_mensual / $cat['horas_mes']) : 0;

                                // 2. Cálculos específicos de Bolsillo
                                $hora_bolsillo = $costo_unitario_hs * $factor_bolsillo;
                                $dia_bolsillo = $hora_bolsillo * 8;
                                $quincena_bolsillo = $dia_bolsillo * $dias_quincena;
                                $mes_bolsillo = $hora_bolsillo * $cat['horas_mes'];
                            ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($cat['nombre']) ?></td>
                                <td class="text-end text-dark">$ <?= number_format($hora_bolsillo, 2, ',', '.') ?></td>
                                <td class="text-end fw-bold text-dark">$ <?= number_format($dia_bolsillo, 2, ',', '.') ?></td>
                                <td class="text-end fw-semibold text-dark">$ <?= number_format($quincena_bolsillo, 2, ',', '.') ?></td>
                                <td class="text-end fw-semibold text-dark">$ <?= number_format($mes_bolsillo, 2, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
                    </div>

        <!-- PESTAÑA 2: CATEGORÍAS, SUMA NO REMUNERATIVA Y ACUERDO UOCRA -->
        <div class="tab-pane fade" id="panel-categorias" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0 p-3 mb-4">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pencil-square me-2"></i>Edición de Básico, Suma No Remunerativa y Hs</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>Categoría</th>
                                        <th style="width: 140px;">Hs Normales</th>
                                        <th style="width: 180px;">Valor Horario ($)</th>
                                        <th style="width: 180px;">Suma No Rem. ($)</th>
                                        <th style="width: 120px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categorias as $cat): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($cat['nombre']) ?></td>
                                        <td>
                                            <input type="number" class="form-control text-center input-horas-cat" data-id="<?= $cat['id'] ?>" value="<?= $cat['horas_mes'] ?>">
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" class="form-control text-end input-basico-cat" data-id="<?= $cat['id'] ?>" value="<?= $cat['valor_hora_basico'] ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" class="form-control text-end input-norem-cat" data-id="<?= $cat['id'] ?>" value="<?= $cat['suma_no_remunerativa'] ?? 0 ?>">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-dark btn-sm" onclick="guardarCategoria(<?= $cat['id'] ?>)">
                                                <i class="bi bi-save me-1"></i> Guardar
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- CARGA DE ARCHIVO ACUERDO UOCRA -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 p-3 mb-4">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-file-earmark-pdf me-2"></i>Acuerdo UOCRA Vigente</h5>
                        <?php if ($adjunto_uocra): ?>
                            <div class="alert alert-secondary d-flex align-items-center justify-content-between p-2 mb-3">
                                <div class="text-truncate me-2">
                                    <i class="bi bi-paperclip me-1"></i>
                                    <strong><?= htmlspecialchars($adjunto_uocra['nombre_archivo']) ?></strong>
                                    <br>
                                    <small class="text-muted">Subido: <?= date('d/m/Y', strtotime($adjunto_uocra['fecha_subida'])) ?></small>
                                </div>
                                <a href="<?= $adjunto_uocra['ruta_archivo'] ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver archivo">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning p-2 mb-3 text-center">
                                <small>No hay acuerdo adjunto cargado.</small>
                            </div>
                        <?php endif; ?>

                        <form id="formAdjuntoUocra" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Cargar/Reemplazar PDF de escala</label>
                                <input type="file" name="adjunto_uocra" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">
                                <i class="bi bi-upload me-1"></i> Subir Acuerdo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 3: EPP (EDITABLE DIRECTO), VIANDAS Y PREOCUPACIONAL -->
        <div class="tab-pane fade" id="panel-epp" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-shield-check me-2"></i>Elementos de Protección Personal (EPP)</h5>
                            <button class="btn btn-sm btn-outline-dark" onclick="abrirModalEPP('NUEVO')">
                                <i class="bi bi-plus-circle me-1"></i> Nuevo Ítem
                            </button>
                        </div>
                        
                        <!-- Formulario de Edición Directa de EPPs -->
                        <form id="formEPPLote">
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-dark text-center">
                                        <tr>
                                            <th>Descripción</th>
                                            <th style="width: 170px;">Precio Unit. ($)</th>
                                            <th style="width: 100px;">Cantidad</th>
                                            <th style="width: 120px;">Meses Repo.</th>
                                            <th style="width: 130px;">Costo/Hs ($)</th>
                                            <th style="width: 80px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($epp_items as $epp): 
                                            $total_p = $epp['precio_unitario'] * $epp['cantidad'];
                                            $hs_repo = $epp['meses_reposicion'] * 160;
                                            $costo_h = $hs_repo > 0 ? ($total_p / $hs_repo) : 0;
                                        ?>
                                        <tr>
                                            <td class="fw-semibold"><?= htmlspecialchars($epp['descripcion']) ?></td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" name="epp[<?= $epp['id'] ?>][precio_unitario]" class="form-control text-end" value="<?= $epp['precio_unitario'] ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" name="epp[<?= $epp['id'] ?>][cantidad]" class="form-control form-control-sm text-center" value="<?= $epp['cantidad'] ?>">
                                            </td>
                                            <td>
                                                <input type="number" name="epp[<?= $epp['id'] ?>][meses_reposicion]" class="form-control form-control-sm text-center" value="<?= $epp['meses_reposicion'] ?>">
                                            </td>
                                            <td class="text-end fw-bold text-dark">
                                                $ <?= number_format($costo_h, 2, ',', '.') ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar Ítem" onclick="eliminarEPP(<?= $epp['id'] ?>)">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-save me-1"></i> Guardar Cambios EPP
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 p-3 mb-3">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-cup-hot me-2"></i>Vianda Diaria</h5>
                        <form id="formVianda">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Valor Vianda en Obra ($/día)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="config[vianda_diaria]" class="form-control text-end" value="<?= $config['vianda_diaria'] ?? 7500 ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-save me-1"></i> Actualizar Vianda</button>
                        </form>
                    </div>

                    <div class="card shadow-sm border-0 p-3">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-heart-pulse me-2"></i>Examen Preocupacional</h5>
                        <form id="formPreocupacional">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Costo Examen Unitario ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="config[preocupacional_unitario]" class="form-control text-end" value="<?= $config['preocupacional_unitario'] ?? 95000 ?>">
                                </div>
                                <small class="text-muted">Amortización estimada a 18 meses (2880 hs de trabajo).</small>
                            </div>
                            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-save me-1"></i> Actualizar Preocupacional</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 4: PORCENTAJES Y CARGAS ORGANIZADOS -->
        <div class="tab-pane fade" id="panel-porcentajes" role="tabpanel">
        <!-- GUÍA APB: EXPLICACIÓN DE FORMATO DECIMAL -->
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
            <div>
                <h6 class="fw-bold mb-1">¿Cómo ingresar los valores de Porcentaje?</h6>
                <span class="small">
                    Los valores se deben ingresar en **formato decimal** (Porcentaje / 100), ya que el sistema los multiplica directamente en la fórmula. 
                    <br>
                    <strong>Ejemplos:</strong> 
                    <span class="badge bg-white text-dark border ms-1">20% &rArr; <strong>0.20</strong></span>
                    <span class="badge bg-white text-dark border ms-1">7% &rArr; <strong>0.07</strong></span>
                    <span class="badge bg-white text-dark border ms-1">2.5% &rArr; <strong>0.025</strong></span>
                    <span class="badge bg-white text-dark border ms-1">1% &rArr; <strong>0.01</strong></span>
                </span>
            </div>
        </div>    
            <form id="formPorcentajes">
                <div class="row g-3">
                    
                    <!-- BLOQUE 1: INCIDENCIAS ADICIONALES -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 p-3 h-100">
                            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-person-badge me-1"></i> Incidencias Adicionales</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Presentismo (% s/ Básico)</label>
                                <input type="number" step="0.0001" name="config[presentismo]" class="form-control text-end" value="<?= $config['presentismo'] ?? 0.20 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Feriados Anuales (Días)</label>
                                <input type="number" step="1" name="config[feriados_anuales]" class="form-control text-end" value="<?= $config['feriados_anuales'] ?? 16 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Incidencia Enfermedades (%)</label>
                                <input type="number" step="0.0001" name="config[incidencia_enfermedad]" class="form-control text-end" value="<?= $config['incidencia_enfermedad'] ?? 0.025 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold ">Aporte s/ Suma No Rem. (%)</label>
                                <input type="number" step="0.0001" name="config[aporte_no_remunerativo]" class="form-control text-end " value="<?= $config['aporte_no_remunerativo'] ?? 0.0700 ?>">
                            </div>
                        </div>
                    </div>

                    <!-- BLOQUE 2: CARGAS SOCIALES PATRONALES -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 p-3 h-100">
                            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-building me-1"></i> Cargas Patronales</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">C.U.S.S. (%)</label>
                                <input type="number" step="0.0001" name="config[cuss]" class="form-control text-end" value="<?= $config['cuss'] ?? 0.17 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Obra Social Patronal (%)</label>
                                <input type="number" step="0.0001" name="config[obra_social]" class="form-control text-end" value="<?= $config['obra_social'] ?? 0.06 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">A.R.T. (%)</label>
                                <input type="number" step="0.0001" name="config[art]" class="form-control text-end" value="<?= $config['art'] ?? 0.07 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Fondo Cese / Desempleo (%)</label>
                                <input type="number" step="0.0001" name="config[fondo_desempleo]" class="form-control text-end" value="<?= $config['fondo_desempleo'] ?? 0.12 ?>">
                            </div>
                        </div>
                    </div>

                    <!-- BLOQUE 3: APORTES DEL OBRERO -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 p-3 h-100">
                            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-person-lines-fill me-1"></i> Aportes del Obrero</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jubilación (%)</label>
                                <input type="number" step="0.0001" name="config[jubilacion_obrero]" class="form-control text-end" value="<?= $config['jubilacion_obrero'] ?? 0.1100 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">PAMI / Ley 19032 (%)</label>
                                <input type="number" step="0.0001" name="config[pami_obrero]" class="form-control text-end" value="<?= $config['pami_obrero'] ?? 0.0300 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Obra Social Obrero (%)</label>
                                <input type="number" step="0.0001" name="config[obra_social_obrero]" class="form-control text-end" value="<?= $config['obra_social_obrero'] ?? 0.0300 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Aporte Sindical (%)</label>
                                <input type="number" step="0.0001" name="config[sindicato_obrero]" class="form-control text-end" value="<?= $config['sindicato_obrero'] ?? 0.0250 ?>">
                            </div>
                        </div>
                    </div>

                    <!-- BLOQUE 4: FONDOS UOCRA / OTROS -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 p-3 h-100">
                            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-journal-plus me-1"></i> Fondos UOCRA / Extra</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">IERIC (% s/ F. Desempleo)</label>
                                <input type="number" step="0.0001" name="config[ieric]" class="form-control text-end" value="<?= $config['ieric'] ?? 0.02 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">FODECO (% s/ F. Desempleo)</label>
                                <input type="number" step="0.0001" name="config[fodeco]" class="form-control text-end" value="<?= $config['fodeco'] ?? 0.01 ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Capacitación (% s/ F. Desempleo)</label>
                                <input type="number" step="0.0001" name="config[capacitacion]" class="form-control text-end" value="<?= $config['capacitacion'] ?? 0.01 ?>">
                            </div>
                            <div class="mb-3 bg-light p-2 border rounded">
                                <label class="form-label fw-semibold">Extra / Ayuda Gremio (%)</label>
                                <input type="number" step="0.0001" name="config[extra_gremio]" class="form-control text-end" value="<?= $config['extra_gremio'] ?? 0.0000 ?>">
                            </div>
                        </div>
                    </div>
                    <!-- BLOQUE 5: PARÁMETROS BOLSILLO -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 p-3 h-100">
                            <h6 class="fw-bold text-dark border-bottom pb-2">
                                <i class="bi bi-wallet2 me-1"></i> Parámetros Bolsillo
                            </h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Factor Bolsillo / Empresa</label>
                                <input type="number" step="0.0001" name="config[factor_bolsillo]" class="form-control text-end" value="<?= $config['factor_bolsillo'] ?? 0.4350 ?>">
                                <small class="text-muted">Proporción aproximada respecto al Costo Unitario/Hs.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Días por Quincena</label>
                                <input type="number" step="1" name="config[dias_quincena]" class="form-control text-end" value="<?= $config['dias_quincena'] ?? 11 ?>">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-dark btn-lg"><i class="bi bi-save me-1"></i> Guardar Parámetros y Porcentajes</button>
                </div>
            </form>
        </div>

    </div>

</div>

<!-- MODAL CREAR NUEVO EPP -->
<div class="modal fade" id="modalEPP" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalEPPLabel"><i class="bi bi-shield-check me-2"></i>Nuevo Elemento EPP</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEPPItem">
                <div class="modal-body bg-light">
                    <input type="hidden" name="id" id="epp_id" value="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción *</label>
                        <input name="descripcion" id="epp_descripcion" class="form-control" required placeholder="Ej: Zapato de seguridad">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Precio Unitario (s/IVA) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="precio_unitario" id="epp_precio" class="form-control text-end" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cantidad *</label>
                            <input type="number" name="cantidad" id="epp_cantidad" class="form-control" required value="1">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Meses de Reposición *</label>
                            <input type="number" name="meses_reposicion" id="epp_meses" class="form-control" required value="6">
                            <small class="text-muted">Generalmente 6 meses (960 hs efectivas).</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark">Guardar Nuevo EPP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalEPPBS;
let tablaResumenDetallada;

document.addEventListener("DOMContentLoaded", function(){
    modalEPPBS = new bootstrap.Modal(document.getElementById('modalEPP'));

    // --- MANEJO DE RETENCIÓN DE PESTAÑA ACTIVA (LocalStorage) ---
    const activeTab = localStorage.getItem('activeTab_manoObra');
    if (activeTab) {
        const tabToTrigger = document.querySelector(`button[data-bs-target="${activeTab}"]`);
        if (tabToTrigger) {
            const tabInstance = new bootstrap.Tab(tabToTrigger);
            tabInstance.show();
        }
    }

    // Escuchar cambios de pestaña y guardar en LocalStorage
    const tabElements = document.querySelectorAll('#tabManoObra button[data-bs-toggle="tab"]');
    tabElements.forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            const targetPane = event.target.getAttribute('data-bs-target');
            localStorage.setItem('activeTab_manoObra', targetPane);
        });
    });

    // Inicializar DataTable Resumen
    tablaResumenDetallada = $('#tablaResumenDetallada').DataTable({
        dom: '<"d-flex justify-content-between align-items-center mb-3"B>t',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel',
                className: 'btn btn-success btn-sm me-1',
                title: 'Desglose_Costo_Mano_de_Obra_UOCRA'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> Exportar a PDF',
                className: 'btn btn-danger btn-sm me-1',
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Desglose Costo Mano de Obra UOCRA'
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Imprimir',
                className: 'btn btn-secondary btn-sm',
                title: 'Desglose Costo Mano de Obra UOCRA'
            }
        ],
        responsive: {
            details: {
                type: 'column',
                target: 0
            }
        },
        columnDefs: [
            { className: 'dtr-control', orderable: false, targets: 0 },
            //Targets hace referencia a la posición de la columna (0 = primera columna, 1 = segunda columna, etc.)
            // Colocamos prioridad a las columnas clave para que SIEMPRE se vean a simple vista (Priority 1 y 2):
            { responsivePriority: 1, targets: 0 },  // Categoría
            { responsivePriority: 1, targets: 1 },  // Básico ($/hs)
            { responsivePriority: 7, targets: 2 }, // Rem.Mensual
            { responsivePriority: 4, targets: 4 },  // Rem. Bruta
            { responsivePriority: 4, targets: 13 },  // Fondo Cese
            { responsivePriority: 5, targets: 23 }, // Subtotal Cargas
            { responsivePriority: 3, targets: 24 }, // Total Mensual
            { responsivePriority: 3, targets: 25 }, // Costo Unit. ($/hs)                                 
            { responsivePriority: 2, targets: -1 }, // COSTO DIARIO (Última columna)
            { responsivePriority: 2, targets: -2 }, // COSTO DIARIO (Penultima columna)           
            // Las demás columnas tomarán un orden secundario y se desplegarán al tocar el (+)
        ],
        paging: false,
        searching: false,
        info: false,
        ordering: false
    });

    // Modal EPP (Creación de un nuevo ítem)
    $('#formEPPItem').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: 'mano_obra.php?action=guardar_epp',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    modalEPPBS.hide();
                    location.reload();
                } else {
                    alert('Error al guardar el elemento EPP.');
                }
            }
        });
    });

    // Edición Lote de EPP desde la tabla
    $('#formEPPLote').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: 'mano_obra.php?action=guardar_epp_lote',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    location.reload();
                } else {
                    alert('Error al guardar los cambios en EPP.');
                }
            }
        });
    });

    // Formularios de Configuración
    $('#formVianda, #formPreocupacional, #formPorcentajes').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: 'mano_obra.php?action=guardar_configuracion',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    location.reload();
                } else {
                    alert('Error al guardar la configuración.');
                }
            }
        });
    });

    // Adjunto PDF UOCRA
    $('#formAdjuntoUocra').submit(function(e){
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: 'mano_obra.php?action=subir_acuerdo',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    location.reload();
                } else {
                    alert(res.error || 'Error al subir el acuerdo.');
                }
            }
        });
    });
});

function guardarCategoria(id) {
    let valor = $(`.input-basico-cat[data-id="${id}"]`).val();
    let norem = $(`.input-norem-cat[data-id="${id}"]`).val();
    let horas = $(`.input-horas-cat[data-id="${id}"]`).val();
    
    $.post('mano_obra.php?action=guardar_categoria', { 
        id: id, 
        valor_hora_basico: valor, 
        suma_no_remunerativa: norem, 
        horas_mes: horas 
    }, function(res){
        if(res.success) {
            location.reload();
        } else {
            alert('Error al guardar los datos de la categoría.');
        }
    }, 'json');
}

function abrirModalEPP() {
    $('#formEPPItem')[0].reset();
    $('#epp_id').val('');
    modalEPPBS.show();
}

window.eliminarEPP = function(id) {
    Swal.fire({
        title: '¿Eliminar EPP?',
        text: 'El registro se desactivará del sistema.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('mano_obra.php?action=eliminar_epp', { id: id }, function(res) {
                if (res.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El EPP ha sido eliminado correctamente.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    location.reload();
                } else {
                    Swal.fire('Error', 'No se pudo eliminar el EPP.', 'error');
                }
            }, 'json');
        }
    });
}
</script>

<?php include '../../includes/footer.php'; ?>