<?php
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>
<style>
#modalPresupuesto .modal-dialog {
    width: 95%;
    max-width: 95%;
    height: 95vh;
    margin: 2.5vh auto;
}

#modalPresupuesto .modal-content {
    height: 100%;
}

/* Modal "Ver APU" apilado encima del modal del presupuesto */
#modalVerAPU { z-index: 1065; }

/* Select de tarea + botón Ver en la misma línea */
.apu-cell .ts-wrapper { flex: 1 1 auto; min-width: 0; }

</style>

<div class="content">
    <!-- CABECERA DE MÓDULO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-file-earmark-spreadsheet-fill text-secondary me-2"></i> Módulo de Presupuestos
        </h4>
        <button class="btn btn-dark d-flex align-items-center" onclick="nuevoPresupuesto()">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Presupuesto
        </button>
    </div>

    <!-- FILTROS DESDE / HASTA / CLIENTE / ESTADO -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body p-3">
            <form id="formFiltros" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1" for="filtro_desde">Desde</label>
                    <input type="date" id="filtro_desde" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1" for="filtro_hasta">Hasta</label>
                    <input type="date" id="filtro_hasta" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small mb-1" for="filtro_cliente">Cliente</label>
                    <select id="filtro_cliente" class="form-select form-select-sm">
                        <option value="">-- Todos los Clientes --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small mb-1" for="filtro_estado">Estado</label>
                    <select id="filtro_estado" class="form-select form-select-sm">
                        <option value="">-- Todos los Estados --</option>
                        <option value="Borrador">Borrador</option>
                        <option value="Enviado">Enviado</option>
                        <option value="Aprobado">Aprobado</option>
                        <option value="Rechazado">Rechazado</option>
                        <option value="Archivado">Archivado</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-dark w-100" onclick="aplicarFiltros()">
                        <i class="bi bi-filter"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarFiltros()" title="Limpiar Filtros">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLA DE PRESUPUESTOS (DATATABLES) -->
    <div class="card shadow-sm border-0 p-3">
        <div class="table-responsive">
            <table id="tablaPresupuestos" class="table table-bordered table-striped w-100 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>N° Presupuesto</th>
                        <th>Fecha</th>
                        <th>Título / Proyecto</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PRINCIPAL: NUEVO / EDITAR PRESUPUESTO -->
<div class="modal fade" id="modalPresupuesto" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title fw-bold fs-6" id="modalPresupuestoLabel">
                    <i class="bi bi-file-earmark-plus me-2"></i> Confección de Presupuesto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formPresupuesto">
                <div class="modal-body p-3">
                    <input type="hidden" id="presupuesto_id" value="0">

                    <!-- DATOS GENERALES -->
                    <div class="row g-2 mb-3 bg-light p-2 rounded border">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small mb-0" for="pres_codigo">N° Presupuesto</label>
                            <input type="text" id="pres_codigo" class="form-control form-control-sm fw-bold" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small mb-0" for="pres_fecha">Fecha *</label>
                            <input type="date" id="pres_fecha" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small mb-0" for="pres_cliente_id">Cliente *</label>
                            <select id="pres_cliente_id" class="form-select form-select-sm" required></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small mb-0" for="pres_obra_id">Obra Vinculada</label>
                            <select id="pres_obra_id" class="form-select form-select-sm"></select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small mb-0" for="pres_estado">Estado</label>
                            <select id="pres_estado" class="form-select form-select-sm">
                                <option value="Borrador">Borrador</option>
                                <option value="Enviado">Enviado</option>
                                <option value="Aprobado">Aprobado</option>
                                <option value="Rechazado">Rechazado</option>
                                <option value="Archivado">Archivado</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small mb-0" for="pres_titulo">Título / Proyecto / Referencia *</label>
                            <input type="text" id="pres_titulo" class="form-control form-control-sm" placeholder="Ej: Construcción Galpón Industrial" required>
                        </div>
                    </div>

                    <!-- PESTAÑAS -->
                    <ul class="nav nav-tabs" id="tabPresupuesto" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold py-1 px-3 text-dark" id="tab-pres-btn" data-bs-toggle="tab" data-bs-target="#tab-pres" type="button" role="tab">
                                <i class="bi bi-list-task text-primary me-1"></i> Presupuesto
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold py-1 px-3 text-dark" id="tab-ind-btn" data-bs-toggle="tab" data-bs-target="#tab-ind" type="button" role="tab">
                                <i class="bi bi-diagram-3 text-warning me-1"></i> Indirectos
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold py-1 px-3 text-dark" id="tab-k-btn" data-bs-toggle="tab" data-bs-target="#tab-k" type="button" role="tab">
                                <i class="bi bi-percent text-info me-1"></i> Coeficiente K
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold py-1 px-3 text-dark" id="tab-cond-btn" data-bs-toggle="tab" data-bs-target="#tab-cond" type="button" role="tab">
                                <i class="bi bi-file-text text-success me-1"></i> Condiciones
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold py-1 px-3 text-dark" id="tab-res-btn" data-bs-toggle="tab" data-bs-target="#tab-res" type="button" role="tab">
                                <i class="bi bi-clipboard-data text-danger me-1"></i> Resumen
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border border-top-0 p-3 bg-white rounded-bottom" id="tabPresupuestoContent">
                        
                        <!-- PESTAÑA 1: PRESUPUESTO -->
                        <div class="tab-pane fade show active" id="tab-pres" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label fw-semibold small mb-0" for="pres_k_base">Coeficiente K Base:</label>
                                    <select id="pres_k_base" class="form-select form-select-sm" style="width: 350px;" onchange="actualizarKDesdePlantilla()">
                                        <option value="1.0000" data-mat="1.0000" data-mo="1.0000">1.0000 - Sin Coeficiente (Neto)</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-dark" onclick="agregarRubro()">
                                    <i class="bi bi-plus-lg me-1"></i> AGREGAR RUBRO
                                </button>
                            </div>

                            <div id="contenedorRubros" class="d-flex flex-column gap-3 mb-3"></div>

                            <div class="card bg-light border p-3">
                                <div class="row align-items-center g-2">
                                    <div class="col-md-4 text-end ms-auto">
                                        <span class="fw-semibold text-muted d-block small">Subtotal Costo directo + K Base:</span>
                                        <span class="fw-bold fs-6 text-dark" id="lblSubtotalDirecto">$ 0,00</span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <label class="form-label fw-bold small text-primary mb-0" for="pres_k_general">K General Final (Pie):</label>
                                        <input type="number" step="0.0001" id="pres_k_general" class="form-control form-control-sm text-end fw-bold border-primary" value="1.0000" oninput="recalcularMatrizPresupuesto()">
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <span class="fw-bold text-uppercase d-block small text-dark">TOTAL GENERAL PRESUPUESTADO:</span>
                                        <span class="fw-bold fs-4 text-success" id="lblTotalPresupuesto">$ 0,00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 2: INDIRECTOS -->
                        <div class="tab-pane fade" id="tab-ind" role="tabpanel">
                            <!-- PANEL DE VARIABLES GLOBALES Y TOTALES -->
                            <div class="card bg-light border p-2 mb-3">
                                <div class="row align-items-center g-2">
                                    <div class="col-md-3 d-flex align-items-center gap-2">
                                        <label for="pres_ind_operarios" class="form-label mb-0 fw-semibold small">Operarios:</label>
                                        <input type="number" min="1" class="form-control form-control-sm text-center fw-bold" style="width: 70px;" id="pres_ind_operarios" value="2" oninput="recalcularIndirectosPresupuesto()">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-center gap-2">
                                        <label for="pres_ind_dias" class="form-label mb-0 fw-semibold small">Días Obra:</label>
                                        <input type="number" min="1" class="form-control form-control-sm text-center fw-bold" style="width: 70px;" id="pres_ind_dias" value="30" oninput="recalcularIndirectosPresupuesto()">
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <span class="fw-bold text-uppercase d-block small text-muted">TOTAL GASTOS INDIRECTOS (ACTIVOS):</span>
                                        <span class="fw-bold fs-5 text-dark" id="lblTotalIndirectos">$ 0,00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- CONTENEDOR DE ACORDEÓN DE CATEGORÍAS -->
                            <div class="accordion" id="acordeonIndirectosPresupuesto" style="max-height: 480px; overflow-y: auto;">
                                <!-- Se renderiza dinámicamente vía JS -->
                            </div>
                        </div>

                        <!-- PESTAÑA 3: COEFICIENTE K -->
                        <div class="tab-pane fade" id="tab-k" role="tabpanel">
                            <div class="row g-3 justify-content-center">
                                <div class="col-md-8">
                                    <div class="card border-secondary shadow-sm">
                                        <div class="card-header bg-secondary text-white fw-semibold d-flex justify-content-between align-items-center py-2">
                                            <span><i class="bi bi-tools me-2"></i> COEFICIENTE K - DETERMINACIÓN DE PRECIO</span>
                                            <span class="fs-6 fw-bold" id="lbl_k_mo_title">K = 1.0000</span>
                                        </div>
                                        <div class="card-body p-3">
                                            <table class="table table-sm table-bordered align-middle mb-0 small">
                                                <tbody>
                                                    <!-- COSTO NETO (A) -->
                                                    <tr class="table-light">
                                                        <td class="fw-bold">COSTO NETO ÍTEM (A) [Subtotal Directo]</td>
                                                        <td class="text-center fw-bold">100.00 %</td>
                                                        <td><input type="text" id="k_monto_a" class="form-control form-control-sm text-end fw-bold bg-light" value="$ 0,00" readonly></td>
                                                    </tr>
                                                   <!-- COSTOS INDIRECTOS (B) -->
                                                    <tr>
                                                        <td>Costos Indirectos % (B)</td>
                                                        <td><input type="number" step="0.01" id="k_mo_ind" class="form-control form-control-sm text-end bg-light fw-bold" value="0.00" readonly></td>
                                                        <td><input type="text" id="k_monto_b" class="form-control form-control-sm text-end bg-light" value="$ 0,00" readonly></td>
                                                    </tr>
                                                    <!-- BENEFICIO (C) -->
                                                    <tr>
                                                        <td>Beneficio % (C)</td>
                                                        <td><input type="number" step="0.01" id="k_mo_ben" class="form-control form-control-sm text-end calc-k-mo" value="25.00" oninput="calcularKGeneralMatMo()"></td>
                                                        <td><input type="text" id="k_monto_c" class="form-control form-control-sm text-end bg-light" value="$ 0,00" readonly></td>
                                                    </tr>
                                                    <!-- SUBTOTAL (D) -->
                                                    <tr class="table-secondary fw-bold">
                                                        <td>SUBTOTAL (D) [A + B + C]</td>
                                                        <td class="text-end" id="lbl_mo_sub_d">1.4000</td>
                                                        <td><input type="text" id="k_monto_d" class="form-control form-control-sm text-end fw-bold bg-light" value="$ 0,00" readonly></td>
                                                    </tr>
                                                    <!-- FINANCIEROS (E) -->
                                                    <tr>
                                                        <td>Costos Financieros % (E)</td>
                                                        <td><input type="number" step="0.01" id="k_mo_fin" class="form-control form-control-sm text-end calc-k-mo" value="2.00" oninput="calcularKGeneralMatMo()"></td>
                                                        <td><input type="text" id="k_monto_e" class="form-control form-control-sm text-end bg-light" value="$ 0,00" readonly></td>
                                                    </tr>
                                                    <!-- SUBTOTAL (F) -->
                                                    <tr class="table-secondary fw-bold">
                                                        <td>SUBTOTAL (F) [D + E]</td>
                                                        <td class="text-end" id="lbl_mo_sub_f">1.4280</td>
                                                        <td><input type="text" id="k_monto_f" class="form-control form-control-sm text-end fw-bold bg-light" value="$ 0,00" readonly></td>
                                                    </tr>
                                                    <!-- IIBB (G) -->
                                                    <tr>
                                                        <td>Ingresos Brutos % (G)</td>
                                                        <td><input type="number" step="0.01" id="k_mo_iibb" class="form-control form-control-sm text-end calc-k-mo" value="2.00" oninput="calcularKGeneralMatMo()"></td>
                                                        <td><input type="text" id="k_monto_g" class="form-control form-control-sm text-end bg-light" value="$ 0,00" readonly></td>
                                                    </tr>
                                                    <!-- OTROS IMPUESTOS (H) -->
                                                    <tr>
                                                        <td>Otros Impuestos % (H)</td>
                                                        <td><input type="number" step="0.01" id="k_mo_otros" class="form-control form-control-sm text-end calc-k-mo" value="0.00" oninput="calcularKGeneralMatMo()"></td>
                                                        <td><input type="text" id="k_monto_h" class="form-control form-control-sm text-end bg-light" value="$ 0,00" readonly></td>
                                                    </tr>
                                                    <!-- IVA (I) -->
                                                    <tr>
                                                        <td>IVA % (I)</td>
                                                        <td><input type="number" step="0.01" id="k_mo_iva" class="form-control form-control-sm text-end calc-k-mo" value="21.00" oninput="calcularKGeneralMatMo()"></td>
                                                        <td><input type="text" id="k_monto_i" class="form-control form-control-sm text-end bg-light" value="$ 0,00" readonly></td>
                                                    </tr>
                                                    <!-- PRECIO TOTAL VENTA -->
                                                    <tr class="table-dark text-white fw-bold">
                                                        <td>PRECIO TOTAL / OFERTA FINAL</td>
                                                        <td class="text-end fs-6" id="lbl_k_final_factor">1.0000</td>
                                                        <td><input type="text" id="k_monto_total" class="form-control form-control-sm text-end fw-bold bg-dark text-white border-0" value="$ 0,00" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 5: RESUMEN -->
                        <div class="tab-pane fade" id="tab-res" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-clipboard-data me-2"></i> Resumen de recursos del presupuesto</h6>
                                <span class="small text-muted">Suma de los insumos de todas las APU × cantidades del presupuesto (costos netos, sin K)</span>
                            </div>
                            <div id="resumenContenido"></div>
                        </div>

                        <!-- PESTAÑA 4: CONDICIONES -->
                        <div class="tab-pane fade" id="tab-cond" role="tabpanel">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-file-text me-2"></i> Condiciones Generales del Presupuesto</h6>
                                <div class="d-flex align-items-center gap-1">
                                    <select id="condiciones_base" class="form-select form-select-sm" style="min-width: 240px;" title="Bases cargadas en Presupuestos > Condiciones Generales">
                                        <option value="">-- Elegir una base --</option>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-primary text-nowrap" onclick="aplicarCondicionesBase()">
                                        <i class="bi bi-box-arrow-in-down me-1"></i> Aplicar
                                    </button>
                                </div>
                            </div>
                            <div id="condiciones_editor"></div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer py-2 bg-light">
                    <span id="lblEstadoGuardado" class="small text-muted me-auto"></span>
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-dark" id="btnGuardarPresupuesto">
                        <i class="bi bi-save me-1"></i> Guardar Presupuesto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: VER DETALLE DE UNA APU -->
