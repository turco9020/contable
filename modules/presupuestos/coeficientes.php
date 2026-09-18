<?php
require_once '../../config/database.php';

// Cierre preventivo de otros submenús en el sidebar
echo "<script>localStorage.setItem('menuOperaciones', 'closed'); localStorage.setItem('menuConfig', 'closed');</script>";

// Detectar variable de conexión MySQLi
$db_conn = $conn ?? $conexion ?? $db ?? null;

// Control de guardado mediante POST (MySQLi Prepared Statements)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'guardar_coeficiente') {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    // Materiales
    $mat_indirectos = floatval($_POST['mat_costos_indirectos']);
    $mat_beneficio  = floatval($_POST['mat_beneficio']);
    $mat_financieros= floatval($_POST['mat_costos_financieros']);
    $mat_iibb       = floatval($_POST['mat_iibb']);
    $mat_otros_imp  = floatval($_POST['mat_otros_impuestos']);
    $mat_iva        = floatval($_POST['mat_iva']);
    $mat_k          = floatval($_POST['mat_k_resultante']);

    // Mano de Obra
    $mo_indirectos  = floatval($_POST['mo_costos_indirectos']);
    $mo_beneficio   = floatval($_POST['mo_beneficio']);
    $mo_financieros = floatval($_POST['mo_costos_financieros']);
    $mo_iibb        = floatval($_POST['mo_iibb']);
    $mo_otros_imp   = floatval($_POST['mo_otros_impuestos']);
    $mo_iva         = floatval($_POST['mo_iva']);
    $mo_k           = floatval($_POST['mo_k_resultante']);

    if ($id > 0) {
        $stmt = $db_conn->prepare("UPDATE presupuesto_coeficientes_plantillas SET 
            nombre=?, descripcion=?, 
            mat_costos_indirectos=?, mat_beneficio=?, mat_costos_financieros=?, mat_iibb=?, mat_otros_impuestos=?, mat_iva=?, mat_k_resultante=?,
            mo_costos_indirectos=?, mo_beneficio=?, mo_costos_financieros=?, mo_iibb=?, mo_otros_impuestos=?, mo_iva=?, mo_k_resultante=?
            WHERE id=?");
        $stmt->bind_param("ssddddddddddddddi", 
            $nombre, $descripcion,
            $mat_indirectos, $mat_beneficio, $mat_financieros, $mat_iibb, $mat_otros_imp, $mat_iva, $mat_k,
            $mo_indirectos, $mo_beneficio, $mo_financieros, $mo_iibb, $mo_otros_imp, $mo_iva, $mo_k,
            $id
        );
        $stmt->execute();
    } else {
        $stmt = $db_conn->prepare("INSERT INTO presupuesto_coeficientes_plantillas (
            nombre, descripcion, 
            mat_costos_indirectos, mat_beneficio, mat_costos_financieros, mat_iibb, mat_otros_impuestos, mat_iva, mat_k_resultante,
            mo_costos_indirectos, mo_beneficio, mo_costos_financieros, mo_iibb, mo_otros_impuestos, mo_iva, mo_k_resultante
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssdddddddddddddd", 
            $nombre, $descripcion,
            $mat_indirectos, $mat_beneficio, $mat_financieros, $mat_iibb, $mat_otros_imp, $mat_iva, $mat_k,
            $mo_indirectos, $mo_beneficio, $mo_financieros, $mo_iibb, $mo_otros_imp, $mo_iva, $mo_k
        );
        $stmt->execute();
    }
    header('Location: coeficientes.php?msg=guardado');
    exit;
}

