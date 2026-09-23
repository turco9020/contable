<?php
// C:\xampp\htdocs\contable\modules\presupuestos\indirectos.php
session_start();
require_once '../../config/database.php';

$db_conn = $conn ?? $conexion ?? $db ?? null;

// DEFINICIONES CORE
$unidades_disponibles = ['GL', 'MES', 'DÍA', 'HS', 'UN', 'KM', 'M2', 'M3'];
$afectaciones_disponibles = [
    '0.00' => '0%',
    '0.25' => '25%',
    '0.50' => '50%',
    '0.75' => '75%',
    '1.00' => '100%'
];

// OBTENER CONFIGURACIÓN GLOBAL
$cant_operarios = 2;
$dias_obra = 30;
if ($db_conn) {
    $res_conf = $db_conn->query("SELECT clave, valor FROM presupuesto_indirectos_config");
    if ($res_conf) {
        while ($c = $res_conf->fetch_assoc()) {
            if ($c['clave'] === 'cant_operarios') $cant_operarios = intval($c['valor']);
            if ($c['clave'] === 'dias_obra') $dias_obra = intval($c['valor']);
        }
    }
}

// PROCESAMIENTO AJAX
if (isset($_GET['action'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    // 1. Guardar Variables Globales
    if ($action === 'guardar_config_global' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $operarios = intval($_POST['cant_operarios'] ?? 2);
        $dias = intval($_POST['dias_obra'] ?? 30);

        $stmt1 = $db_conn->prepare("INSERT INTO presupuesto_indirectos_config (clave, valor) VALUES ('cant_operarios', ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt1->bind_param("ss", $operarios, $operarios);
        $stmt1->execute();

        $stmt2 = $db_conn->prepare("INSERT INTO presupuesto_indirectos_config (clave, valor) VALUES ('dias_obra', ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt2->bind_param("ss", $dias, $dias);
        $stmt2->execute();

        echo json_encode(['success' => true]);
        exit;
    }

    // 2. Guardar/Editar Categoría
    if ($action === 'guardar_categoria' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['cat_id'] ?? 0);
        $codigo = trim($_POST['cat_codigo'] ?? '');
        $nombre = trim($_POST['cat_nombre'] ?? '');

        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_indirectos_categorias SET codigo = ?, nombre = ? WHERE id = ?");
            $stmt->bind_param("ssi", $codigo, $nombre, $id);
        } else {
            $stmt = $db_conn->prepare("INSERT INTO presupuesto_indirectos_categorias (codigo, nombre) VALUES (?, ?)");
            $stmt->bind_param("ss", $codigo, $nombre);
        }
        $ok = $stmt ? $stmt->execute() : false;
        echo json_encode(['success' => $ok]);
        exit;
    }

    // 3. Eliminar Categoría
    if ($action === 'eliminar_categoria' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db_conn->prepare("DELETE FROM presupuesto_indirectos_categorias WHERE id = ?");
            $stmt->bind_param("i", $id);
            $ok = $stmt->execute();
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // 4. Cambiar Estado (Activo / Inactivo)
    if ($action === 'toggle_estado_item' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $estado = intval($_POST['estado'] ?? 1);
        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_indirectos_plantilla SET activo = ? WHERE id = ?");
            $stmt->bind_param("ii", $estado, $id);
            $ok = $stmt->execute();
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // 5. Eliminar Ítem Individual
    if ($action === 'eliminar_item' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db_conn->prepare("DELETE FROM presupuesto_indirectos_plantilla WHERE id = ?");
            $stmt->bind_param("i", $id);
            $ok = $stmt->execute();
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // 6. Guardar / Editar Ítem Individual (Modal)
    if ($action === 'guardar_item' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $cat_id = intval($_POST['categoria_id'] ?? 0);
        
        $cat_codigo = '';
        $cat_nombre = '';
        if ($cat_id > 0) {
            $res_c = $db_conn->query("SELECT codigo, nombre FROM presupuesto_indirectos_categorias WHERE id = $cat_id");
            if ($res_c && $r_c = $res_c->fetch_assoc()) {
                $cat_codigo = $r_c['codigo'];
                $cat_nombre = $r_c['nombre'];
            }
        }

        $item_codigo = trim($_POST['item_codigo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $observacion = trim($_POST['observacion'] ?? '');
        $cantidad = floatval($_POST['cantidad_defecto'] ?? 1);
        $unidad = trim($_POST['unidad'] ?? 'GL');
        $depende_operarios = isset($_POST['depende_operarios']) ? 1 : 0;
        $depende_tiempo = isset($_POST['depende_tiempo']) ? 1 : 0;
        $afectacion = floatval($_POST['afectacion_defecto'] ?? 1);
        
        // Limpiar formato argentino en caso de recibir coma decimal o puntos de miles
        $unitario_raw = str_replace('.', '', $_POST['unitario_defecto'] ?? '0');
        $unitario_raw = str_replace(',', '.', $unitario_raw);
        $unitario = floatval($unitario_raw);
        
        $activo = intval($_POST['activo'] ?? 1);

        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_indirectos_plantilla SET categoria_codigo=?, categoria_nombre=?, item_codigo=?, descripcion=?, observacion=?, cantidad_defecto=?, unidad=?, depende_operarios=?, depende_tiempo=?, afectacion_defecto=?, unitario_defecto=?, activo=? WHERE id=?");
            $stmt->bind_param("sssssdsiidiii", $cat_codigo, $cat_nombre, $item_codigo, $descripcion, $observacion, $cantidad, $unidad, $depende_operarios, $depende_tiempo, $afectacion, $unitario, $activo, $id);
        } else {
            $stmt = $db_conn->prepare("INSERT INTO presupuesto_indirectos_plantilla (categoria_codigo, categoria_nombre, item_codigo, descripcion, observacion, cantidad_defecto, unidad, depende_operarios, depende_tiempo, afectacion_defecto, unitario_defecto, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdsiidii", $cat_codigo, $cat_nombre, $item_codigo, $descripcion, $observacion, $cantidad, $unidad, $depende_operarios, $depende_tiempo, $afectacion, $unitario, $activo);
        }
        
        $ok = $stmt ? $stmt->execute() : false;
        echo json_encode(['success' => $ok]);
        exit;
    }

    // 7. Guardar Cambios en Lote (Edición Rápida)
    if ($action === 'guardar_indirectos_lote' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($db_conn && isset($_POST['items']) && is_array($_POST['items'])) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_indirectos_plantilla SET afectacion_defecto = ?, cantidad_defecto = ?, unidad = ?, unitario_defecto = ? WHERE id = ?");
            foreach ($_POST['items'] as $id => $valores) {
                $afec = floatval($valores['afectacion'] ?? 0);
                $cant = floatval($valores['cantidad'] ?? 0);
                $unid = trim($valores['unidad'] ?? 'GL');
                
                // Sanitizar formato numérico para base de datos
                $unit_raw = str_replace('.', '', $valores['unitario'] ?? '0');
                $unit_raw = str_replace(',', '.', $unit_raw);
                $unit = floatval($unit_raw);

                $id_item = intval($id);
                $stmt->bind_param("ddsdi", $afec, $cant, $unid, $unit, $id_item);
                $stmt->execute();
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se recibieron datos.']);
        }
        exit;
    }
}

// OBTENER FECHA DE ÚLTIMA ACTUALIZACIÓN
$fecha_ultima_act = 'Sin registros';
if ($db_conn) {
    $res_fecha = $db_conn->query("SELECT MAX(updated_at) AS ultima_fecha FROM presupuesto_indirectos_plantilla");
    if ($res_fecha && $row_f = $res_fecha->fetch_assoc()) {
        if (!empty($row_f['ultima_fecha'])) {
            $fecha_ultima_act = date("d/m/Y H:i", strtotime($row_f['ultima_fecha']));
        }
    }
}

// OBTENER LISTADO DE CATEGORÍAS
$categorias_lista = [];
if ($db_conn) {
    $res_cat = $db_conn->query("SELECT * FROM presupuesto_indirectos_categorias ORDER BY codigo ASC");
    if ($res_cat) {
        while ($c = $res_cat->fetch_assoc()) {
            $categorias_lista[] = $c;
        }
    }
}

// CONSULTA Y AGRUPACIÓN DE TODOS LOS ÍTEMS POR CATEGORÍA
$indirectos_por_cat = [];
$total_plantilla_general = 0;

if ($db_conn) {
    $query = "SELECT * FROM presupuesto_indirectos_plantilla ORDER BY categoria_codigo ASC, id ASC";
    $result = $db_conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cat_key = $row['categoria_codigo'] . ' - ' . $row['categoria_nombre'];
            $indirectos_por_cat[$cat_key][] = $row;
            if ($row['activo'] == 1) {
                $mult_op = ($row['depende_operarios'] == 1) ? $cant_operarios : 1;
                $mult_dias = ($row['depende_tiempo'] == 1) ? $dias_obra : 1;
                $total_plantilla_general += ($row['cantidad_defecto'] * $row['afectacion_defecto'] * $row['unitario_defecto'] * $mult_dias * $mult_op);
            }
        }
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<style>
/* Ocultar flechas (spinners) en inputs numéricos por defecto */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
input[type=number] {
    -moz-appearance: textfield;
}

/* Mostrar flechas solo cuando el modo edición está activo */
body.modo-edicion-activo input[type=number]::-webkit-inner-spin-button,
body.modo-edicion-activo input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: inner-spin-button !important;
    opacity: 1 !important;
}
body.modo-edicion-activo input[type=number] {
    -moz-appearance: number-input !important;
}

.input-group-sm .input-group-text {
    padding-left: 0.35rem;
    padding-right: 0.35rem;
    font-weight: 600;
}

/* Reducir el alto del encabezado del acordeón */
.accordion-button {
    padding-top: 0.35rem;
    padding-bottom: 0.35rem;
}

/* Reducir la separación entre cada ítem del acordeón */
.accordion-item {
    margin-bottom: 3px !important;
}

/* Opcional: Reducir el padding interno del contenido desplegado */
.accordion-body {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>

<div class="content">

    <!-- CABECERA DE MÓDULO -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-diagram-3-fill text-secondary me-2"></i> Gestión de Gastos Indirectos (Plantilla Base)
        </h4>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 shadow-sm fs-6 fw-normal">
                <i class="bi bi-clock-history me-1 text-primary"></i> <strong>Última mod.:</strong> <?= $fecha_ultima_act ?> hs
            </span>
            <button class="btn btn-outline-warning text-dark fw-semibold d-flex align-items-center" id="btnModoEdicion" onclick="toggleModoEdicion()">
                <i class="bi bi-pencil-square me-1"></i> Edición Rápida
            </button>
            <button class="btn btn-success d-none align-items-center" id="btnGuardarRapido" onclick="guardarEdicionRapida()">
                <i class="bi bi-check-lg me-1"></i> Guardar Cambios
            </button>
            <button class="btn btn-dark" onclick="abrirModalItem('NUEVO')">
                <i class="bi bi-plus-circle me-1"></i> Agregar Nuevo Ítem
            </button>
        </div>
    </div>

    <!-- PANEL DE VARIABLES GLOBALES -->
    <div class="card bg-secondary bg-opacity-10 border border-dark border-opacity-50 shadow-sm mb-4">
        <div class="card-body p-3">
            <form id="formConfigGlobal" class="row g-3 align-items-center">
                <div class="col-auto">
                    <span class="fw-bold text-dark"><i class="bi bi-gear-fill me-1"></i> Variables Globales:</span>
                </div>
                <div class="col-auto d-flex align-items-center gap-2">
                    <label for="cant_operarios" class="form-label mb-0 fw-semibold">Operarios:</label>
                    <input type="number" min="1" class="form-control border-secondary form-control-sm text-center" style="width: 80px;" id="cant_operarios" name="cant_operarios" value="<?= $cant_operarios ?>" required>
                </div>
                <div class="col-auto d-flex align-items-center gap-2">
                    <label for="dias_obra" class="form-label mb-0 fw-semibold">Días de Obra:</label>
                    <input type="number" min="1" class="form-control border-secondary form-control-sm text-center" style="width: 80px;" id="dias_obra" name="dias_obra" value="<?= $dias_obra ?>" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-check-circle me-1"></i> Aplicar Parámetros
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- PESTAÑAS -->
    <ul class="nav nav-tabs mb-3" id="tabIndirectos" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold text-dark" id="tab-plantilla" data-bs-toggle="tab" data-bs-target="#panel-plantilla" type="button" role="tab">
                <i class="bi bi-journal-text me-1 text-secondary"></i> Detalle Default
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold text-dark" id="tab-categorias" data-bs-toggle="tab" data-bs-target="#panel-categorias" type="button" role="tab">
                <i class="bi bi-tags-fill me-1 text-secondary"></i> Categorías
            </button>
        </li>
    </ul>

    <div class="tab-content" id="tabIndirectosContent">
        
        <!-- PESTAÑA 1: DETALLE DEFAULT -->
        <div class="tab-pane fade show active" id="panel-plantilla" role="tabpanel">
            <form id="formIndirectosLote">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        Total Estimado Plantilla (Activos): <span class="text-secondary fs-5 ms-2" id="totalGeneralBanner">$ <?= number_format($total_plantilla_general, 2, ',', '.') ?></span>
                    </h5>
                </div>

                <!-- ACORDEÓN POR CATEGORÍA -->
                <div class="accordion mb-4" id="acordeonIndirectos">
                    <?php 
                    if (empty($indirectos_por_cat)): 
                    ?>
                        <div class="alert alert-warning text-center p-4">
                            No existen ítems en la plantilla base. Haz clic en <strong>"Agregar Nuevo Ítem"</strong> para comenzar.
                        </div>
                    <?php 
                    else:
                        $index_cat = 0;
                        foreach ($indirectos_por_cat as $cat_nombre => $items): 
                            $index_cat++;
                            $acc_id = "cat_collapse_" . $index_cat;
                            $subtotal_cat = 0;
                            foreach ($items as $it) {
                                if ($it['activo'] == 1) {
                                    $m_op = ($it['depende_operarios'] == 1) ? $cant_operarios : 1;
                                    $m_dias = ($it['depende_tiempo'] == 1) ? $dias_obra : 1;
                                    $subtotal_cat += ($it['cantidad_defecto'] * $it['afectacion_defecto'] * $it['unitario_defecto'] * $m_dias * $m_op);
                                }
                            }
                    ?>
                        <div class="accordion-item shadow-sm mb-3 border">
                            <h2 class="accordion-header" id="heading_<?= $acc_id ?>">
                                <button class="accordion-button collapsed bg-light text-dark fw-bold w-100" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $acc_id ?>" aria-expanded="false">
                                    <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                        <span class="d-flex align-items-center">
                                            <i class="bi bi-folder2-open text-dark me-2 fs-5"></i>
                                            <span class="fs-6"><?= htmlspecialchars($cat_nombre) ?></span>
                                        </span>
                                        <span class="badge bg-light text-dark border px-3 py-2 shadow-sm">
                                            Subtotal (Activos): <strong>$ <span class="subtotal-cat-val"><?= number_format($subtotal_cat, 2, ',', '.') ?></span></strong>
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            <div id="<?= $acc_id ?>" class="accordion-collapse collapse" data-bs-parent="#acordeonIndirectos">
                                <div class="accordion-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle mb-0 tabla-cat-indirectos">
                                            <thead class="table-dark text-center">
                                                <tr>
                                                    <th style="width: 50px;">Estado</th>
                                                    <th style="width: 65px;">Código</th>
                                                    <th>Descripción</th>
                                                    <th>Observación</th>
                                                    <th style="width: 85px;">Afectación</th>
                                                    <th style="width: 95px;">Cantidad</th>
                                                    <th style="width: 85px;">Unidad</th>
                                                    <th style="width: 150px;">Unitario ($)</th>
                                                    <th style="width: 140px;">Importe Total</th>
                                                    <th style="width: 90px;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($items as $item): 
                                                    $is_active = ($item['activo'] == 1);
                                                    $dep_op = intval($item['depende_operarios'] ?? 0);
                                                    $dep_tm = intval($item['depende_tiempo'] ?? 1);
                                                    $mult_op = ($dep_op === 1) ? $cant_operarios : 1;
                                                    $mult_dias = ($dep_tm === 1) ? $dias_obra : 1;
                                                    $unitario_val = floatval($item['unitario_defecto']);
                                                    $importe = $item['cantidad_defecto'] * $item['afectacion_defecto'] * $unitario_val * $mult_dias * $mult_op;
                                                    $val_afectacion = number_format((float)$item['afectacion_defecto'], 2, '.', '');
                                                ?>
                                                <tr data-id="<?= $item['id'] ?>" data-depende-operarios="<?= $dep_op ?>" data-depende-tiempo="<?= $dep_tm ?>" class="<?= !$is_active ? 'table-secondary text-muted opacity-75' : '' ?>">
                                                    <!-- 1. ESTADO -->
                                                    <td class="text-center">
                                                        <div class="form-check form-switch d-inline-block">
                                                            <input class="form-check-input switch-estado" type="checkbox" role="switch" 
                                                                   data-id="<?= $item['id'] ?>" <?= $is_active ? 'checked' : '' ?> title="Prender / Apagar ítem">
                                                        </div>
                                                    </td>
                                                    <!-- 2. CÓDIGO -->
                                                    <td class="text-center fw-semibold"><?= htmlspecialchars($item['item_codigo'] ?? '-') ?></td>
                                                    <!-- 3. DESCRIPCIÓN -->
                                                    <td>
                                                        <?= htmlspecialchars($item['descripcion']) ?>
                                                        <?php if ($dep_op === 1): ?>
                                                            <span class="badge bg-warning text-dark ms-1" title="Multiplica por Cantidad de Operarios"><i class="bi bi-person-fill"></i> Op</span>
                                                        <?php endif; ?>
                                                        <?php if ($dep_tm === 1): ?>
                                                            <span class="badge bg-secondary text-light ms-1" title="Multiplica por Días de Obra"><i class="bi bi-calendar-event"></i> Tiempo</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <!-- 4. OBSERVACIÓN -->
                                                    <td><small><?= htmlspecialchars($item['observacion'] ?? '') ?></small></td>
                                                    <!-- 5. AFECTACIÓN -->
                                                    <td>
                                                        <select class="form-select form-select-sm input-afec px-1" name="items[<?= $item['id'] ?>][afectacion]" disabled>
                                                            <?php foreach ($afectaciones_disponibles as $val => $lbl): ?>
                                                                <option value="<?= $val ?>" <?= ($val_afectacion === $val) ? 'selected' : '' ?>><?= $lbl ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <!-- 6. CANTIDAD -->
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control form-control-sm text-end input-cant" 
                                                               name="items[<?= $item['id'] ?>][cantidad]" value="<?= number_format((float)$item['cantidad_defecto'], 2, '.', '') ?>" disabled>
                                                    </td>
                                                    <!-- 7. UNIDAD -->
                                                    <td>
                                                        <select class="form-select form-select-sm select-unidad px-1" name="items[<?= $item['id'] ?>][unidad]" disabled>
                                                            <?php foreach ($unidades_disponibles as $u): ?>
                                                                <option value="<?= $u ?>" <?= ($item['unidad'] === $u) ? 'selected' : '' ?>><?= $u ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <!-- 8. UNITARIO FORMATO ARGENTINO -->
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">$</span>
                                                            <input type="text" class="form-control form-control-sm text-end input-unit" 
                                                                   name="items[<?= $item['id'] ?>][unitario]" 
                                                                   value="<?= number_format($unitario_val, 2, ',', '.') ?>" disabled>
                                                        </div>
                                                    </td>
                                                    <!-- 9. IMPORTE TOTAL -->
                                                    <td class="text-end fw-semibold cell-importe">
                                                        $ <?= number_format($importe, 2, ',', '.') ?>
                                                    </td>
                                                    <!-- 10. ACCIONES -->
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-primary" title="Editar Completo" 
                                                                    onclick='editarItem(<?= json_encode($item) ?>)'>
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger" title="Eliminar Ítem" 
                                                                    onclick='eliminarItem(<?= $item['id'] ?>)'>
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach; 
                    endif;
                    ?>
                </div>
            </form>
        </div>

        <!-- PESTAÑA 2: CATEGORÍAS -->
        <div class="tab-pane fade" id="panel-categorias" role="tabpanel">
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-dark text-white fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> <span id="tituloFormCat">Agregar Nueva Categoría</span>
                        </div>
                        <div class="card-body bg-light">
                            <form id="formCategoria">
                                <input type="hidden" name="cat_id" id="cat_id" value="">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Código de Categoría *</label>
                                    <input type="text" name="cat_codigo" id="cat_codigo" class="form-control" required placeholder="Ej: G, 07, OBR">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nombre de Categoría *</label>
                                    <input type="text" name="cat_nombre" id="cat_nombre" class="form-control" required placeholder="Ej: SEGUROS Y GARANTÍAS">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-dark w-100">
                                        <i class="bi bi-save me-1"></i> Guardar Categoría
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary d-none" id="btnCancelarEditCat" onclick="limpiarFormCat()">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-dark text-white fw-bold">
                            <i class="bi bi-list-ul me-1"></i> Categorías Registradas
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 100px;" class="text-center">Código</th>
                                            <th>Nombre Categoría</th>
                                            <th style="width: 120px;" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($categorias_lista)): ?>
                                            <tr><td colspan="3" class="text-center p-3 text-muted">No hay categorías registradas.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($categorias_lista as $cat): ?>
                                                <tr>
                                                    <td class="text-center fw-bold"><span class="badge bg-secondary"><?= htmlspecialchars($cat['codigo']) ?></span></td>
                                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($cat['nombre']) ?></td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-primary me-1" onclick='editarCategoria(<?= json_encode($cat) ?>)'>
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarCategoria(<?= $cat['id'] ?>)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL PARA AGREGAR / EDITAR ÍTEM -->
<div class="modal fade" id="modalIndirectoItem" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalItemLabel"><i class="bi bi-plus-circle me-2"></i>Nuevo Ítem de Gasto Indirecto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formIndirectoItem">
                <div class="modal-body bg-light">
                    <input type="hidden" name="id" id="item_id" value="">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Categoría *</label>
                            <select name="categoria_id" id="item_categoria_id" class="form-select" required>
                                <option value="">-- Seleccione una Categoría --</option>
                                <?php foreach ($categorias_lista as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" data-codigo="<?= htmlspecialchars($cat['codigo']) ?>">
                                        [<?= htmlspecialchars($cat['codigo']) ?>] <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nº. Ítem</label>
                            <input name="item_codigo" id="item_codigo" class="form-control" placeholder="Ej: 1.1">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Descripción *</label>
                            <input name="descripcion" id="item_descripcion" class="form-control" required placeholder="Ej: Cartel de Obra">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Observación</label>
                            <input name="observacion" id="item_observacion" class="form-control" placeholder="Detalle técnico o aclaración adicional">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cantidad *</label>
                            <input type="number" step="0.01" name="cantidad_defecto" id="item_cantidad" class="form-control text-end" required value="1.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Unidad *</label>
                            <select name="unidad" id="item_unidad" class="form-select" required>
                                <?php foreach ($unidades_disponibles as $u): ?>
                                    <option value="<?= $u ?>"><?= $u ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Afectación *</label>
                            <select name="afectacion_defecto" id="item_afectacion" class="form-select" required>
                                <?php foreach ($afectaciones_disponibles as $val => $lbl): ?>
                                    <option value="<?= $val ?>"><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- CASILLEROS DEPENDIENTES -->
                        <div class="col-md-12">
                            <div class="form-check form-switch bg-white p-2 ps-5 rounded border mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="item_depende_operarios" name="depende_operarios" value="1">
                                <label class="form-check-label text-dark" for="item_depende_operarios">
                                    <i class="bi bi-people-fill text-primary me-1"></i> Depende de la Cantidad de Operarios (Multiplica el valor por la variable Operarios)
                                </label>
                            </div>
                            <div class="form-check form-switch bg-white p-2 ps-5 rounded border">
                                <input class="form-check-input" type="checkbox" role="switch" id="item_depende_tiempo" name="depende_tiempo" value="1" checked>
                                <label class="form-check-label text-dark" for="item_depende_tiempo">
                                    <i class="bi bi-calendar-event text-primary me-1"></i> Depende del Tiempo de Obra (Multiplica el valor por Días de Obra)
                                </label>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Precio Unitario ($) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" name="unitario_defecto" id="item_unitario" class="form-control text-end" required placeholder="0,00">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Estado *</label>
                            <select name="activo" id="item_activo" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark"><i class="bi bi-save me-1"></i> Guardar Ítem</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalItemBS;
let modoEdicionActivo = false;

// Función para parsear importes con formato AR (12.000,50 -> 12000.50)
function parseMonedaFloat(valStr) {
    if (!valStr) return 0;
    let clean = valStr.toString().replace(/\./g, '').replace(',', '.');
    return parseFloat(clean) || 0;
}

// Función para formatear a número argentino
function formatMonedaArg(valor) {
    return new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valor);
}

document.addEventListener("DOMContentLoaded", function(){
    modalItemBS = new bootstrap.Modal(document.getElementById('modalIndirectoItem'));

    // Recálculo dinámico en pantalla
    function recalcularTotales() {
        let totalGeneral = 0;
        const diasObraVal = parseFloat(document.getElementById('dias_obra').value) || 0;
        const operariosVal = parseFloat(document.getElementById('cant_operarios').value) || 0;

        document.querySelectorAll('#acordeonIndirectos .accordion-item').forEach(accItem => {
            let subtotalCat = 0;
            accItem.querySelectorAll('tr[data-id]').forEach(tr => {
                const isChecked = tr.querySelector('.switch-estado').checked;
                const dependeOperarios = parseInt(tr.getAttribute('data-depende-operarios') || '0');
                const dependeTiempo = parseInt(tr.getAttribute('data-depende-tiempo') || '1');
                
                const cant = parseFloat(tr.querySelector('.input-cant').value) || 0;
                const afec = parseFloat(tr.querySelector('.input-afec').value) || 0;
                
                const unitInput = tr.querySelector('.input-unit');
                const unit = parseMonedaFloat(unitInput.value);

                const multOp = (dependeOperarios === 1) ? operariosVal : 1;
                const multDias = (dependeTiempo === 1) ? diasObraVal : 1;
                const importe = cant * afec * unit * multDias * multOp;
                
                tr.querySelector('.cell-importe').textContent = '$ ' + formatMonedaArg(importe);
                
                if (isChecked) {
                    subtotalCat += importe;
                }
            });

            const subLabel = accItem.querySelector('.subtotal-cat-val');
            if (subLabel) {
                subLabel.textContent = formatMonedaArg(subtotalCat);
            }
            totalGeneral += subtotalCat;
        });

        const totalBanner = document.getElementById('totalGeneralBanner');
        if (totalBanner) totalBanner.textContent = '$ ' + formatMonedaArg(totalGeneral);
    }

    // Escuchar tipeo / selección de inputs
    document.querySelectorAll('#acordeonIndirectos input, #acordeonIndirectos select, #dias_obra, #cant_operarios').forEach(elem => {
        elem.addEventListener('input', recalcularTotales);
        elem.addEventListener('change', recalcularTotales);
    });

    // Formatear automáticamente el unitario al salir del campo (blur) durante la edición
    $(document).on('blur', '.input-unit', function(){
        let valFloat = parseMonedaFloat($(this).val());
        $(this).val(formatMonedaArg(valFloat));
    });

    // Guardar Parámetros Globales
    $('#formConfigGlobal').submit(function(e){
        e.preventDefault();
        $.post('indirectos.php?action=guardar_config_global', $(this).serialize(), function(res){
            if(res.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Configuración Guardada!',
                    text: 'Las variables globales se han actualizado.',
                    timer: 1500,
                    showConfirmButton: false
                });
                recalcularTotales();
            } else {
                Swal.fire('Error', 'No se pudieron guardar las variables globales.', 'error');
            }
        }, 'json');
    });

    // Switch de prender/apagar ítem
    $(document).on('change', '.switch-estado', function(){
        const chk = $(this);
        const id = chk.data('id');
        const estado = chk.is(':checked') ? 1 : 0;
        const tr = chk.closest('tr');

        $.post('indirectos.php?action=toggle_estado_item', { id: id, estado: estado }, function(res){
            if (res.success) {
                if (estado === 1) {
                    tr.removeClass('table-secondary text-muted opacity-75');
                    if (modoEdicionActivo) {
                        tr.find('input, select').not('.switch-estado').prop('disabled', false);
                    }
                } else {
                    tr.addClass('table-secondary text-muted opacity-75');
                    tr.find('input, select').not('.switch-estado').prop('disabled', true);
                }
                recalcularTotales();
            } else {
                chk.prop('checked', !estado);
                Swal.fire('Error', 'No se pudo cambiar el estado del ítem.', 'error');
            }
        }, 'json');
    });

    // Guardar Categoría
    $('#formCategoria').submit(function(e){
        e.preventDefault();
        $.post('indirectos.php?action=guardar_categoria', $(this).serialize(), function(res){
            if(res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Categoría guardada',
                    timer: 1200,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', 'No se pudo guardar la categoría.', 'error');
            }
        }, 'json');
    });

    // Guardar Ítem Individual (Modal)
    $('#formIndirectoItem').submit(function(e){
        e.preventDefault();
        $.post('indirectos.php?action=guardar_item', $(this).serialize(), function(res){
            if(res.success) {
                modalItemBS.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Ítem guardado',
                    timer: 1200,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', 'No se pudo procesar el ítem.', 'error');
            }
        }, 'json');
    });
});

// CONTROL DE EDICIÓN RÁPIDA (Idéntico comportamiento que Materiales)
function toggleModoEdicion() {
    modoEdicionActivo = !modoEdicionActivo;

    if (modoEdicionActivo) {
        document.body.classList.add('modo-edicion-activo');
        $('#btnModoEdicion').html('<i class="bi bi-x-circle me-1"></i> Cancelar Edición').removeClass('btn-outline-warning').addClass('btn-outline-secondary');
        
        // Mostrar botón de guardar cambios
        $('#btnGuardarRapido').removeClass('d-none').addClass('d-inline-flex');
        
        // Habilitar campos de ítems activos
        $('#acordeonIndirectos tr').each(function(){
            const isChecked = $(this).find('.switch-estado').is(':checked');
            if (isChecked) {
                $(this).find('input, select').not('.switch-estado').prop('disabled', false);
            }
        });
    } else {
        document.body.classList.remove('modo-edicion-activo');
        $('#btnModoEdicion').html('<i class="bi bi-pencil-square me-1"></i> Edición Rápida').removeClass('btn-outline-secondary').addClass('btn-outline-warning');
        
        // Ocultar botón de guardar cambios
        $('#btnGuardarRapido').removeClass('d-inline-flex').addClass('d-none');
        
        // Deshabilitar todos los campos nuevamente
        $('#acordeonIndirectos tr input, #acordeonIndirectos tr select').not('.switch-estado').prop('disabled', true);
    }
}

function guardarEdicionRapida() {
    $.post('indirectos.php?action=guardar_indirectos_lote', $('#formIndirectosLote').serialize(), function(res){
        if(res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Cambios guardados',
                text: 'Se actualizaron los valores de la plantilla.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', 'Error al guardar los cambios: ' + (res.error || ''), 'error');
        }
    }, 'json');
}

// ELIMINAR ÍTEM CON SWEETALERT2
function eliminarItem(id) {
    Swal.fire({
        title: '¿Eliminar este ítem?',
        text: 'Esta acción borrará el ítem de la plantilla base.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('indirectos.php?action=eliminar_item', { id: id }, function(res){
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'El ítem fue removido correctamente.',
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', 'No se pudo eliminar el ítem.', 'error');
                }
            }, 'json');
        }
    });
}

