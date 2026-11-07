<?php
include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<div class="content">

    <!-- CABECERA DEL MÓDULO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-cone-striped text-secondary me-2"></i> Gestión de Obras
        </h4>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal('NUEVO')">
            <i class="bi bi-plus-circle me-2"></i> Nueva Obra
        </button>
    </div>

    <!-- Pestañas de Navegación de Obras -->
    <ul class="nav nav-tabs mb-3" id="tabFacturas" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold text-dark" id="cobrar-tab" data-bs-toggle="tab" data-bs-target="#cobrar" type="button" role="tab" onclick="filtrarPestaña('POR_COBRAR')">
                <i class="bi bi-wallet2 me-1 text-secondary"></i> Por Cobrar 
                <span class="badge bg-secondary ms-1" id="cant-cobrar">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-dark" id="pagadas-tab" data-bs-toggle="tab" data-bs-target="#pagadas" type="button" role="tab" onclick="filtrarPestaña('PAGADAS')">
                <i class="bi bi-check-circle me-1 text-secondary"></i> Pagadas 
                <span class="badge bg-secondary ms-1" id="cant-pagadas">0</span>
            </button>
        </li>
    </ul>

    <!-- Contenedor de la Tabla -->
    <div class="card shadow-sm border-0 p-3">
        <div class="table-responsive">
            <table id="tablaObras" class="table table-bordered table-striped w-100 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre de la Obra</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>N° OC</th>
                        <th>Inicio / Fin</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- MODAL UNIFICADO (NUEVO / VER / EDITAR) -->
