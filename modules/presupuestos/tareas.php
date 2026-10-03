<?php
// C:\xampp\htdocs\contable\modules\presupuestos\tareas.php
session_start();
require_once '../../config/database.php';

echo "<script>localStorage.setItem('menuOperaciones', 'closed'); localStorage.setItem('menuConfig', 'closed');</script>";

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<style>
/* Ancho unificado para todos los botones de agregar en el modal */
.btn-add-row {
    width: 190px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}    
</style>

<!-- TomSelect CSS/JS para buscador en combos -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-calculator-fill text-secondary me-2"></i> Análisis de Precios Unitarios (Tareas / APU)
        </h4>
        <button class="btn btn-dark d-flex align-items-center" onclick="nuevaTarea()">
            <i class="bi bi-plus-circle me-2"></i> Nueva Tarea / APU
        </button>
    </div>
    
    <!-- FILTROS DE BÚSQUEDA DEL INDEX -->
    <div class="card shadow-sm border-0 p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Filtrar por Código</label>
                <input type="text" id="filter_codigo" class="form-control form-control-sm" placeholder="Buscar código...">
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold small mb-1">Filtrar por Descripción / Tarea</label>
                <input type="text" id="filter_nombre" class="form-control form-control-sm" placeholder="Buscar tarea...">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small mb-1">Filtrar por Usuario Creador</label>
                <input type="text" id="filter_usuario" class="form-control form-control-sm" placeholder="Buscar usuario...">
            </div>
        </div>
    </div>

    <!-- TABLA PRINCIPAL DE TAREAS -->
    <div class="card shadow-sm border-0 p-3">
        <div class="table-responsive">
            <table id="tablaTareas" class="table table-bordered table-striped w-100 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width: 5%;">Cód.</th>
                        <th style="width: 50%;">Descripción de Tarea</th>
                        <th class="text-center" style="width: 5%;">Unidad</th>
                        <th class="text-end" style="width: 10%;">Costo Total</th>
                        <th class="text-center" style="width: 3%;">Usuario</th>
                        <th class="text-center" style="width: 10%;">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- ================================================================= -->
<!-- MODAL SELECCIÓN COEFICIENTE K Y OPCIONES DE IMPRESIÓN/PDF -->
<!-- ================================================================= -->
<div class="modal fade" id="modalImprimirK" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-file-earmark-pdf me-2"></i> Exportar Tarea / APU</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tarea_id_pdf" value="0">
                
                <div class="mb-3">
                    <label class="form-label fw-bold small">Seleccione Coeficiente "K" a aplicar:</label>
                    <select class="form-select form-select-sm" id="select_perfil_k">
                        <option value="0">Sin Coeficiente (Costo Neto Puro - K = 1.000)</option>
                        <?php
                        $db_conn =$conn ?? $conexion ?? $db ?? null;
                        if ($db_conn) {
                            $res_k =$db_conn->query("SELECT id, nombre, mat_k_resultante, mo_k_resultante FROM presupuesto_coeficientes_plantillas WHERE activo = 1 ORDER BY id DESC");
                            while($k_row =$res_k->fetch_assoc()):
                            ?>
                                <option value="<?= $k_row['id'] ?>">
                                    <?= htmlspecialchars($k_row['nombre']) ?> 
                                    (K Mat: <?= number_format($k_row['mat_k_resultante'], 3) ?> / K MO: <?= number_format($k_row['mo_k_resultante'], 3) ?>)
                                </option>
                            <?php 
                            endwhile; 
                        }
                        ?>
                    </select>
                </div>

                <!-- OPCIONES DE IMPRESIÓN -->
                <div class="mb-2">
                    <label class="form-label fw-bold small">Opciones de presentación:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="chk_k_visible">
                        <label class="form-check-label small" for="chk_k_visible">
                            <strong>K visible</strong> (Mostrar cuadro de coeficientes y desglose K)
                        </label>
                    </div>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" value="1" id="chk_agregar_logo">
                        <label class="form-check-label small" for="chk_agregar_logo">
                            <strong>Agregar logo</strong> (Encabezado con título a la izquierda y logo institucional)
                        </label>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-danger px-3" onclick="generarPDFConK()">
                    <i class="bi bi-file-pdf me-1"></i> Generar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- INCLUSIÓN DEL MODAL DE EDICIÓN/CREACIÓN DE TAREA -->
<?php include 'includes/modal_tarea.php'; ?>

<!-- CARGA DE JS ESPECÍFICO DEL MÓDULO (NECESARIO PARA DATATABLES) -->
<script src="assets/js/tareas.js"></script>

<script>
let modalKInstancia = null;

function obtenerInstanciaModalK() {
    let el = document.getElementById('modalImprimirK');
    if (!el) return null;
    return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
}

function abrirModalImpresion(idTarea) {
    document.getElementById('tarea_id_pdf').value = idTarea;
    document.getElementById('chk_k_visible').checked = false;
    document.getElementById('chk_agregar_logo').checked = false;
    
    let modal = obtenerInstanciaModalK();
    if (modal) modal.show();
}

function generarPDFConK() {
    let tareaId   = document.getElementById('tarea_id_pdf').value;
    let kId       = document.getElementById('select_perfil_k').value;
    let kVisible  = document.getElementById('chk_k_visible').checked ? 1 : 0;
    let conLogo   = document.getElementById('chk_agregar_logo').checked ? 1 : 0;
    
    if (tareaId > 0) {
        let url = `ajax/generar_pdf_tarea.php?id=${tareaId}&k_id=${kId}&k_visible=${kVisible}&logo=${conLogo}`;
        window.open(url, '_blank');
        
        let modal = obtenerInstanciaModalK();
        if (modal) modal.hide();
    }
}
</script>

<?php include '../../includes/footer.php'; ?>