<div class="modal fade" id="modalVerAPU" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2 bg-info bg-opacity-10">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-eye me-2"></i><span id="verApuTitulo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="verApuBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- Editor de texto con formato para las Condiciones Generales -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="/contable/modules/presupuestos/assets/js/condiciones_editor.js"></script>
<style>
#condiciones_editor .ql-editor { min-height: 300px; font-size: 0.95rem; }
</style>

<script>
const URL_AJAX = '/contable/modules/presupuestos/ajax/presupuestos.php';

let tablaPresupuestos;
let modalPresBS;
let clientesGlobal = [];
let obrasGlobal = [];
let coeficientesGlobal = [];
let tareasGlobal = [];
let indirectosGlobal = [];
let condicionesTextoGlobal = "";
let condicionesBasesGlobal = [];  // bases de Condiciones Generales (Presupuestos > Condiciones Generales)
let editorCondiciones;            // editor Quill de la pestaña Condiciones
let codigoSugeridoGlobal = "";
let fechaHoyGlobal = "";
let contadorRubros = 0;
let apuDetallesGlobal = {};      // insumos de cada APU, indexado por id de tarea
let cambiosSinGuardar = false;   // hay ediciones sin guardar en el modal
let cierreConfirmado = false;    // el usuario confirmó que quiere cerrar

// Instancias de TomSelect estáticos
let tsFiltroCliente, tsPresCliente, tsPresObra;