// FUNCIONES AUXILIARES CATEGORÍAS
function editarCategoria(cat) {
    $('#cat_id').val(cat.id);
    $('#cat_codigo').val(cat.codigo);
    $('#cat_nombre').val(cat.nombre);
    $('#tituloFormCat').text('Editar Categoría');
    $('#btnCancelarEditCat').removeClass('d-none');
}

function limpiarFormCat() {
    $('#formCategoria')[0].reset();
    $('#cat_id').val('');
    $('#tituloFormCat').text('Agregar Nueva Categoría');
    $('#btnCancelarEditCat').addClass('d-none');
}

function eliminarCategoria(id) {
    Swal.fire({
        title: '¿Eliminar esta categoría?',
        text: 'Ten en cuenta que no se borrarán los ítems asociados automáticamente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('indirectos.php?action=eliminar_categoria', { id: id }, function(res){
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Categoría eliminada',
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', 'No se pudo eliminar la categoría.', 'error');
                }
            }, 'json');
        }
    });
}

// FUNCIONES AUXILIARES ÍTEMS
function abrirModalItem(modo) {
    $('#formIndirectoItem')[0].reset();
    $('#item_id').val('');
    $('#item_activo').val('1');
    $('#item_afectacion').val('1.00');
    $('#item_cantidad').val('1.00');
    $('#item_depende_operarios').prop('checked', false);
    $('#item_depende_tiempo').prop('checked', true);
    $('#modalItemLabel').html('<i class="bi bi-plus-circle me-2"></i>Nuevo Ítem de Gasto Indirecto');
    modalItemBS.show();
}

