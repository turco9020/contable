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

                    <!-- RESUMEN FINANCIERO DE LA OBRA (Se activa al ver/editar) -->
                    <div id="seccionResumenFinanciero" class="d-none mb-4 p-3 bg-light rounded border">
                        <div class="row text-center g-2">
                            <div class="col-md-4">
                                <div class="p-2 bg-white rounded shadow-sm border-start border-success border-4">
                                    <small class="text-muted fw-bold text-uppercase d-block">Ingresos (Ventas)</small>
                                    <span class="fs-5 fw-bold text-success" id="lblTotalVentas">$ 0,00</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2 bg-white rounded shadow-sm border-start border-danger border-4">
                                    <small class="text-muted fw-bold text-uppercase d-block">Egresos (Gastos)</small>
                                    <span class="fs-5 fw-bold text-danger" id="lblTotalGastos">$ 0,00</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2 bg-white rounded shadow-sm border-start border-primary border-4" id="boxMargenNeto">
                                    <small class="text-muted fw-bold text-uppercase d-block">Balance / Margen</small>
                                    <span class="fs-5 fw-bold" id="lblMargenNeto">$ 0,00</span>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                    <button class="nav-link active fw-bold text-dark" id="tab-repo-btn" data-bs-toggle="tab" data-bs-target="#tab-repo" type="button" role="tab">
                                        <i class="bi bi-folder2-open me-1 text-primary"></i> Documentos (<span id="cantDocRepo">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-dark" id="tab-ventas-btn" data-bs-toggle="tab" data-bs-target="#tab-ventas" type="button" role="tab">
                                        <i class="bi bi-receipt me-1 text-success"></i> Facturas Venta (<span id="cantFacVentas">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-dark" id="tab-gastos-btn" data-bs-toggle="tab" data-bs-target="#tab-gastos" type="button" role="tab">
                                        <i class="bi bi-cart-dash me-1 text-danger"></i> Gastos / Compras (<span id="cantFacGastos">0</span>)
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content border border-top-0 p-3 rounded-bottom bg-white" id="tabModulosObraContent">
                                <!-- TAB REPOSITORIO -->
                                <div class="tab-pane fade show active" id="tab-repo" role="tabpanel">
                                    <ul class="list-group" id="listaArchivosObra">
                                        <!-- Dinámico -->
                                    </ul>
                                </div>

                                <!-- TAB FACTURAS VENTA -->
                                <div class="tab-pane fade" id="tab-ventas" role="tabpanel">
                                    <ul class="list-group" id="listaFacturasObra">
                                        <!-- Dinámico -->
                                    </ul>
                                </div>

                                <!-- TAB GASTOS / COMPRAS -->
                                <div class="tab-pane fade" id="tab-gastos" role="tabpanel">
                                    <ul class="list-group" id="listaGastosObra">
                                        <!-- Dinámico -->
                                    </ul>
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
    $('#listaArchivosObra, #listaFacturasObra, #listaGastosObra').html('');
    
    $('#seccionResumenFinanciero').addClass('d-none');
    $('#cantDocRepo, #cantFacVentas, #cantFacGastos').text('0');

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

    $('#listaArchivosObra, #listaFacturasObra, #listaGastosObra').html('');
    $('#formObra')[0].reset();
    $('#verPresupuestoActual').html('');
    $('#formObra').find('input, select, textarea').prop('disabled', false);
    $('#btnGuardarObra').show();
    $('#wrapperPresupuesto, #wrapperRepositorio').show();
    $('#modalObraLabel').html('<i class="bi bi-pencil me-2"></i> Editar Datos de Obra');
    
    $('#tab-repo-btn').tab('show');

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
        
        let d = null;
        tabla.rows().data().each(function(row) { if(row.id == id) d = row; });

        if(d && d.presupuesto_archivo) {
            $('#listaArchivosObra').prepend(`
                <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center py-2 fw-semibold border-secondary">
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
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
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
            lista.append('<li class="list-group-item text-muted text-center py-3">No hay documentos cargados en el repositorio.</li>');
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
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
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
            lista.append('<li class="list-group-item text-muted text-center py-3">No hay facturas de venta vinculadas a esta obra.</li>');
        }
        calcularBalanceGlobal();
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
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <div>
                            <i class="bi bi-cart-dash text-danger me-2"></i> 
                            <span class="fw-semibold">${prov}</span> 
                            <small class="text-muted ms-2">(${comp} - ${fechaFormateada})</small>
                            <div class="small text-secondary">${g.detalle || ''}</div>
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
            lista.append('<li class="list-group-item text-muted text-center py-3">No hay gastos o egresos registrados para esta obra.</li>');
        }
        calcularBalanceGlobal();
    }, 'json');
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