document.addEventListener("DOMContentLoaded", function() {
    modalPresBS = new bootstrap.Modal(document.getElementById('modalPresupuesto'));
    editorCondiciones = crearEditorCondiciones('#condiciones_editor', 'Escriba o ajuste las condiciones generales del presupuesto...');

    // Inicialización de TomSelect principales
    tsFiltroCliente = new TomSelect('#filtro_cliente', {
        create: false,
        placeholder: '-- Todos los Clientes --'
    });

    tsPresCliente = new TomSelect('#pres_cliente_id', {
        create: false,
        placeholder: '-- Seleccionar Cliente --',
        onChange: function(val) {
            actualizarObrasCombo(val);
        }
    });

    tsPresObra = new TomSelect('#pres_obra_id', {
        create: false,
        placeholder: '-- Seleccionar Obra --'
    });

    tablaPresupuestos = $('#tablaPresupuestos').DataTable({
        ajax: {
            url: URL_AJAX + '?accion=listar',
            data: function(d) {
                d.desde = $('#filtro_desde').val();
                d.hasta = $('#filtro_hasta').val();
                d.cliente_id = $('#filtro_cliente').val();
                d.estado = $('#filtro_estado').val();
            }
        },
        order: [[0, 'desc']],
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
        buttons: [
            { extend: 'excelHtml5', text: ' Excel', className: 'btn btn-success btn-sm' },
            { extend: 'print', text: ' Imprimir', className: 'btn btn-secondary btn-sm' }
        ],
        columns: [
            { data: 'codigo', className: 'fw-bold' },
            { data: 'fecha', render: function(d) { return d ? d.split('-').reverse().join('/') : '-'; } },
            { data: 'titulo', className: 'fw-semibold' },
            { data: 'cliente_nombre', render: function(d) { return d ? d : '<span class="text-muted">-</span>'; } },
            { 
                data: 'total_presupuestado', 
                render: function(d) {
                    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(d || 0);
                } 
            },
            { 
                data: 'estado',
                render: function(d) {
                    let map = { 'Borrador': 'bg-secondary', 'Enviado': 'bg-primary', 'Aprobado': 'bg-success', 'Rechazado': 'bg-danger', 'Archivado': 'bg-dark' };
                    return '<span class="badge ' + (map[d] || 'bg-secondary') + '">' + d + '</span>';
                }
            },
            { data: 'usuario_nombre', render: function(d) { return d ? '<small><i class="bi bi-person"></i> ' + d + '</small>' : '<small>Sistema</small>'; } },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: function(d) {
                    return '<div class="d-inline-flex gap-1">' +
                           '<button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editarPresupuesto(' + d.id + ')"><i class="bi bi-pencil"></i></button>' +
                           '<a class="btn btn-sm btn-outline-danger" title="Ver PDF (última versión guardada)" href="/contable/modules/presupuestos/pdf.php?id=' + d.id + '" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i></a>' +
                           '<button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarPresupuesto(' + d.id + ')"><i class="bi bi-trash"></i></button>' +
                           '</div>';
                }
            }
        ]
    });

    $.get(URL_AJAX + '?accion=cargar_maestros', function(res) {
        if (res.success) {
            clientesGlobal = res.clientes || [];
            obrasGlobal = res.obras || [];
            coeficientesGlobal = res.coeficientes || [];
            tareasGlobal = res.tareas || [];
            apuDetallesGlobal = res.apu_detalles || {};
            indirectosGlobal = res.indirectos || [];
            condicionesTextoGlobal = res.condiciones_texto || "";
            condicionesBasesGlobal = res.condiciones_bases || [];
            codigoSugeridoGlobal = res.codigo_sugerido || "";
            fechaHoyGlobal = res.fecha_hoy || "";

            tsFiltroCliente.clearOptions();
            tsPresCliente.clearOptions();

            tsFiltroCliente.addOption({ value: '', text: '-- Todos los Clientes --' });
            tsPresCliente.addOption({ value: '', text: '-- Seleccionar Cliente --' });

            clientesGlobal.forEach(function(c) {
                tsFiltroCliente.addOption({ value: c.id, text: c.nombre });
                tsPresCliente.addOption({ value: c.id, text: c.nombre });
            });

            actualizarObrasCombo('');

            let comboKBase = $('#pres_k_base').empty().append('<option value="1.0000" data-mat="1.0000" data-mo="1.0000">1.0000 - Sin Coeficiente (Neto)</option>');
            
            coeficientesGlobal.forEach(function(k) {
                comboKBase.append('<option value="' + k.id + '" data-mat="' + k.mat_k_resultante + '" data-mo="' + k.mo_k_resultante + '">' + k.nombre + ' (K Mat=' + k.mat_k_resultante + ' | K MO=' + k.mo_k_resultante + ')</option>');
            });

            $('#pres_codigo').val(codigoSugeridoGlobal);
            $('#pres_fecha').val(fechaHoyGlobal);
            renderizarCondicionesBases();
            setCondicionesEditor(editorCondiciones, condicionesTextoGlobal);

            renderizarIndirectosBase();
        } else {
            console.error("Error backend:", res.error);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error en el Servidor', res.error || 'No se pudieron cargar los datos maestros.', 'error');
            }
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error("Error AJAX 500:", xhr.responseText);
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error 500', 'Hubo un error interno en el servidor PHP.', 'error');
        }
    });

    $('#formPresupuesto').submit(function(e) {
        e.preventDefault();

        let payload = {
            id: $('#presupuesto_id').val(),
            codigo: $('#pres_codigo').val(),
            fecha: $('#pres_fecha').val(),
            cliente_id: $('#pres_cliente_id').val(),
            obra_id: $('#pres_obra_id').val(),
            titulo: $('#pres_titulo').val(),
            estado: $('#pres_estado').val(),
            coeficiente_k_base: $('#pres_k_base').val(),
            coeficiente_k_general: $('#pres_k_general').val(),
            condiciones: getCondicionesEditor(editorCondiciones),
            total_neto: parseMonedaFloat($('#lblSubtotalDirecto').text()),
            total_presupuestado: parseMonedaFloat($('#lblTotalPresupuesto').text()),
            rubros: []
        };

        $('.rubro-block').each(function() {
            let rubroObj = {
                numero: $(this).find('.rubro-num').text().replace('.', '').trim(),
                titulo: $(this).find('.rubro-titulo-input').val(),
                items: []
            };

            $(this).find('tr.item-row').each(function() {
                let selectElem = $(this).find('.item-tarea-select')[0];
                let tsInstance = selectElem ? selectElem.tomselect : null;
                
                let valSelect = tsInstance ? tsInstance.getValue() : $(selectElem).val();
                let tareaId = (!isNaN(valSelect) && valSelect !== '') ? parseInt(valSelect) : 0;
                
                let detalleText = $(this).find('.item-detalle-hidden').val() || valSelect || '';

                rubroObj.items.push({
                    item_num: $(this).find('.item-num').text(),
                    tarea_id: tareaId,
                    detalle: detalleText,
                    unidad: $(this).find('.item-unidad').val(),
                    cantidad: parseFloat($(this).find('.item-cant').val()) || 0,
                    precio_base: parseFloat($(this).find('.item-precio').val()) || 0,
                    precio_unitario: parseMonedaFloat($(this).find('.item-punit').val()),
                    subtotal: parseMonedaFloat($(this).find('.item-subtotal').val())
                });
            });

            payload.rubros.push(rubroObj);
            payload.indirectos_operarios = $('#pres_ind_operarios').val();
            payload.indirectos_dias = $('#pres_ind_dias').val();
            payload.indirectos_items = [];

            $('.item-ind-row').each(function() {
                payload.indirectos_items.push({
                    id: $(this).data('id'),
                    activo: $(this).find('.switch-ind-estado').is(':checked') ? 1 : 0,
                    afectacion: $(this).find('.ind-afec').val(),
                    cantidad: $(this).find('.ind-cant').val(),
                    unidad: $(this).find('.ind-unidad').val(),
                    unitario: $(this).find('.ind-unit').val()
                });
            });
        });

        const $btnGuardar = $('#btnGuardarPresupuesto');
        const htmlBtnGuardar = $btnGuardar.html();
        $btnGuardar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

        $.ajax({
            url: URL_AJAX + '?accion=guardar',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    // Importante: a partir de acá los guardados actualizan este mismo presupuesto
                    $('#presupuesto_id').val(res.id);
                    cambiosSinGuardar = false;

                    const hora = new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
                    $('#lblEstadoGuardado').html('<i class="bi bi-check-circle-fill text-success me-1"></i>Guardado a las ' + hora + ' (versión ' + res.version + ')');

                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Avance guardado', timer: 1800, showConfirmButton: false });
                    tablaPresupuestos.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.error || 'No se pudo guardar el presupuesto', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Ocurrió un problema de comunicación al guardar.', 'error');
            },
            complete: function() {
                $btnGuardar.prop('disabled', false).html(htmlBtnGuardar);
            }
        });
    });

    calcularKGeneralMatMo();
    initControlesPresupuesto();
});

function actualizarObrasCombo(cid) {
    if (!tsPresObra) return;
    tsPresObra.clearOptions();
    tsPresObra.addOption({ value: '', text: '-- Seleccionar Obra --' });

    obrasGlobal.filter(function(o) {
        return !cid || o.cliente_id == cid || !o.cliente_id;
    }).forEach(function(o) {
        tsPresObra.addOption({ value: o.id, text: o.nombre });
    });
}

function parseMonedaFloat(str) {
    if (!str) return 0;
    let clean = str.replace(/[^0-9,-]+/g, "").replace(/\./g, "").replace(',', '.');
    return parseFloat(clean) || 0;
}

function formatMoneda(val) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(val || 0);
}

function aplicarFiltros() { tablaPresupuestos.ajax.reload(); }
function limpiarFiltros() {
    $('#formFiltros')[0].reset();
    tsFiltroCliente.setValue('');
    tablaPresupuestos.ajax.reload();
}