<div class="modal fade" id="modalObra" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalObraLabel"><i class="bi bi-cone-striped me-2"></i>Obra</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formObra" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">

                    <div class="row">
                        <!-- Fila 1 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nombre de la Obra</label>
                            <input name="nombre" id="nombre" class="form-control" required placeholder="Ej: Remodelación Oficinas">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Dirección de la Obra</label>
                            <input name="direccion" id="direccion" class="form-control" placeholder="Ej: Av. Santa Fe 1234">
                        </div>

                        <!-- Fila 2 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Cliente Asignado</label>
                            <select name="cliente_id" id="cliente_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Responsable a Cargo</label>
                            <input name="responsable" id="responsable" class="form-control" placeholder="Ej: Ing. Carlos Gómez">
                        </div>

                        <!-- Fila 3 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tipo de Obra</label>
                            <select name="tipo_obra" id="tipo_obra" class="form-select" required>
                                <option value="PRIVADA">🔒 PRIVADA</option>
                                <option value="PUBLICA">🏛️ PUBLICA</option>
                                <option value="PARTICULAR">👤 PARTICULAR</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">N° de OC</label>
                            <input name="nro_oc" id="nro_oc" class="form-control" placeholder="Ej: OC-2026-88">
                        </div>

                        <!-- Fila 4 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Fecha de Fin (Estimada/Real)</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control">
                        </div>

                        <!-- Fila 5 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Estado Operativo</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="ACTIVA">🟢 ACTIVA</option>
                                <option value="FINALIZADA">🔴 FINALIZADA</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Facturación</label>
                            <select name="facturacion" id="facturacion" class="form-select" required>
                                <option value="Por Cobrar">⏳ Por Cobrar</option>
                                <option value="Pagadas">💵 Pagadas</option>
                            </select>
                        </div>

                        <!-- Fila 6: Detalles -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Detalles / Memoria Descriptiva</label>
                            <textarea name="detalle" id="detalle" class="form-control" rows="2" placeholder="Notas sobre el alcance del proyecto..."></textarea>
                        </div>

                        <hr class="my-3">

                        <!-- SECCIÓN ARCHIVOS DE CARGA -->
                        <div class="col-md-6 mb-3" id="wrapperPresupuesto">
                            <label class="form-label fw-semibold">Presupuesto Aceptado (PDF/Doc)</label>
                            <input type="file" name="presupuesto_archivo" id="presupuesto_archivo" class="form-control">
                            <div id="verPresupuestoActual" class="mt-2"></div>
                        </div>

                        <div class="col-md-6 mb-3" id="wrapperRepositorio">
                            <label class="form-label fw-semibold">Archivos Adjuntos (Repositorio Múltiple)</label>
                            <input type="file" name="archivos_repositorio[]" id="archivos_repositorio" class="form-control" multiple>
                            <small class="text-muted">Podés seleccionar varios archivos juntos.</small>
                        </div>

                        <!-- PESTAÑAS DE VINCULACIONES Y ARCHIVOS EN LA OBRA -->
                        <div class="col-12 mt-2" id="seccionListadosObra">
                            <ul class="nav nav-tabs" id="tabModulosObra" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active fw-bold text-dark py-1 px-3" id="tab-repo-btn" data-bs-toggle="tab" data-bs-target="#tab-repo" type="button" role="tab">
                                        <i class="bi bi-folder2-open me-1 text-primary"></i> Documentos (<span id="cantDocRepo">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-dark py-1 px-3" id="tab-ventas-btn" data-bs-toggle="tab" data-bs-target="#tab-ventas" type="button" role="tab">
                                        <i class="bi bi-receipt me-1 text-success"></i> Facturas Venta (<span id="cantFacVentas">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-dark py-1 px-3" id="tab-gastos-btn" data-bs-toggle="tab" data-bs-target="#tab-gastos" type="button" role="tab">
                                        <i class="bi bi-cart-dash me-1 text-danger"></i> Gastos / Compras (<span id="cantFacGastos">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-dark py-1 px-3" id="tab-polizas-btn" data-bs-toggle="tab" data-bs-target="#tab-polizas" type="button" role="tab">
                                        <i class="bi bi-shield-check me-1 text-warning"></i> Pólizas de Caución (<span id="cantPolizas">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-dark py-1 px-3" id="tab-presupuestos-btn" data-bs-toggle="tab" data-bs-target="#tab-presupuestos" type="button" role="tab">
                                        <i class="bi bi-file-earmark-spreadsheet me-1 text-info"></i> Presupuestos (<span id="cantPresupuestosObra">0</span>)
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content border border-top-0 p-2 rounded-bottom bg-white" id="tabModulosObraContent">
                                <!-- TAB REPOSITORIO -->
                                <div class="tab-pane fade show active" id="tab-repo" role="tabpanel">
                                    <ul class="list-group list-group-flush" id="listaArchivosObra">
                                        <!-- Dinámico -->
                                    </ul>
                                </div>

                                <!-- TAB FACTURAS VENTA -->
                                <div class="tab-pane fade" id="tab-ventas" role="tabpanel">
                                    <ul class="list-group list-group-flush" id="listaFacturasObra">
                                        <!-- Dinámico -->
                                    </ul>
                                </div>

                                <!-- TAB GASTOS / COMPRAS -->
                                <div class="tab-pane fade" id="tab-gastos" role="tabpanel">
                                    <ul class="list-group list-group-flush" id="listaGastosObra">
                                        <!-- Dinámico -->
                                    </ul>
                                </div>

                                <!-- TAB PÓLIZAS DE CAUCIÓN -->
                                <div class="tab-pane fade" id="tab-polizas" role="tabpanel">
                                    <div id="contenidoPolizasObra">
                                        <!-- Dinámico -->
                                    </div>
                                </div>

                                <!-- TAB PRESUPUESTOS VINCULADOS (módulo Presupuestos) -->
                                <div class="tab-pane fade" id="tab-presupuestos" role="tabpanel">
                                    <ul class="list-group list-group-flush" id="listaPresupuestosObra">
                                        <!-- Dinámico -->
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- RESUMEN FINANCIERO DISCRETO Y COMPACTO (UBICADO ABAJO) -->
                        <div id="seccionResumenFinanciero" class="col-12 d-none mt-3">
                            <div class="p-2 bg-light rounded border shadow-sm">
                                <div class="row text-center g-2 align-items-center">
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded border-start border-success border-3">
                                            <span class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.75rem;">Total Ventas</span>
                                            <span class="fw-bold text-success fs-6" id="lblTotalVentas">$ 0,00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded border-start border-danger border-3">
                                            <span class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.75rem;">Total Gastos</span>
                                            <span class="fw-bold text-danger fs-6" id="lblTotalGastos">$ 0,00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded border-start border-primary border-3" id="boxMargenNeto">
                                            <span class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.75rem;">Margen / Balance</span>
                                            <span class="fw-bold fs-6" id="lblMargenNeto">$ 0,00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark" id="btnGuardarObra">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: CARGA / EDICIÓN DE PÓLIZA DE CAUCIÓN (apilado sobre el modal de la obra) -->
<div class="modal fade" id="modalPoliza" data-bs-backdrop="static" tabindex="-1" style="z-index: 1065;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold" id="tituloModalPoliza"><i class="bi bi-shield-check me-2"></i>Póliza de Caución</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPoliza" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="poliza_id" id="poliza_id">
                    <input type="hidden" name="poliza_obra_id" id="poliza_obra_id">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tipo de Póliza</label>
                            <select name="tipo" id="poliza_tipo" class="form-select form-select-sm" required>
                                <option value="OFERTA">Cumplimiento de oferta</option>
                                <option value="CONTRATO">Cumplimiento de contrato</option>
                                <option value="AMPLIACION">Ampliación</option>
                                <option value="FONDO_REPARO">Fondo de reparo</option>
                                <option value="ANTICIPO">Anticipo financiero</option>
                                <option value="OTRA">Otra</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Monto Caucionado ($)</label>
                            <input type="text" inputmode="decimal" name="monto" id="poliza_monto" class="form-control form-control-sm text-end" value="0,00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Aseguradora</label>
                            <input name="aseguradora" id="poliza_aseguradora" class="form-control form-control-sm" maxlength="150" placeholder="Ej: Fianzas y Crédito SA">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">N° de Póliza</label>
                            <input name="nro_poliza" id="poliza_nro" class="form-control form-control-sm" maxlength="50" placeholder="Ej: 123456">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Vigencia Desde</label>
                            <input type="date" name="vigencia_desde" id="poliza_desde" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Vigencia Hasta <span class="text-muted fw-normal">(vacío = no vence)</span></label>
                            <input type="date" name="vigencia_hasta" id="poliza_hasta" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Póliza Adjunta (PDF / Imagen)</label>
                            <input type="file" name="poliza_archivo" id="poliza_archivo" class="form-control form-control-sm" accept="application/pdf,image/*">
                            <div id="poliza_archivo_actual" class="mt-1 small"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Observaciones</label>
                            <textarea name="observaciones" id="poliza_observaciones" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="liberada" id="poliza_liberada">
                                <label class="form-check-label small" for="poliza_liberada">Liberada / devuelta por el comitente <span class="text-muted">(no suma al resumen)</span></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-dark" id="btnGuardarPoliza"><i class="bi bi-save me-1"></i> Guardar Póliza</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalObraBS;
let tabla;
let estadoFacturacionActual = 'POR_COBRAR';

let globalTotalVentas = 0;
let globalTotalGastos = 0;

window.filtrarPestaña = function(estado) {
    estadoFacturacionActual = estado;
    tabla.draw();
}

function cargarClientes(callback = null){
    $.get('/contable/ajax/clientes.php?accion=listar', function(r){
        let s = $('#cliente_id');
        s.empty().append('<option value="" selected disabled>Seleccione un cliente...</option>');
        if(r.data) {
            r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
        }
        if(callback) callback();
    }, 'json');
}

window.abrirModal = function(modo) {
    $('#formObra')[0].reset();
    $('#id').val('');
    $('#verPresupuestoActual').html('');
    $('#listaArchivosObra, #listaFacturasObra, #listaGastosObra, #listaPresupuestosObra').html('');
    
    $('#seccionResumenFinanciero').addClass('d-none');
    $('#cantDocRepo, #cantFacVentas, #cantFacGastos, #cantPresupuestosObra').text('0');

    polizasObraId = 0;
    polizasObraActual = [];
    polizasSoloLectura = false;
    renderPolizasObra();

    $('#formObra').find('input, select, textarea').prop('disabled', false);
    $('#btnGuardarObra').show();
    $('#wrapperPresupuesto, #wrapperRepositorio').show();
    
    // Reset a primera pestaña
    $('#tab-repo-btn').tab('show');
    
    if(modo === 'NUEVO') {
        $('#modalObraLabel').html('<i class="bi bi-plus-circle me-2"></i> Registrar Nueva Obra');
        cargarClientes();
        modalObraBS.show();
    }
}

// ==========================================
// FUNCIONES DE CARGA Y HERRAMIENTAS
// ==========================================
window.editar = function(id){
    let d = tabla.rows().data().toArray().find(x => x.id == id);
    if(!d) return;

    globalTotalVentas = 0;
    globalTotalGastos = 0;

    $('#listaArchivosObra, #listaFacturasObra, #listaGastosObra, #listaPresupuestosObra').html('');
    $('#formObra')[0].reset();
    $('#verPresupuestoActual').html('');
    $('#formObra').find('input, select, textarea').prop('disabled', false);
    $('#btnGuardarObra').show();
    $('#wrapperPresupuesto, #wrapperRepositorio').show();
    $('#modalObraLabel').html('<i class="bi bi-pencil me-2"></i> Editar Datos de Obra');

    $('#tab-repo-btn').tab('show');

    polizasObraId = d.id;
    polizasObraActual = [];
    polizasSoloLectura = false;
    $('#cantPolizas').text('0');
    $('#contenidoPolizasObra').html('');

    cargarClientes(function() {
        $('#id').val(d.id);
        $('#nombre').val(d.nombre);
        $('#cliente_id').val(d.cliente_id);
        $('#responsable').val(d.responsable);
        $('#direccion').val(d.direccion);
        $('#nro_oc').val(d.nro_oc);
        $('#fecha_inicio').val(d.fecha_inicio);
        $('#fecha_fin').val(d.fecha_fin);
        $('#tipo_obra').val(d.tipo_obra);
        $('#detalle').val(d.detalle);
        $('#estado').val(d.estado);
        $('#facturacion').val(d.facturacion);

        if(d.presupuesto_archivo) {
            $('#verPresupuestoActual').html(`
                <a href="/contable/uploads/obras/${d.presupuesto_archivo}" target="_blank" class="btn btn-xs btn-link text-primary p-0">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Ver presupuesto actual
                </a>
            `);
        }
        
        $('#seccionResumenFinanciero').removeClass('d-none');
        
        window.cargarRepositorio(d.id);
        window.cargarFacturasAsociadas(d.id);
        window.cargarGastosAsociados(d.id);
        window.cargarPolizasObra(d.id);
        window.cargarPresupuestosObra(d.id);
    });
    modalObraBS.show();
}

window.verObra = function(id) {
    window.editar(id);
    setTimeout(() => {
        $('#modalObraLabel').html('<i class="bi bi-eye me-2"></i> Detalles Completos de la Obra');
        $('#formObra').find('input:not([type="file"]), select, textarea').prop('disabled', true);
        $('#btnGuardarObra').hide();
        $('#wrapperPresupuesto, #wrapperRepositorio').hide();

        // En modo ver, las pólizas se muestran sin botones de carga / edición
        polizasSoloLectura = true;
        renderPolizasObra();

        let d = null;
        tabla.rows().data().each(function(row) { if(row.id == id) d = row; });

        if(d && d.presupuesto_archivo) {
            $('#listaArchivosObra').prepend(`
                <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center py-1 fw-semibold border-secondary small">
                    <a href="/contable/uploads/obras/${d.presupuesto_archivo}" target="_blank" class="text-decoration-none text-dark">
                        <i class="bi bi-file-earmark-check-fill text-success me-2"></i> 📄 PRESUPUESTO ACEPTADO
                    </a>
                    <span class="badge bg-success rounded-pill">Principal</span>
                </li>
            `);
        }
    }, 250);
}

function calcularBalanceGlobal() {
    let fmt = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
    
    $('#lblTotalVentas').text(fmt.format(globalTotalVentas));
    $('#lblTotalGastos').text(fmt.format(globalTotalGastos));

    let margen = globalTotalVentas - globalTotalGastos;
    let lblMargen = $('#lblMargenNeto');
    lblMargen.text(fmt.format(margen));

    if(margen >= 0) {
        lblMargen.removeClass('text-danger').addClass('text-success');
    } else {
        lblMargen.removeClass('text-success').addClass('text-danger');
    }
}

window.cargarRepositorio = function(obraId) {
    $.get('/contable/ajax/obras.php?accion=listar_archivos', { obra_id: obraId }, function(r) {
        let lista = $('#listaArchivosObra');
        if(r.success && r.archivos.length > 0) {
            $('#cantDocRepo').text(r.archivos.length);
            r.archivos.forEach(arc => {
                lista.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                        <a href="/contable/uploads/obras/${arc.archivo}" target="_blank" class="text-decoration-none text-dark">
                            <i class="bi bi-file-earmark text-primary me-2"></i> ${arc.nombre_original}
                        </a>
                        <button type="button" class="btn btn-sm text-danger p-0" title="Eliminar documento" onclick="eliminarArchivoRepo(${arc.id}, ${obraId})">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </li>
                `);
            });
        } else {
            $('#cantDocRepo').text('0');
            lista.append('<li class="list-group-item text-muted text-center py-2 small">No hay documentos cargados en el repositorio.</li>');
        }
    }, 'json');
}

window.cargarFacturasAsociadas = function(obraId) {
    $.get('/contable/ajax/obras.php?accion=listar_facturas', { obra_id: obraId }, function(r) {
        let lista = $('#listaFacturasObra');
        lista.empty();
        globalTotalVentas = 0;
        
        if(r.success && r.facturas.length > 0) {
            $('#cantFacVentas').text(r.facturas.length);
            r.facturas.forEach(fac => {
                let monto = parseFloat(fac.total) || 0;
                globalTotalVentas += monto;

                let totalFormateado = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto);
                let fechaFormateada = fac.fecha ? fac.fecha.split('-').reverse().join('/') : '-';

                let botonAdjunto = fac.archivo ? 
                    `<a href="/contable/uploads/facturacion/${fac.archivo}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2" title="Ver PDF Factura"><i class="bi bi-file-earmark-pdf"></i></a>` : 
                    `<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" disabled title="Sin adjunto"><i class="bi bi-eye-slash"></i></button>`;

                lista.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                        <div>
                            <i class="bi bi-file-earmark-text text-success me-2"></i> 
                            <span class="fw-semibold">Factura N° ${fac.nro_factura}</span> 
                            <small class="text-muted ms-2">(${fechaFormateada})</small>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-dark">${totalFormateado}</span>
                            ${botonAdjunto}
                        </div>
                    </li>
                `);
            });
        } else {
            $('#cantFacVentas').text('0');
            lista.append('<li class="list-group-item text-muted text-center py-2 small">No hay facturas de venta vinculadas a esta obra.</li>');
        }
        calcularBalanceGlobal();
    }, 'json');
}

// Presupuestos del módulo Presupuestos vinculados a la obra (no suman al balance:
// son cotizaciones, no movimientos)
const COLOR_ESTADO_PRESUPUESTO = { 'Borrador': 'bg-secondary', 'Enviado': 'bg-primary', 'Aprobado': 'bg-success', 'Rechazado': 'bg-danger', 'Archivado': 'bg-dark' };

window.cargarPresupuestosObra = function(obraId) {
    $.get('/contable/ajax/obras.php?accion=listar_presupuestos', { obra_id: obraId }, function(r) {
        let lista = $('#listaPresupuestosObra');
        lista.empty();
        const presupuestos = (r.success && r.presupuestos) ? r.presupuestos : [];
        $('#cantPresupuestosObra').text(presupuestos.length);

        if (presupuestos.length === 0) {
            lista.append('<li class="list-group-item text-muted text-center py-2 small">No hay presupuestos vinculados a esta obra.</li>');
            return;
        }

        presupuestos.forEach(p => {
            const total = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(parseFloat(p.total_presupuestado) || 0);
            const fecha = p.fecha ? p.fecha.split('-').reverse().join('/') : '-';
            const color = COLOR_ESTADO_PRESUPUESTO[p.estado] || 'bg-secondary';
            const version = p.ultima_version ? `<small class="text-muted ms-1">v${p.ultima_version}</small>` : '';

            const botonPdf = p.ultima_version
                ? `<a href="/contable/modules/presupuestos/pdf.php?id=${p.id}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger py-0 px-2" title="Ver PDF (última versión)"><i class="bi bi-file-earmark-pdf"></i></a>`
                : `<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" disabled title="Sin versiones guardadas"><i class="bi bi-file-earmark-pdf"></i></button>`;

            lista.append(`
                <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                    <div class="text-truncate me-2">
                        <i class="bi bi-file-earmark-spreadsheet text-info me-2"></i>
                        <span class="fw-semibold">${escapeHtml(p.codigo || '-')}</span>${version}
                        <span class="ms-1">– ${escapeHtml(p.titulo || '')}</span>
                        <small class="text-muted ms-2">(${fecha})</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span class="badge ${color}">${escapeHtml(p.estado || '-')}</span>
                        <span class="fw-bold text-dark">${total}</span>
                        ${botonPdf}
                        <a href="/contable/modules/presupuestos/?historial=${p.id}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Historial de versiones"><i class="bi bi-clock-history"></i></a>
                        <a href="/contable/modules/presupuestos/?abrir=${p.id}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-2" title="Abrir en el módulo Presupuestos"><i class="bi bi-box-arrow-up-right"></i></a>
                    </div>
                </li>
            `);
        });
    }, 'json');
}

window.cargarGastosAsociados = function(obraId) {
    $.get('/contable/ajax/obras.php?accion=listar_gastos', { obra_id: obraId }, function(r) {
        let lista = $('#listaGastosObra');
        lista.empty();
        globalTotalGastos = 0;

        if(r.success && r.gastos.length > 0) {
            $('#cantFacGastos').text(r.gastos.length);
            r.gastos.forEach(g => {
                let monto = parseFloat(g.total) || 0;
                globalTotalGastos += monto;

                let totalFormateado = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto);
                let fechaFormateada = g.fecha ? g.fecha.split('-').reverse().join('/') : '-';
                let prov = g.proveedor ? g.proveedor : 'Gasto General';
                let comp = g.numero_comprobante ? `Comp: ${g.numero_comprobante}` : 'Sin comprobante';

                let botonAdjunto = g.archivo ? 
                    `<a href="/contable/uploads/gastos/${g.archivo}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2" title="Ver Comprobante Gasto"><i class="bi bi-file-earmark-pdf"></i></a>` : 
                    `<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" disabled title="Sin adjunto"><i class="bi bi-eye-slash"></i></button>`;

                lista.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                        <div>
                            <i class="bi bi-cart-dash text-danger me-2"></i> 
                            <span class="fw-semibold">${prov}</span> 
                            <small class="text-muted ms-2">(${comp} - ${fechaFormateada})</small>
                            <span class="text-secondary d-block" style="font-size:0.75rem">${g.detalle || ''}</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-danger">${totalFormateado}</span>
                            ${botonAdjunto}
                        </div>
                    </li>
                `);
            });
        } else {
            $('#cantFacGastos').text('0');
            lista.append('<li class="list-group-item text-muted text-center py-2 small">No hay gastos o egresos registrados para esta obra.</li>');
        }
        calcularBalanceGlobal();
    }, 'json');
}

// ==========================================
// PÓLIZAS DE CAUCIÓN
// ==========================================
let modalPolizaBS;
let polizasObraActual = [];
let polizasObraId = 0;
let polizasSoloLectura = false;

const TIPOS_POLIZA = {
    OFERTA:       'Cumpl. de oferta',
    CONTRATO:     'Cumpl. de contrato',
    AMPLIACION:   'Ampliación',
    FONDO_REPARO: 'Fondo de reparo',
    ANTICIPO:     'Anticipo financiero',
    OTRA:         'Otra'
};

function escapeHtml(s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

// Importes con punto de miles y coma decimal (1.234.567,89)
function parseMonto(v) {
    let s = String(v === null || v === undefined ? '' : v).trim().replace(/[^\d.,-]/g, '');
    if (s === '') return 0;
    if (s.includes(',')) {
        s = s.replace(/\./g, '').replace(',', '.');
    } else if (!/^-?\d+\.\d{1,2}$/.test(s)) {
        s = s.replace(/\./g, '');
    }
    let n = parseFloat(s);
    return isNaN(n) ? 0 : n;
}

function formatMonto(n) {
    return Number(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fechaCorta(f) {
    if (!f) return '';
    let p = f.split('-');
    return `${p[2]}/${p[1]}/${p[0].slice(2)}`;
}

function badgeEstadoPoliza(p) {
    if (p.liberada == 1) return '<span class="badge bg-secondary">Liberada</span>';
    if (!p.vigencia_hasta) return '<span class="badge bg-success">Vigente</span>';

    let hoy = new Date(); hoy.setHours(0, 0, 0, 0);
    let dias = Math.ceil((new Date(p.vigencia_hasta + 'T00:00:00') - hoy) / 86400000);

    if (dias < 0) return '<span class="badge bg-danger">Vencida</span>';
    if (dias <= 30) return `<span class="badge bg-warning text-dark">Vence en ${dias} ${dias === 1 ? 'día' : 'días'}</span>`;
    return '<span class="badge bg-success">Vigente</span>';
}

window.cargarPolizasObra = function(obraId) {
    polizasObraId = obraId;
    $.get('/contable/ajax/obras.php?accion=listar_polizas', { obra_id: obraId }, function(r) {
        polizasObraActual = (r.success && r.polizas) ? r.polizas : [];
        renderPolizasObra();
    }, 'json');
}

function renderPolizasObra() {
    let cont = $('#contenidoPolizasObra');
    $('#cantPolizas').text(polizasObraActual.length);

    if (!polizasObraId) {
        cont.html('<div class="text-muted text-center py-2 small">Guardá la obra para poder cargar sus pólizas de caución.</div>');
        return;
    }

    let html = '';
    if (polizasObraActual.length === 0) {
        html += '<div class="text-muted text-center py-2 small">No hay pólizas de caución cargadas para esta obra.</div>';
    } else {
        let filas = '';
        polizasObraActual.forEach(p => {
            let vigencia = (p.vigencia_desde || p.vigencia_hasta)
                ? `${fechaCorta(p.vigencia_desde) || '-'} → ${fechaCorta(p.vigencia_hasta) || 'Sin venc.'}`
                : '-';
            let aseg = [p.aseguradora, p.nro_poliza].filter(x => x).map(escapeHtml).join(' · ') || '-';
            let btnPdf = p.archivo
                ? `<a href="/contable/uploads/obras/${p.archivo}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-1" title="Ver póliza"><i class="bi bi-file-earmark-pdf"></i></a>`
                : '';
            let acciones = polizasSoloLectura ? btnPdf : `
                ${btnPdf}
                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1" title="Editar póliza" onclick="editarPoliza(${p.id})"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar póliza" onclick="eliminarPoliza(${p.id})"><i class="bi bi-trash3"></i></button>`;

            filas += `
                <tr class="${p.liberada == 1 ? 'text-muted' : ''}">
                    <td class="fw-semibold">${TIPOS_POLIZA[p.tipo] || p.tipo}</td>
                    <td>${aseg}${p.observaciones ? `<div class="text-muted" style="font-size: 0.75rem;">${escapeHtml(p.observaciones)}</div>` : ''}</td>
                    <td class="text-nowrap">${vigencia}</td>
                    <td class="text-end fw-bold text-nowrap">$ ${formatMonto(p.monto)}</td>
                    <td class="text-center">${badgeEstadoPoliza(p)}</td>
                    <td class="text-end text-nowrap"><div class="d-inline-flex gap-1">${acciones}</div></td>
                </tr>`;
        });

        html += `
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr><th>Tipo</th><th>Aseguradora / N°</th><th>Vigencia</th><th class="text-end">Monto caucionado</th><th class="text-center">Estado</th><th></th></tr>
                    </thead>
                    <tbody>${filas}</tbody>
                </table>
            </div>`;

        // Resumen: suma del monto caucionado por tipo (sin las liberadas)
        let resumen = {};
        polizasObraActual.forEach(p => {
            if (p.liberada == 1) return;
            resumen[p.tipo] = (resumen[p.tipo] || 0) + (parseFloat(p.monto) || 0);
        });
        let partes = Object.keys(TIPOS_POLIZA)
            .filter(t => resumen[t] !== undefined)
            .map(t => `<span class="text-nowrap">${TIPOS_POLIZA[t]}: <b>$ ${formatMonto(resumen[t])}</b></span>`);

        html += `
            <div class="border-top mt-1 pt-2 px-1 small d-flex flex-wrap gap-3 align-items-center">
                <span class="fw-bold text-uppercase text-secondary" style="font-size: 0.75rem;">Resumen caucionado</span>
                ${partes.length ? partes.join('<span class="text-muted">·</span>') : '<span class="text-muted">Todas las pólizas están liberadas.</span>'}
            </div>`;
    }

    if (!polizasSoloLectura) {
        html += `
            <div class="text-end mt-2">
                <button type="button" class="btn btn-sm btn-dark" onclick="nuevaPoliza()"><i class="bi bi-plus-circle me-1"></i> Nueva Póliza</button>
            </div>`;
    }

    cont.html(html);
}

function limpiarFormPoliza() {
    $('#formPoliza')[0].reset();
    $('#poliza_id').val('');
    $('#poliza_obra_id').val(polizasObraId);
    $('#poliza_monto').val('0,00');
    $('#poliza_archivo_actual').html('');
}

window.nuevaPoliza = function() {
    limpiarFormPoliza();
    $('#tituloModalPoliza').html('<i class="bi bi-shield-plus me-2"></i>Nueva Póliza de Caución');
    modalPolizaBS.show();
}

window.editarPoliza = function(id) {
    let p = polizasObraActual.find(x => x.id == id);
    if (!p) return;

    limpiarFormPoliza();
    $('#poliza_id').val(p.id);
    $('#poliza_tipo').val(p.tipo);
    $('#poliza_monto').val(formatMonto(p.monto));
    $('#poliza_aseguradora').val(p.aseguradora || '');
    $('#poliza_nro').val(p.nro_poliza || '');
    $('#poliza_desde').val(p.vigencia_desde || '');
    $('#poliza_hasta').val(p.vigencia_hasta || '');
    $('#poliza_observaciones').val(p.observaciones || '');
    $('#poliza_liberada').prop('checked', p.liberada == 1);
    if (p.archivo) {
        $('#poliza_archivo_actual').html(`<a href="/contable/uploads/obras/${p.archivo}" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>${escapeHtml(p.nombre_original || 'Ver archivo actual')}</a> <span class="text-muted">(si subís otro, lo reemplaza)</span>`);
    }
    $('#tituloModalPoliza').html('<i class="bi bi-pencil me-2"></i>Editar Póliza de Caución');
    modalPolizaBS.show();
}

window.eliminarPoliza = function(id) {
    Swal.fire({
        title: '¿Eliminar esta póliza?',
        text: 'Se borra el registro y su archivo adjunto. Si el comitente la devolvió, podés marcarla como liberada en lugar de eliminarla.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (!result.isConfirmed) return;
        $.post('/contable/ajax/obras.php?accion=eliminar_poliza', { id: id }, function(r) {
            if (r.success) {
                window.cargarPolizasObra(polizasObraId);
            } else {
                Swal.fire('Error', r.msg || 'No se pudo eliminar la póliza.', 'error');
            }
        }, 'json');
    });
}

window.eliminarArchivoRepo = function(archivoId, obraId) {
    if(confirm('¿Desea remover este archivo del repositorio?')) {
        $.post('/contable/ajax/obras.php?accion=eliminar_archivo', { id: archivoId }, function() {
            $('#listaArchivosObra').html('');
            window.cargarRepositorio(obraId);
        }, 'json');
    }
}

window.eliminar = function(id){
    Swal.fire({
        title: '¿Eliminar esta obra?',
        text: "Se borrará permanentemente junto con su repositorio de archivos y presupuestos.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#212529',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/contable/ajax/obras.php?accion=eliminar', {id}, () => {
                Swal.fire({ icon: 'success', title: 'Eliminado', text: 'La obra se borró correctamente.', timer: 1500, showConfirmButton: false });
                tabla.ajax.reload(null, false);
            });
        }
    });
}

// ==========================================
// INICIALIZACIÓN DEL DOM
// ==========================================
document.addEventListener("DOMContentLoaded", function(){
    modalObraBS = new bootstrap.Modal(document.getElementById('modalObra'));

    // Modal de póliza apilado sobre el de la obra
    const polizaEl = document.getElementById('modalPoliza');
    modalPolizaBS = new bootstrap.Modal(polizaEl);
    polizaEl.addEventListener('shown.bs.modal', function() {
        $('.modal-backdrop').last().css('z-index', 1060);
    });
    polizaEl.addEventListener('hidden.bs.modal', function() {
        // Bootstrap quita "modal-open" del body al cerrar el segundo modal
        if ($('#modalObra').hasClass('show')) $('body').addClass('modal-open');
    });

    $('#poliza_monto').on('blur', function() {
        $(this).val(formatMonto(parseMonto($(this).val())));
    }).on('focus', function() {
        this.select();
    });

    $('#formPoliza').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        // El monto se muestra como 1.234,56 pero al servidor va como número
        formData.set('monto', parseMonto($('#poliza_monto').val()).toFixed(2));

        let btn = $('#btnGuardarPoliza').prop('disabled', true);
        $.ajax({
            url: '/contable/ajax/obras.php?accion=guardar_poliza',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    modalPolizaBS.hide();
                    window.cargarPolizasObra(polizasObraId);
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Póliza guardada', timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire('Error', r.msg || 'No se pudo guardar la póliza.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Ocurrió un problema de comunicación al guardar la póliza.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex, rowData) {
            let factura = rowData.facturacion; 
            
            if (estadoFacturacionActual === 'POR_COBRAR') {
                return factura === 'Por Cobrar';
            } else if (estadoFacturacionActual === 'PAGADAS') {
                return factura === 'Pagadas';
            }
            return true;
        }
    );

    tabla = $('#tablaObras').DataTable({
        ajax: '/contable/ajax/obras.php?accion=listar',
        order: [[0, 'desc']],
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: ' Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: 'print',
                text: ' Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            { 
                extend: 'colvis', 
                text: 'Columnas', 
                className: 'btn btn-sm btn-secondary' 
            }
        ],
        drawCallback: function(settings) {
            let api = this.api();
            let datosCompletos = api.rows().data().toArray();
            
            let cantCobrar = datosCompletos.filter(x => x.facturacion === 'Por Cobrar').length;
            let cantPagadas = datosCompletos.filter(x => x.facturacion === 'Pagadas').length;

            $('#cant-cobrar').text(cantCobrar);
            $('#cant-pagadas').text(cantPagadas);
        },
        columns: [
            { data: 'id' },
            { data: 'nombre', className: 'fw-semibold' },
            { data: 'cliente' },
            { data: 'tipo_obra' },
            { data: 'nro_oc', render: d => d ? d : '-' },
            {
                data: null,
                render: function(d){
                    let inicio = d.fecha_inicio ? d.fecha_inicio.split('-').reverse().join('/') : '-';
                    let fin = d.fecha_fin ? d.fecha_fin.split('-').reverse().join('/') : 'En curso';
                    return `<small>${inicio} al ${fin}</small>`;
                }
            },
            { 
                data: 'estado',
                render: function(d) {
                    let badgeColor = d === 'ACTIVA' ? 'bg-success' : 'bg-secondary';
                    return `<span class="badge ${badgeColor}">${d}</span>`;
                }
            },
            { 
                data: 'usuario_nombre',
                render: function(d) {
                    return d ? `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i> ${d}</span>` : '<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i> Sistema</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: function(d){
                    let btnPres = d.presupuesto_archivo ? `<a href="/contable/uploads/obras/${d.presupuesto_archivo}" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver Presupuesto"><i class="bi bi-file-earmark-check"></i></a>` : '';
                    return `
                        <div class="d-inline-flex gap-1">
                            ${btnPres}
                            <button class="btn btn-sm btn-outline-secondary" title="Ver Obra" onclick="verObra(${d.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" title="Editar Obra" onclick="editar(${d.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar Obra" onclick="eliminar(${d.id})">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#formObra').submit(function(e){
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: '/contable/ajax/obras.php?accion=guardar',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function() {
                tabla.ajax.reload(null, false);
                modalObraBS.hide();
            }
        });
    });
});
</script>

<?php include '../../../includes/footer.php'; ?>