// Inclusión del layout estándar del sistema
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Consultar perfiles con MySQLi
$plantillas = [];
if ($db_conn) {
    $result = $db_conn->query("SELECT * FROM presupuesto_coeficientes_plantillas WHERE activo = 1 ORDER BY id DESC");
    if ($result) {
        $plantillas = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<div class="content p-4 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-percent text-secondary me-2"></i> Determinación de Coeficientes "K"
            </h4>
            <p class="text-muted small mb-0">Configuración de matrices polinómicas para Materiales y Mano de Obra.</p>
        </div>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModalNuevo()">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Perfil
        </button>
    </div>

    <!-- TABLA DE PERFILES -->
    <div class="card p-3 shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0 w-100">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre del Perfil</th>
                        <th>Descripción</th>
                        <th class="text-center">K Materiales</th>
                        <th class="text-center">K Mano de Obra</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($plantillas)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No hay perfiles de coeficientes cargados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($plantillas as $p): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($p['descripcion']) ?></td>
                            <td class="text-center"><span class="badge bg-info text-dark fs-6">K = <?= number_format($p['mat_k_resultante'], 3) ?></span></td>
                            <td class="text-center"><span class="badge bg-warning text-dark fs-6">K = <?= number_format($p['mo_k_resultante'], 3) ?></span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary" onclick='editarPerfil(<?= json_encode($p) ?>)'>
                                    <i class="bi bi-pencil me-1"></i> Editar
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

<!-- MODAL CÁLCULO DE K -->
<div class="modal fade" id="modalCoeficiente" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" id="formCoeficiente">
                <input type="hidden" name="action" value="guardar_coeficiente">
                <input type="hidden" name="id" id="coef_id" value="0">
                
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalTitulo"><i class="bi bi-calculator me-2"></i> Configurar Perfil de Coeficientes</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body bg-light">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Nombre del Perfil / Obra</label>
                            <input type="text" name="nombre" id="nombre" class="form-control form-control-sm" required placeholder="Ej: Obra Pública / Cliente Privado">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Descripción / Observaciones</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control form-control-sm" placeholder="Ej: Válido para licitaciones con IVA incluido">
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- MATERIALES -->
                        <div class="col-md-6">
                            <div class="card border-primary shadow-sm h-100">
                                <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center py-2">
                                    <span class="small"><i class="bi bi-box-seam me-1"></i> COEFICIENTE MATERIALES</span>
                                    <span class="fs-6" id="mat_k_display">K = 1.710</span>
                                </div>
                                <div class="card-body p-3">
                                    <input type="hidden" name="mat_k_resultante" id="mat_k_resultante" value="1.7100">
                                    <table class="table table-sm table-borderless align-middle mb-0 small">
                                        <tr>
                                            <td class="fw-bold">COSTO NETO ÍTEM (A)</td>
                                            <td><input type="text" class="form-control form-control-sm text-end" value="1,000" disabled></td>
                                        </tr>
                                        <tr>
                                            <td>Costos Indirectos % (A)</td>
                                            <td><input type="number" step="0.01" name="mat_costos_indirectos" id="mat_indirectos" class="form-control form-control-sm text-end calc-mat" value="10.73"></td>
                                        </tr>
                                        <tr>
                                            <td>Beneficio % (A)</td>
                                            <td><input type="number" step="0.01" name="mat_beneficio" id="mat_beneficio" class="form-control form-control-sm text-end calc-mat" value="20.00"></td>
                                        </tr>
                                        <tr class="table-secondary">
                                            <td class="fw-bold">SUBTOTAL (D) [A+B+C]</td>
                                            <td class="fw-bold text-end" id="mat_subtotal_d">1.307</td>
                                        </tr>
                                        <tr>
                                            <td>Costos Financieros % (D)</td>
                                            <td><input type="number" step="0.01" name="mat_costos_financieros" id="mat_financieros" class="form-control form-control-sm text-end calc-mat" value="2.00"></td>
                                        </tr>
                                        <tr class="table-secondary">
                                            <td class="fw-bold">SUBTOTAL (F) [D+E]</td>
                                            <td class="fw-bold text-end" id="mat_subtotal_f">1.333</td>
                                        </tr>
                                        <tr>
                                            <td>Ingresos Brutos % (F)</td>
                                            <td><input type="number" step="0.01" name="mat_iibb" id="mat_iibb" class="form-control form-control-sm text-end calc-mat" value="2.00"></td>
                                        </tr>
                                        <tr>
                                            <td>Otros Impuestos % (C)</td>
                                            <td><input type="number" step="0.01" name="mat_otros_impuestos" id="mat_otros_imp" class="form-control form-control-sm text-end calc-mat" value="35.00"></td>
                                        </tr>
                                        <tr>
                                            <td>IVA %</td>
                                            <td><input type="number" step="0.01" name="mat_iva" id="mat_iva" class="form-control form-control-sm text-end calc-mat" value="21.00"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- MANO DE OBRA -->
                        <div class="col-md-6">
                            <div class="card border-warning shadow-sm h-100">
                                <div class="card-header bg-warning text-dark fw-bold d-flex justify-content-between align-items-center py-2">
                                    <span class="small"><i class="bi bi-tools me-1"></i> COEFICIENTE MANO DE OBRA</span>
                                    <span class="fs-6" id="mo_k_display">K = 1.885</span>
                                </div>
                                <div class="card-body p-3">
                                    <input type="hidden" name="mo_k_resultante" id="mo_k_resultante" value="1.8845">
                                    <table class="table table-sm table-borderless align-middle mb-0 small">
                                        <tr>
                                            <td class="fw-bold">COSTO NETO ÍTEM (A)</td>
                                            <td><input type="text" class="form-control form-control-sm text-end" value="1,000" disabled></td>
                                        </tr>
                                        <tr>
                                            <td>Costos Indirectos % (A)</td>
                                            <td><input type="number" step="0.01" name="mo_costos_indirectos" id="mo_indirectos" class="form-control form-control-sm text-end calc-mo" value="15.00"></td>
                                        </tr>
                                        <tr>
                                            <td>Beneficio % (A)</td>
                                            <td><input type="number" step="0.01" name="mo_beneficio" id="mo_beneficio" class="form-control form-control-sm text-end calc-mo" value="25.00"></td>
                                        </tr>
                                        <tr class="table-secondary">
                                            <td class="fw-bold">SUBTOTAL (D) [A+B+C]</td>
                                            <td class="fw-bold text-end" id="mo_subtotal_d">1.400</td>
                                        </tr>
                                        <tr>
                                            <td>Costos Financieros % (D)</td>
                                            <td><input type="number" step="0.01" name="mo_costos_financieros" id="mo_financieros" class="form-control form-control-sm text-end calc-mo" value="2.00"></td>
                                        </tr>
                                        <tr class="table-secondary">
                                            <td class="fw-bold">SUBTOTAL (F) [D+E]</td>
                                            <td class="fw-bold text-end" id="mo_subtotal_f">1.428</td>
                                        </tr>
                                        <tr>
                                            <td>Ingresos Brutos % (F)</td>
                                            <td><input type="number" step="0.01" name="mo_iibb" id="mo_iibb" class="form-control form-control-sm text-end calc-mo" value="2.00"></td>
                                        </tr>
                                        <tr>
                                            <td>Otros Impuestos % (C)</td>
                                            <td><input type="number" step="0.01" name="mo_otros_impuestos" id="mo_otros_imp" class="form-control form-control-sm text-end calc-mo" value="35.00"></td>
                                        </tr>
                                        <tr>
                                            <td>IVA %</td>
                                            <td><input type="number" step="0.01" name="mo_iva" id="mo_iva" class="form-control form-control-sm text-end calc-mo" value="21.00"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark px-4"><i class="bi bi-save me-1"></i> Guardar Perfil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
let modalCoeficiente;

document.addEventListener('DOMContentLoaded', function() {
    modalCoeficiente = new bootstrap.Modal(document.getElementById('modalCoeficiente'));

    document.querySelectorAll('.calc-mat').forEach(el => el.addEventListener('input', calcularMat));
    document.querySelectorAll('.calc-mo').forEach(el => el.addEventListener('input', calcularMO));

    calcularMat();
    calcularMO();
});

function calcularCascada(indirectosP, beneficioP, financierosP, iibbP, otrosImpP, ivaP) {
    let A = 1.000;
    let B = A * (indirectosP / 100);
    let C = A * (beneficioP / 100);
    let D = A + B + C;
    let E = D * (financierosP / 100);
    let F = D + E;
    let G = F * (iibbP / 100);
    let H = C * (otrosImpP / 100);
    let I = (F + G + H) * (ivaP / 100);
    let K = F + G + H + I;

    return { D, F, K };
}

function calcularMat() {
    let ind = parseFloat(document.getElementById('mat_indirectos').value) || 0;
    let ben = parseFloat(document.getElementById('mat_beneficio').value) || 0;
    let fin = parseFloat(document.getElementById('mat_financieros').value) || 0;
    let iibb = parseFloat(document.getElementById('mat_iibb').value) || 0;
    let otros = parseFloat(document.getElementById('mat_otros_imp').value) || 0;
    let iva = parseFloat(document.getElementById('mat_iva').value) || 0;

    let res = calcularCascada(ind, ben, fin, iibb, otros, iva);

    document.getElementById('mat_subtotal_d').innerText = res.D.toFixed(3);
    document.getElementById('mat_subtotal_f').innerText = res.F.toFixed(3);
    document.getElementById('mat_k_display').innerText = 'K = ' + res.K.toFixed(3);
    document.getElementById('mat_k_resultante').value = res.K.toFixed(4);
}

function calcularMO() {
    let ind = parseFloat(document.getElementById('mo_indirectos').value) || 0;
    let ben = parseFloat(document.getElementById('mo_beneficio').value) || 0;
    let fin = parseFloat(document.getElementById('mo_financieros').value) || 0;
    let iibb = parseFloat(document.getElementById('mo_iibb').value) || 0;
    let otros = parseFloat(document.getElementById('mo_otros_imp').value) || 0;
    let iva = parseFloat(document.getElementById('mo_iva').value) || 0;

    let res = calcularCascada(ind, ben, fin, iibb, otros, iva);

    document.getElementById('mo_subtotal_d').innerText = res.D.toFixed(3);
    document.getElementById('mo_subtotal_f').innerText = res.F.toFixed(3);
    document.getElementById('mo_k_display').innerText = 'K = ' + res.K.toFixed(3);
    document.getElementById('mo_k_resultante').value = res.K.toFixed(4);
}

function abrirModalNuevo() {
    document.getElementById('coef_id').value = 0;
    document.getElementById('modalTitulo').innerHTML = '<i class="bi bi-calculator me-2"></i> Nuevo Perfil de Coeficientes';
    document.getElementById('formCoeficiente').reset();
    calcularMat();
    calcularMO();
    modalCoeficiente.show();
}

function editarPerfil(p) {
    document.getElementById('coef_id').value = p.id;
    document.getElementById('modalTitulo').innerHTML = '<i class="bi bi-pencil me-2"></i> Editar Perfil: ' + p.nombre;
    document.getElementById('nombre').value = p.nombre;
    document.getElementById('descripcion').value = p.descripcion;

    document.getElementById('mat_indirectos').value = p.mat_costos_indirectos;
    document.getElementById('mat_beneficio').value = p.mat_beneficio;
    document.getElementById('mat_financieros').value = p.mat_costos_financieros;
    document.getElementById('mat_iibb').value = p.mat_iibb;
    document.getElementById('mat_otros_imp').value = p.mat_otros_impuestos;
    document.getElementById('mat_iva').value = p.mat_iva;

    document.getElementById('mo_indirectos').value = p.mo_costos_indirectos;
    document.getElementById('mo_beneficio').value = p.mo_beneficio;
    document.getElementById('mo_financieros').value = p.mo_costos_financieros;
    document.getElementById('mo_iibb').value = p.mo_iibb;
    document.getElementById('mo_otros_imp').value = p.mo_otros_impuestos;
    document.getElementById('mo_iva').value = p.mo_iva;

    calcularMat();
    calcularMO();
    modalCoeficiente.show();
}
</script>