function nuevoPresupuesto() {
    $('#formPresupuesto')[0].reset();
    $('#presupuesto_id').val('0');
    $('#pres_codigo').val(codigoSugeridoGlobal);
    $('#pres_fecha').val(fechaHoyGlobal);
    
    tsPresCliente.setValue('');
    tsPresObra.setValue('');

    $('#contenedorRubros').empty();
    contadorRubros = 0;
    
    renderizarIndirectosBase();
    seleccionarCondicionesBaseDefecto();
    setCondicionesEditor(editorCondiciones, condicionesTextoGlobal);
    agregarRubro();

    modalPresBS.show();
}

function editarPresupuesto(id) {
    $.get(URL_AJAX + '?accion=obtener_detalle&id=' + id, function(res) {
        if (res.success && res.detalle) {
            let p = res.detalle;
            $('#presupuesto_id').val(id);
            $('#pres_codigo').val(p.codigo || '');
            $('#pres_fecha').val(p.fecha || '');

            tsPresCliente.setValue(p.cliente_id || '');
            actualizarObrasCombo(p.cliente_id || '');
            tsPresObra.setValue(p.obra_id || '');

            $('#pres_estado').val(p.estado || 'Borrador');
            $('#pres_titulo').val(p.titulo || '');
            $('#pres_k_base').val(p.coeficiente_k_base || '1.0000');
            $('#pres_k_general').val(p.coeficiente_k_general || '1.0000');
            setCondicionesEditor(editorCondiciones, p.condiciones || condicionesTextoGlobal);
            $('#condiciones_base').val(''); // conserva el texto guardado; la base se elige solo si se quiere reemplazar
            
            // --- RESTAURACIÓN DE PARÁMETROS E INDIRECTOS ---
            $('#pres_ind_operarios').val(p.indirectos_operarios || 2);
            $('#pres_ind_dias').val(p.indirectos_dias || 30);
            renderizarIndirectosBase(p.indirectos_items || null);
            // ----------------------------------------------

            $('#contenedorRubros').empty();
            contadorRubros = 0;

            if (p.rubros && p.rubros.length > 0) {
                p.rubros.forEach(function(r) {
                    contadorRubros++;
                    let rNum = contadorRubros;
                    let html = '<div class="card border rubro-block" data-rubro-id="' + rNum + '">' +
                        '<div class="card-header bg-secondary bg-opacity-10 d-flex justify-content-between align-items-center py-1 px-2">' +
                            '<div class="d-flex align-items-center gap-2 w-75">' +
                                '<span class="fw-bold text-dark rubro-num">' + rNum + '.</span>' +
                                '<input type="text" class="form-control form-control-sm fw-bold rubro-titulo-input" value="' + (r.titulo || '') + '" placeholder="Nombre del Rubro" required>' +
                            '</div>' +
                            '<div>' +
                                '<button type="button" class="btn btn-xs btn-dark py-0" onclick="agregarItemARubro(' + rNum + ')">+ Tarea</button>' +
                                '<button type="button" class="btn btn-xs btn-outline-danger py-0 ms-1" onclick="eliminarRubro(this)"><i class="bi bi-trash"></i></button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="card-body p-2">' +
                            '<table class="table table-sm table-bordered align-middle mb-0">' +
                                '<thead class="table-light small text-center">' +
                                    '<tr>' +
                                        '<th style="width: 50px;">ITEM</th>' +
                                        '<th style="min-width: 320px;">DETALLE / DESCRIPCIÓN (APU)</th>' +
                                        '<th style="width: 70px;">UNID</th>' +
                                        '<th style="width: 80px;">CANT</th>' +
                                        '<th style="width: 110px;">PRECIO BASE</th>' +
                                        '<th style="width: 110px;">P x K BASE</th>' +
                                        '<th style="width: 120px;">SUBTOTAL</th>' +
                                        '<th style="width: 35px;"></th>' +
                                    '</tr>' +
                                '</thead>' +
                                '<tbody class="body-items-rubro"></tbody>' +
                            '</table>' +
                        '</div>' +
                    '</div>';
                    $('#contenedorRubros').append(html);

                    let card = $('.rubro-block[data-rubro-id="' + rNum + '"]');
                    let tbody = card.find('.body-items-rubro');

                    if (r.items && r.items.length > 0) {
                        r.items.forEach(function(it, idx) {
                            let itemCode = rNum + '.' + (idx + 1);
                            
                            let tr = `<tr class="item-row">
                                <td class="text-center fw-bold small item-num">${itemCode}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 apu-cell"><select class="form-select form-select-sm item-tarea-select"></select><button type="button" class="btn btn-sm btn-outline-info btn-ver-apu d-none text-nowrap" title="Ver detalle de la APU" onclick="verAPUDesdeFila(this)"><i class="bi bi-eye me-1"></i>Ver</button></div>
                                    <input type="hidden" class="item-detalle-hidden" value="${it.detalle || ''}">
                                </td>
                                <td><input type="text" class="form-control form-control-sm text-center item-unidad" value="${it.unidad || 'UN'}"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm text-end item-cant" value="${it.cantidad || 0}" oninput="recalcularMatrizPresupuesto()"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm text-end item-precio" value="${it.precio_base || 0}" oninput="recalcularMatrizPresupuesto()"></td>
                                <td><input type="text" class="form-control form-control-sm text-end item-punit bg-light" readonly value="$ 0,00"></td>
                                <td><input type="text" class="form-control form-control-sm text-end item-subtotal bg-light fw-bold" readonly value="$ 0,00"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs text-danger p-0" onclick="eliminarItemRow(this)"><i class="bi bi-x-circle-fill"></i></button>
                                </td>
                            </tr>`;
                            tbody.append(tr);

                            let selectElem = tbody.find('tr:last .item-tarea-select')[0];
                            inicializarTomSelectFila(selectElem, it.tarea_id, it.detalle);
                        });
                    }
                });
            } else {
                agregarRubro();
            }

            recalcularMatrizPresupuesto();
            modalPresBS.show();
        } else {
            Swal.fire('Error', 'No se pudieron recuperar los datos del presupuesto.', 'error');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'No se pudo establecer conexión con el servidor.', 'error');
    });
}

function agregarRubro() {
    contadorRubros++;
    let rNum = contadorRubros;
    let html = '<div class="card border rubro-block" data-rubro-id="' + rNum + '">' +
            '<div class="card-header bg-secondary bg-opacity-10 d-flex justify-content-between align-items-center py-1 px-2">' +
                '<div class="d-flex align-items-center gap-2 w-75">' +
                    '<span class="fw-bold text-dark rubro-num">' + rNum + '.</span>' +
                    '<input type="text" class="form-control form-control-sm fw-bold rubro-titulo-input" placeholder="Nombre del Rubro" required>' +
                '</div>' +
                '<div>' +
                    '<button type="button" class="btn btn-xs btn-dark py-0" onclick="agregarItemARubro(' + rNum + ')">+ Tarea</button>' +
                    '<button type="button" class="btn btn-xs btn-outline-danger py-0 ms-1" onclick="eliminarRubro(this)"><i class="bi bi-trash"></i></button>' +
                '</div>' +
            '</div>' +
            '<div class="card-body p-2">' +
                '<table class="table table-sm table-bordered align-middle mb-0">' +
                    '<thead class="table-light small text-center">' +
                        '<tr>' +
                            '<th style="width: 50px;">ITEM</th>' +
                            '<th style="min-width: 320px;">DETALLE / DESCRIPCIÓN (APU)</th>' +
                            '<th style="width: 70px;">UNID</th>' +
                            '<th style="width: 80px;">CANT</th>' +
                            '<th style="width: 110px;">PRECIO BASE</th>' +
                            '<th style="width: 110px;">P x K BASE</th>' +
                            '<th style="width: 120px;">SUBTOTAL</th>' +
                            '<th style="width: 35px;"></th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody class="body-items-rubro"></tbody>' +
                '</table>' +
            '</div>' +
        '</div>';
    $('#contenedorRubros').append(html);
    agregarItemARubro(rNum);
}