function editarItem(item) {
    $('#item_id').val(item.id);
    
    let matchedOption = false;
    $('#item_categoria_id option').each(function(){
        if ($(this).data('codigo') === item.categoria_codigo) {
            $(this).prop('selected', true);
            matchedOption = true;
        }
    });
    if (!matchedOption) $('#item_categoria_id').val('');

    $('#item_codigo').val(item.item_codigo);
    $('#item_descripcion').val(item.descripcion);
    $('#item_observacion').val(item.observacion);
    $('#item_cantidad').val(parseFloat(item.cantidad_defecto).toFixed(2));
    $('#item_unidad').val(item.unidad);
    
    $('#item_depende_operarios').prop('checked', parseInt(item.depende_operarios) === 1);
    $('#item_depende_tiempo').prop('checked', parseInt(item.depende_tiempo) === 1);

    let afecVal = parseFloat(item.afectacion_defecto).toFixed(2);
    $('#item_afectacion').val(afecVal);
    
    $('#item_unitario').val(formatMonedaArg(parseFloat(item.unitario_defecto)));
    $('#item_activo').val(item.activo);

    $('#modalItemLabel').html('<i class="bi bi-pencil-square me-2"></i>Editar Ítem de Gasto Indirecto');
    modalItemBS.show();
}
</script>

<?php include '../../includes/footer.php'; ?>