function agregarItemARubro(rNum) {
    let card = $('.rubro-block[data-rubro-id="' + rNum + '"]');
    let tbody = card.find('.body-items-rubro');
    let subItemIndex = tbody.find('tr').length + 1;
    let itemCode = rNum + '.' + subItemIndex;

    let tr = `
    <tr class="item-row">
        <td class="text-center fw-bold small item-num">${itemCode}</td>
        <td>
            <div class="d-flex align-items-center gap-1 apu-cell"><select class="form-select form-select-sm item-tarea-select"></select><button type="button" class="btn btn-sm btn-outline-info btn-ver-apu d-none text-nowrap" title="Ver detalle de la APU" onclick="verAPUDesdeFila(this)"><i class="bi bi-eye me-1"></i>Ver</button></div>
            <input type="hidden" class="item-detalle-hidden" value="">
        </td>
        <td><input type="text" class="form-control form-control-sm text-center item-unidad" value="UN"></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm text-end item-cant" value="1.00" oninput="recalcularMatrizPresupuesto()"></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm text-end item-precio" value="0.00" oninput="recalcularMatrizPresupuesto()"></td>
        <td><input type="text" class="form-control form-control-sm text-end item-punit bg-light" readonly value="$ 0,00"></td>
        <td><input type="text" class="form-control form-control-sm text-end item-subtotal bg-light fw-bold" readonly value="$ 0,00"></td>
        <td class="text-center">
            <button type="button" class="btn btn-xs text-danger p-0" onclick="eliminarItemRow(this)"><i class="bi bi-x-circle-fill"></i></button>
        </td>
    </tr>`;

    tbody.append(tr);

    let selectElem = tbody.find('tr:last .item-tarea-select')[0];
    inicializarTomSelectFila(selectElem, 0, '');

    recalcularMatrizPresupuesto();
}

function inicializarTomSelectFila(selectElem, tareaIdVal, detalleVal) {
    let ts = new TomSelect(selectElem, {
        create: true,
        persist: false,
        placeholder: 'Buscar APU o escribir detalle...',
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        options: [],
        onChange: function(value) {
            let row = selectElem.closest('tr');
            let option = this.options[value];

            if (option && option.costo !== undefined) {
                let unidad = option.unidad || 'UN';
                let costo  = option.costo || 0;
                let nombre = option.nombre || option.text;

                $(row).find('.item-unidad').val(unidad);
                $(row).find('.item-precio').val(costo);$(row).find('.item-detalle-hidden').val(nombre);
            } else {
                $(row).find('.item-detalle-hidden').val(value || '');
            }

            actualizarBotonVerAPU(selectElem, value);
            recalcularMatrizPresupuesto();
        }
    });

    // Agregar opciones maestras APU
    tareasGlobal.forEach(function(t) {
        ts.addOption({
            id: t.id,
            text: `[${t.codigo}] ${t.nombre}`,
            nombre: t.nombre,
            unidad: t.unidad,
            costo: t.costo_unitario_total,
            costoMat: t.costo_material_neto,
            costoMo: t.costo_mo_neto
        });
    });

    // Seleccionar la opción si ya existía
    if (tareaIdVal && tareaIdVal > 0) {
        ts.setValue(tareaIdVal);
    } else if (detalleVal) {
        ts.addOption({ id: detalleVal, text: detalleVal });
        ts.setValue(detalleVal);
    }

    actualizarBotonVerAPU(selectElem, ts.getValue());
}

function reindexarRubrosEItems() {
    $('.rubro-block').each(function(indexRubro) {
        let nuevoNumRubro = indexRubro + 1;
        $(this).attr('data-rubro-id', nuevoNumRubro);
        $(this).find('.rubro-num').text(nuevoNumRubro + '.');$(this).find('.btn-dark').attr('onclick', 'agregarItemARubro(' + nuevoNumRubro + ')');

        $(this).find('tr.item-row').each(function(indexItem) {
            let nuevoNumItem = nuevoNumRubro + '.' + (indexItem + 1);
            $(this).find('.item-num').text(nuevoNumItem);
        });
    });
}

function eliminarRubro(btn) {
    $(btn).closest('.rubro-block').remove();
    reindexarRubrosEItems();
    recalcularMatrizPresupuesto();
}

function eliminarItemRow(btn) {
    let card = $(btn).closest('.rubro-block');$(btn).closest('tr').remove();
    
    card.find('tr.item-row').each(function(indexItem) {
        let numRubro = card.find('.rubro-num').text().replace('.', '').trim();
        $(this).find('.item-num').text(numRubro + '.' + (indexItem + 1));
    });
    
    recalcularMatrizPresupuesto();
}

function actualizarKDesdePlantilla() {
    let opt = $('#pres_k_base option:selected');
    let kMat = parseFloat(opt.data('mat')) || 1;
    let kMo = parseFloat(opt.data('mo')) || 1;
    
    let kGeneralPonderado = ((kMat + kMo) / 2).toFixed(4);
    $('#pres_k_general').val(kGeneralPonderado);
    recalcularMatrizPresupuesto();
}

function recalcularMatrizPresupuesto() {
    let optKBase = $('#pres_k_base option:selected');
    let kMat = parseFloat(optKBase.data('mat')) || parseFloat(optKBase.attr('data-mat')) || 1;
    let kMo  = parseFloat(optKBase.data('mo'))  || parseFloat(optKBase.attr('data-mo'))  || 1;

    let subtotalDirectoSum = 0;

    $('.rubro-block').each(function() {
        $(this).find('tr.item-row').each(function() {
            let cant = parseFloat($(this).find('.item-cant').val()) || 0;
            let selectElem = $(this).find('.item-tarea-select')[0];
            let tsInstance = selectElem ? selectElem.tomselect : null;

            let costoMatNeto = 0;
            let costoMoNeto  = 0;

            if (tsInstance) {
                let val = tsInstance.getValue();
                let option = tsInstance.options[val];
                if (option) {
                    costoMatNeto = parseFloat(option.costoMat) || 0;
                    costoMoNeto  = parseFloat(option.costoMo) || 0;
                }
            }

            let pConK = 0;

            if (costoMatNeto > 0 || costoMoNeto > 0) {
                pConK = (costoMatNeto * kMat) + (costoMoNeto * kMo);
            } else {
                let precioBaseManual = parseFloat($(this).find('.item-precio').val()) || 0;
                pConK = precioBaseManual * kMat;
            }

            let subtotalItem = cant * pConK;

            $(this).find('.item-punit').val(formatMoneda(pConK));
            $(this).find('.item-subtotal').val(formatMoneda(subtotalItem));

            subtotalDirectoSum += subtotalItem;
        });
    });

    $('#lblSubtotalDirecto').text(formatMoneda(subtotalDirectoSum));

    // Recalcular montos de la pestaña K automáticamente al cambiar ítems o cantidades
    calcularKGeneralMatMo();
}

// Renderiza el listado de indirectos agrupado por categoría en formato Acordeón
function renderizarIndirectosBase(indirectosGuardados = null) {
    let contenedor = $('#acordeonIndirectosPresupuesto').empty();

    if (!indirectosGlobal || indirectosGlobal.length === 0) {
        contenedor.html('<div class="alert alert-warning text-center p-3 mb-0">No hay indirectos configurados en el sistema.</div>');
        $('#lblTotalIndirectos').text(formatMoneda(0));
        return;
    }

    // Agrupar ítems por categoría
    let agrupados = {};
    indirectosGlobal.forEach(function(i) {
        let catKey = (i.categoria_codigo || 'VARIOS') + ' - ' + (i.categoria_nombre || 'Sin Categoria');
        if (!agrupados[catKey]) agrupados[catKey] = [];
        
        // Si hay una versión guardada en el presupuesto, restaurar esos valores
        let itemGuardado = indirectosGuardados ? indirectosGuardados.find(g => g.id == i.id) : null;
        
        agrupados[catKey].push({
            id: i.id,
            item_codigo: i.item_codigo || '',
            descripcion: i.descripcion || '',
            observacion: i.observacion || '',
            unidad: itemGuardado ? itemGuardado.unidad : (i.unidad || 'GL'),
            cantidad: itemGuardado ? parseFloat(itemGuardado.cantidad) : parseFloat(i.cantidad_defecto || 1),
            afectacion: itemGuardado ? parseFloat(itemGuardado.afectacion) : parseFloat(i.afectacion_defecto || 1),
            unitario: itemGuardado ? parseFloat(itemGuardado.unitario) : parseFloat(i.unitario_defecto || 0),
            activo: itemGuardado ? parseInt(itemGuardado.activo) : parseInt(i.activo ?? 1),
            depende_operarios: parseInt(i.depende_operarios || 0),
            depende_tiempo: parseInt(i.depende_tiempo || 0)
        });
    });

    let indexCat = 0;
    for (let catNombre in agrupados) {
        indexCat++;
        let accId = 'pres_cat_collapse_' + indexCat;
        let items = agrupados[catNombre];

        let htmlCat = `
        <div class="accordion-item shadow-sm mb-2 border">
            <h2 class="accordion-header" id="heading_${accId}">
                <button class="accordion-button collapsed bg-light text-dark fw-bold py-2" type="button" data-bs-toggle="collapse" data-bs-target="#${accId}">
                    <div class="d-flex align-items-center justify-content-between w-100 me-3">
                        <span class="fs-6"><i class="bi bi-folder2-open me-2 text-secondary"></i>${catNombre}</span>
                        <span class="badge bg-white text-dark border px-2 py-1 fs-6">
                            Subtotal: <strong class="subtotal-cat-pres">$ 0,00</strong>
                        </span>
                    </div>
                </button>
            </h2>
            <div id="${accId}" class="accordion-collapse collapse" data-bs-parent="#acordeonIndirectosPresupuesto">
                <div class="accordion-body p-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered align-middle mb-0 text-center small">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 45px;">Estado</th>
                                    <th style="width: 60px;">Cód.</th>
                                    <th>Descripción</th>
                                    <th style="width: 90px;">Afectación</th>
                                    <th style="width: 85px;">Cantidad</th>
                                    <th style="width: 75px;">Unidad</th>
                                    <th style="width: 120px;">Unitario ($)</th>
                                    <th style="width: 130px;">Importe Total</th>
                                </tr>
                            </thead>
                            <tbody>`;

        items.forEach(function(it) {
            let checked = it.activo === 1 ? 'checked' : '';
            let rowClass = it.activo === 1 ? '' : 'table-secondary text-muted opacity-75';

            htmlCat += `
            <tr data-id="${it.id}" data-dep-op="${it.depende_operarios}" data-dep-tm="${it.depende_tiempo}" class="item-ind-row ${rowClass}">
                <td class="text-center">
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input switch-ind-estado" type="checkbox" role="switch" ${checked} onchange="toggleEstadoIndirectoFila(this)">
                    </div>
                </td>
                <td class="fw-bold">${it.item_codigo || '-'}</td>
                <td class="text-start">
                    ${it.descripcion}
                    ${it.depende_operarios ? '<span class="badge bg-warning text-dark ms-1"><i class="bi bi-person-fill"></i> Op</span>' : ''}
                    ${it.depende_tiempo ? '<span class="badge bg-secondary text-light ms-1"><i class="bi bi-calendar-event"></i> T</span>' : ''}
                </td>
                <td>
                    <select class="form-select form-select-sm ind-afec px-1" onchange="recalcularIndirectosPresupuesto()">
                        <option value="0.00" ${it.afectacion == 0 ? 'selected' : ''}>0%</option>
                        <option value="0.25" ${it.afectacion == 0.25 ? 'selected' : ''}>25%</option>
                        <option value="0.50" ${it.afectacion == 0.50 ? 'selected' : ''}>50%</option>
                        <option value="0.75" ${it.afectacion == 0.75 ? 'selected' : ''}>75%</option>
                        <option value="1.00" ${it.afectacion == 1.00 ? 'selected' : ''}>100%</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-end ind-cant" value="${it.cantidad}" oninput="recalcularIndirectosPresupuesto()">
                </td>
                <td>
                    <select class="form-select form-select-sm ind-unidad px-1">
                        <option value="GL" ${it.unidad == 'GL' ? 'selected' : ''}>GL</option>
                        <option value="MES" ${it.unidad == 'MES' ? 'selected' : ''}>MES</option>
                        <option value="DÍA" ${it.unidad == 'DÍA' ? 'selected' : ''}>DÍA</option>
                        <option value="HS" ${it.unidad == 'HS' ? 'selected' : ''}>HS</option>
                        <option value="UN" ${it.unidad == 'UN' ? 'selected' : ''}>UN</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-end ind-unit" value="${it.unitario}" oninput="recalcularIndirectosPresupuesto()">
                </td>
                <td class="text-end fw-bold ind-subtotal">${formatMoneda(0)}</td>
            </tr>`;
        });

        htmlCat += `</tbody></table></div></div></div></div>`;
        contenedor.append(htmlCat);
    }

    recalcularIndirectosPresupuesto();
}

// Prender / Apagar Ítem individual dentro de la modal
function toggleEstadoIndirectoFila(switchElem) {
    let tr = $(switchElem).closest('tr');
    if (switchElem.checked) {
        tr.removeClass('table-secondary text-muted opacity-75');
    } else {
        tr.addClass('table-secondary text-muted opacity-75');
    }
    recalcularIndirectosPresupuesto();
}

// Recálculo dinámico de subtotales por categoría y Total General
function recalcularIndirectosPresupuesto() {
    let cantOperarios = parseFloat($('#pres_ind_operarios').val()) || 1;
    let diasObra = parseFloat($('#pres_ind_dias').val()) || 1;
    let totalGeneral = 0;

    $('.accordion-item').each(function() {
        let subtotalCat = 0;

        $(this).find('tr.item-ind-row').each(function() {
            let activo = $(this).find('.switch-ind-estado').is(':checked') ? 1 : 0;
            let depOp = parseInt($(this).data('dep-op')) === 1 ? cantOperarios : 1;
            let depTm = parseInt($(this).data('dep-tm')) === 1 ? diasObra : 1;

            let cant = parseFloat($(this).find('.ind-cant').val()) || 0;
            let afec = parseFloat($(this).find('.ind-afec').val()) || 0;
            let unit = parseFloat($(this).find('.ind-unit').val()) || 0;

            let importe = cant * afec * unit * depOp * depTm;

            $(this).find('.ind-subtotal').text(formatMoneda(importe));

            if (activo === 1) {
                subtotalCat += importe;
            }
        });

        $(this).find('.subtotal-cat-pres').text(formatMoneda(subtotalCat));
        totalGeneral += subtotalCat;
    });

    $('#lblTotalIndirectos').text(formatMoneda(totalGeneral));
    
    // Impactar recálculo sobre la Pestaña Coeficiente K
    if (typeof calcularKGeneralMatMo === 'function') {
        calcularKGeneralMatMo();
    }
}

function recalcularSubtotalIndirecto(input) {
    let tr = $(input).closest('tr');
    let cant = parseFloat(tr.find('.ind-cant').val()) || 0;
    let unit = parseFloat(tr.find('.ind-unit').val()) || 0;
    tr.find('.lbl-subtotal-ind').text(formatMoneda(cant * unit));

    recalcularTotalIndirectos();
}

function recalcularTotalIndirectos() {
    let totalIndirectos = 0;
    $('#bodyIndirectosPresupuesto tr').each(function() {
        let cant = parseFloat($(this).find('.ind-cant').val()) || 0;
        let unit = parseFloat($(this).find('.ind-unit').val()) || 0;
        totalIndirectos += (cant * unit);
    });

    $('#lblTotalIndirectos').text(formatMoneda(totalIndirectos));
    
    // Recalcular K cuando cambian los indirectos
    calcularKGeneralMatMo();
}

// =====================================================================
// CONDICIONES GENERALES: desplegable de bases
// =====================================================================
function renderizarCondicionesBases() {
    const combo = $('#condiciones_base').empty()
        .append('<option value="">' + (condicionesBasesGlobal.length ? '-- Elegir una base --' : '-- No hay bases cargadas --') + '</option>');
    condicionesBasesGlobal.forEach(function(b) {
        combo.append($('<option>').val(b.id).text(b.nombre + (b.por_defecto ? ' (por defecto)' : '')));
    });
    seleccionarCondicionesBaseDefecto();
}

function seleccionarCondicionesBaseDefecto() {
    const def = condicionesBasesGlobal.find(function(b) { return b.por_defecto; });
    $('#condiciones_base').val(def ? String(def.id) : '');
}

function aplicarCondicionesBase() {
    const id = $('#condiciones_base').val();
    const base = condicionesBasesGlobal.find(function(b) { return String(b.id) === String(id); });
    if (!base) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Elegí una base', 'Seleccioná una base del desplegable para aplicarla.', 'info');
        }
        return;
    }

    const aplicar = function() {
        // Se copia el texto: los cambios en el presupuesto no modifican la base
        setCondicionesEditor(editorCondiciones, base.contenido);
        cambiosSinGuardar = true;
    };

    if (condicionesEditorVacio(editorCondiciones)) {
        aplicar();
        return;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Reemplazar las condiciones?',
            text: 'El texto actual de este presupuesto se reemplaza por la base "' + base.nombre + '".',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, reemplazar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) { if (r.isConfirmed) aplicar(); });
    } else if (confirm('¿Reemplazar el texto actual por la base "' + base.nombre + '"?')) {
        aplicar();
    }
}

function calcularKGeneralMatMo() {
    // 1. Obtener Costo Neto (A) desde la Pestaña 1
    let montoA = parseMonedaFloat($('#lblSubtotalDirecto').text()) || 0;
    $('#k_monto_a').val(formatMoneda(montoA));

    // 2. Obtener Total de Indirectos desde la Pestaña 2
    let totalIndirectos = parseMonedaFloat($('#lblTotalIndirectos').text()) || 0;

    // 3. Cálculo automático del Porcentaje (B): (Total Indirectos / Costo Neto A) * 100
    let pctB = (montoA > 0) ? (totalIndirectos / montoA) * 100 : 0;
    $('#k_mo_ind').val(pctB.toFixed(2));

    // Porcentaje C (Beneficio)
    let pctC = parseFloat($('#k_mo_ben').val()) || 0;
    
    // Montos B y C
    let montoB = totalIndirectos; // Equivale a montoA * (pctB / 100)
    let montoC = montoA * (pctC / 100);
    
    $('#k_monto_b').val(formatMoneda(montoB));
    $('#k_monto_c').val(formatMoneda(montoC));

    // Subtotal D
    let factorD = 1 + (pctB + pctC) / 100;
    let montoD = montoA + montoB + montoC;
    $('#lbl_mo_sub_d').text(factorD.toFixed(4));
    $('#k_monto_d').val(formatMoneda(montoD));

    // Financiero E
    let pctE = parseFloat($('#k_mo_fin').val()) || 0;
    let montoE = montoD * (pctE / 100);
    $('#k_monto_e').val(formatMoneda(montoE));

    // Subtotal F
    let factorF = factorD * (1 + pctE / 100);
    let montoF = montoD + montoE;
    $('#lbl_mo_sub_f').text(factorF.toFixed(4));
    $('#k_monto_f').val(formatMoneda(montoF));

    // Impuestos G, H, I calculados sobre F
    let pctG = parseFloat($('#k_mo_iibb').val()) || 0;
    let pctH = parseFloat($('#k_mo_otros').val()) || 0;
    let pctI = parseFloat($('#k_mo_iva').val()) || 0;

    let montoG = montoF * (pctG / 100);
    let montoH = montoF * (pctH / 100);
    let montoI = montoF * (pctI / 100);

    $('#k_monto_g').val(formatMoneda(montoG));
    $('#k_monto_h').val(formatMoneda(montoH));
    $('#k_monto_i').val(formatMoneda(montoI));

    // Factor K Final y Monto Total
    let factorKFinal = factorF * (1 + (pctG + pctH + pctI) / 100);
    let montoTotalFinal = montoF + montoG + montoH + montoI;

    $('#lbl_k_mo_title').text('K = ' + factorKFinal.toFixed(4));
    if ($('#lbl_k_final_factor').length) {
        $('#lbl_k_final_factor').text(factorKFinal.toFixed(4));
    }
    $('#k_monto_total').val(formatMoneda(montoTotalFinal));

    // Actualizar el K General Final del presupuesto
    $('#pres_k_general').val(factorKFinal.toFixed(4));
    $('#lblTotalPresupuesto').text(formatMoneda(montoTotalFinal));
}

function eliminarPresupuesto(id) {
    Swal.fire({
        title: '¿Eliminar Presupuesto?',
        text: 'Esta acción removerá el presupuesto seleccionado.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function(res) {
        if (res.isConfirmed) {
            $.post(URL_AJAX + '?accion=eliminar', { id: id }, function(resp) {
                if(resp.success) {
                    tablaPresupuestos.ajax.reload(null, false);
                    Swal.fire('Eliminado', 'El presupuesto fue removido.', 'success');
                } else {
                    Swal.fire('Error', 'No se pudo eliminar el presupuesto.', 'error');
                }
            }, 'json');
        }
    });
}

// =====================================================================
// CONTROL DE CIERRE Y GUARDADO PARCIAL DEL MODAL
// =====================================================================
function initControlesPresupuesto() {
    const modalEl = document.getElementById('modalPresupuesto');

    // Cualquier edición dentro del modal marca cambios pendientes
    $(modalEl).on('input change', function() { cambiosSinGuardar = true; });

    // Al terminar de abrir, lo cargado por código no cuenta como "cambio"
    modalEl.addEventListener('shown.bs.modal', function() {
        cambiosSinGuardar = false;
        $('#lblEstadoGuardado').empty();
    });

    // Cualquier intento de cierre (X, Cerrar, ESC) pide confirmación
    modalEl.addEventListener('hide.bs.modal', function(e) {
        if (cierreConfirmado) { cierreConfirmado = false; return; }
        e.preventDefault();
        confirmarCierrePresupuesto();
    });

    // Al cerrar, volver a la primera pestaña para la próxima apertura
    modalEl.addEventListener('hidden.bs.modal', function() {
        cambiosSinGuardar = false;
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-pres-btn')).show();
    });

    // Aviso del navegador si intenta recargar o salir con cambios sin guardar
    window.addEventListener('beforeunload', function(e) {
        if (cambiosSinGuardar && $('#modalPresupuesto').hasClass('show')) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Modal "Ver APU" apilado sobre el modal del presupuesto
    const apuEl = document.getElementById('modalVerAPU');
    apuEl.addEventListener('shown.bs.modal', function() {
        $('.modal-backdrop').last().css('z-index', 1060);
    });
    apuEl.addEventListener('hidden.bs.modal', function() {
        // Bootstrap quita "modal-open" del body al cerrar el segundo modal
        if ($('#modalPresupuesto').hasClass('show')) $('body').addClass('modal-open');
    });

    // La pestaña Resumen se recalcula cada vez que se abre
    document.getElementById('tab-res-btn').addEventListener('shown.bs.tab', actualizarResumenPresupuesto);
}

function confirmarCierrePresupuesto() {
    Swal.fire({
        icon: 'warning',
        title: cambiosSinGuardar ? '¿Cerrar sin guardar?' : '¿Cerrar el presupuesto?',
        text: cambiosSinGuardar
            ? 'Hay cambios sin guardar que se van a perder.'
            : 'Podés volver a abrirlo desde el listado.',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar',
        cancelButtonText: 'Seguir editando',
        reverseButtons: true,
        focusCancel: true
    }).then(function(r) {
        if (r.isConfirmed) {
            cierreConfirmado = true;
            modalPresBS.hide();
        }
    });
}

// =====================================================================
// UTILIDADES
// =====================================================================
function escapeHtml(s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

function formatCantidad(n) {
    return new Intl.NumberFormat('es-AR', { maximumFractionDigits: 2 }).format(n || 0);
}

const TIPOS_APU = [
    { key: 'MATERIAL',    titulo: 'Materiales',   icono: 'bi-box-seam', color: 'primary' },
    { key: 'MO',          titulo: 'Mano de Obra', icono: 'bi-people',   color: 'success' },
    { key: 'EQUIPO',      titulo: 'Equipos',      icono: 'bi-truck',    color: 'warning' },
    { key: 'SUBCONTRATO', titulo: 'Subcontratos', icono: 'bi-diagram-2', color: 'secondary' }
];

// =====================================================================
// BOTÓN "VER" DE LA APU + MODAL DE DETALLE
// =====================================================================
function actualizarBotonVerAPU(selectElem, value) {
    const esApu = value !== '' && value !== null && value !== undefined &&
                  tareasGlobal.some(function(t) { return String(t.id) === String(value); });
    $(selectElem).closest('td').find('.btn-ver-apu').toggleClass('d-none', !esApu);
}

function verAPUDesdeFila(btn) {
    const fila = $(btn).closest('tr');
    const sel = fila.find('.item-tarea-select')[0];
    const id = (sel && sel.tomselect) ? sel.tomselect.getValue() : '';
    const cant = parseFloat(fila.find('.item-cant').val()) || 0;
    verAPU(id, cant);
}

function verAPU(tareaId, cantidadItem) {
    const t = tareasGlobal.find(function(x) { return String(x.id) === String(tareaId); });
    if (!t) return;

    const detalles = apuDetallesGlobal[t.id] || [];
    $('#verApuTitulo').text('[' + (t.codigo || '-') + '] ' + t.nombre);

    let html = '<div class="row g-2 mb-2">' +
        '<div class="col-md-3"><div class="border rounded p-2 bg-light h-100"><div class="small text-muted">Unidad</div><div class="fw-bold">' + escapeHtml(t.unidad || '-') + '</div></div></div>' +
        '<div class="col-md-3"><div class="border rounded p-2 bg-light h-100"><div class="small text-muted">Costo unitario neto (APU)</div><div class="fw-bold">' + formatMoneda(t.costo_unitario_total) + '</div></div></div>' +
        '<div class="col-md-3"><div class="border rounded p-2 bg-light h-100"><div class="small text-muted">Cantidad en este presupuesto</div><div class="fw-bold">' + formatCantidad(cantidadItem) + ' ' + escapeHtml(t.unidad || '') + '</div></div></div>' +
        '<div class="col-md-3"><div class="border rounded p-2 bg-light h-100"><div class="small text-muted">Costo neto total (x cantidad)</div><div class="fw-bold text-success">' + formatMoneda((t.costo_unitario_total || 0) * (cantidadItem || 0)) + '</div></div></div>' +
        '</div>';

    if (t.observaciones) {
        html += '<div class="alert alert-light border small py-2 mb-2" style="white-space: pre-line;">' + escapeHtml(t.observaciones) + '</div>';
    }

    if (detalles.length === 0) {
        html += '<div class="alert alert-warning mb-0">Esta APU no tiene insumos cargados; solo tiene el costo unitario total.</div>';
    }

    TIPOS_APU.forEach(function(tipo) {
        const filas = detalles.filter(function(d) { return d.tipo === tipo.key; });
        if (filas.length === 0) return;

        let subtotalTipo = 0;
        let rowsHtml = '';
        filas.forEach(function(d) {
            const sub = d.cantidad * d.precio_unitario;
            subtotalTipo += sub;
            rowsHtml += '<tr>' +
                '<td>' + escapeHtml(d.descripcion) +
                    (d.observaciones ? '<div class="text-muted" style="font-size: 0.75rem;">' + escapeHtml(d.observaciones) + '</div>' : '') + '</td>' +
                '<td class="text-center text-nowrap"><span class="badge bg-light text-muted border fw-normal">' + escapeHtml(d.fecha_actualizacion || '-') + '</span></td>' +
                '<td class="text-center">' + escapeHtml(d.unidad) + '</td>' +
                '<td class="text-end">' + formatCantidad(d.cantidad) + '</td>' +
                '<td class="text-end">' + formatMoneda(d.precio_unitario) + '</td>' +
                '<td class="text-end">' + formatMoneda(sub) + '</td>' +
                '<td class="text-end fw-semibold">' + formatMoneda(sub * (cantidadItem || 0)) + '</td>' +
                '</tr>';
        });

        html += '<h6 class="fw-bold mt-3 mb-1 text-' + tipo.color + '"><i class="bi ' + tipo.icono + ' me-1"></i>' + tipo.titulo + '</h6>' +
            '<table class="table table-sm table-bordered align-middle small mb-0">' +
            '<thead class="table-light"><tr>' +
                '<th>Descripción</th>' +
                '<th class="text-center" style="width: 100px;">Fecha act.</th>' +
                '<th class="text-center" style="width: 70px;">Unid.</th>' +
                '<th class="text-end" style="width: 100px;">Cant.</th>' +
                '<th class="text-end" style="width: 130px;">P. Unit.</th>' +
                '<th class="text-end" style="width: 130px;">Subtotal</th>' +
                '<th class="text-end" style="width: 150px;">Total x cant.</th>' +
            '</tr></thead>' +
            '<tbody>' + rowsHtml + '</tbody>' +
            '<tfoot><tr class="table-light fw-bold">' +
                '<td colspan="5" class="text-end">Subtotal ' + tipo.titulo + '</td>' +
                '<td class="text-end">' + formatMoneda(subtotalTipo) + '</td>' +
                '<td class="text-end">' + formatMoneda(subtotalTipo * (cantidadItem || 0)) + '</td>' +
            '</tr></tfoot></table>';
    });

    $('#verApuBody').html(html);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerAPU')).show();
}

// =====================================================================
// PESTAÑA RESUMEN: horas, materiales, equipos y subcontratos totales
// =====================================================================
function totalesPorUnidad(grupo) {
    const t = {};
    Object.keys(grupo).forEach(function(k) {
        const g = grupo[k];
        t[g.unidad] = (t[g.unidad] || 0) + g.cantidad;
    });
    const txt = Object.keys(t).map(function(u) { return (formatCantidad(t[u]) + ' ' + u).trim(); }).join(' · ');
    return txt || '0';
}

function costoDeGrupo(grupo) {
    return Object.keys(grupo).reduce(function(acc, k) { return acc + grupo[k].costo; }, 0);
}

function tarjetaResumen(titulo, valor, sub, color, icono) {
    return '<div class="col-md-3"><div class="card border-' + color + ' h-100"><div class="card-body p-2 text-center">' +
        '<div class="small text-muted fw-semibold text-uppercase"><i class="bi ' + icono + ' me-1"></i>' + titulo + '</div>' +
        '<div class="fs-5 fw-bold text-' + color + '">' + valor + '</div>' +
        '<div class="small text-muted">' + sub + '</div>' +
        '</div></div></div>';
}

function tablaResumen(tipo, grupo) {
    const claves = Object.keys(grupo).sort(function(a, b) {
        return grupo[a].descripcion.localeCompare(grupo[b].descripcion, 'es');
    });
    if (claves.length === 0) return '';

    let rows = '';
    claves.forEach(function(k) {
        const g = grupo[k];
        rows += '<tr><td>' + escapeHtml(g.descripcion) + '</td>' +
            '<td class="text-center">' + escapeHtml(g.unidad) + '</td>' +
            '<td class="text-end fw-semibold">' + formatCantidad(g.cantidad) + '</td>' +
            '<td class="text-end">' + formatMoneda(g.costo) + '</td></tr>';
    });

    return '<div class="col-lg-6"><h6 class="fw-bold mb-1 text-' + tipo.color + '"><i class="bi ' + tipo.icono + ' me-1"></i>' + tipo.titulo + '</h6>' +
        '<table class="table table-sm table-bordered align-middle small mb-0">' +
        '<thead class="table-light"><tr><th>Descripción</th><th class="text-center" style="width: 70px;">Unid.</th>' +
        '<th class="text-end" style="width: 110px;">Cant. total</th><th class="text-end" style="width: 140px;">Costo neto</th></tr></thead>' +
        '<tbody>' + rows + '</tbody>' +
        '<tfoot><tr class="table-light fw-bold"><td colspan="2" class="text-end">Total</td>' +
        '<td class="text-end">' + escapeHtml(totalesPorUnidad(grupo)) + '</td>' +
        '<td class="text-end">' + formatMoneda(costoDeGrupo(grupo)) + '</td></tr></tfoot></table></div>';
}

function actualizarResumenPresupuesto() {
    const acum = { MATERIAL: {}, MO: {}, EQUIPO: {}, SUBCONTRATO: {} };
    let itemsConApu = 0;
    let itemsSinApu = 0;

    $('.rubro-block tr.item-row').each(function() {
        const cant = parseFloat($(this).find('.item-cant').val()) || 0;
        const sel = $(this).find('.item-tarea-select')[0];
        const val = (sel && sel.tomselect) ? sel.tomselect.getValue() : '';
        if (!val) return; // fila vacía

        const detalles = apuDetallesGlobal[val];
        if (!detalles || detalles.length === 0) { itemsSinApu++; return; }
        itemsConApu++;

        detalles.forEach(function(d) {
            const grupo = acum[d.tipo];
            if (!grupo) return;
            const unidad = (d.unidad || '').toUpperCase();
            const clave = (d.descripcion || '').trim().toUpperCase() + '|' + unidad;
            if (!grupo[clave]) grupo[clave] = { descripcion: (d.descripcion || '').trim(), unidad: unidad, cantidad: 0, costo: 0 };
            grupo[clave].cantidad += d.cantidad * cant;
            grupo[clave].costo += d.cantidad * d.precio_unitario * cant;
        });
    });

    if (itemsConApu === 0) {
        $('#resumenContenido').html('<div class="alert alert-info mb-0">Todavía no hay tareas APU seleccionadas. Elegí APU en la pestaña Presupuesto para ver el resumen de horas, materiales y equipos.</div>');
        return;
    }

    const costoTotal = costoDeGrupo(acum.MATERIAL) + costoDeGrupo(acum.MO) + costoDeGrupo(acum.EQUIPO) + costoDeGrupo(acum.SUBCONTRATO);

    let html = '';
    if (itemsSinApu > 0) {
        html += '<div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>' +
            itemsSinApu + ' ítem(s) no tienen una APU con insumos cargados (detalle libre o APU sin desglose), por lo que no se incluyen en este resumen.</div>';
    }

    html += '<div class="row g-2 mb-3">' +
        tarjetaResumen('Mano de obra', escapeHtml(totalesPorUnidad(acum.MO)), formatMoneda(costoDeGrupo(acum.MO)) + ' neto', 'success', 'bi-people') +
        tarjetaResumen('Materiales', Object.keys(acum.MATERIAL).length + ' insumos', formatMoneda(costoDeGrupo(acum.MATERIAL)) + ' neto', 'primary', 'bi-box-seam') +
        tarjetaResumen('Equipos', escapeHtml(totalesPorUnidad(acum.EQUIPO)), formatMoneda(costoDeGrupo(acum.EQUIPO)) + ' neto', 'warning', 'bi-truck') +
        tarjetaResumen('Subcontratos', Object.keys(acum.SUBCONTRATO).length + ' ítems', formatMoneda(costoDeGrupo(acum.SUBCONTRATO)) + ' neto', 'secondary', 'bi-diagram-2') +
        '</div>';

    html += '<div class="row g-3">' +
        tablaResumen(TIPOS_APU[1], acum.MO) +
        tablaResumen(TIPOS_APU[0], acum.MATERIAL) +
        tablaResumen(TIPOS_APU[2], acum.EQUIPO) +
        tablaResumen(TIPOS_APU[3], acum.SUBCONTRATO) +
        '</div>';

    html += '<div class="text-end mt-3"><span class="text-muted small me-2">Costo directo neto (sin K):</span>' +
        '<span class="fw-bold fs-5">' + formatMoneda(costoTotal) + '</span></div>';

    $('#resumenContenido').html(html);
}
</script>