<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/sidebar.php';
?>

<style>
/* Botones de Acciones más compactos, para que la columna ocupe menos */
.acciones-factura { gap: 2px !important; }
.acciones-factura .btn { padding: 0.15rem 0.35rem; font-size: 0.8rem; }
.celda-cliente { max-width: 200px; white-space: normal; line-height: 1.2; }
</style>

<div class="content">

    <!-- CABECERA DEL MÓDULO (Estilo unificado) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-receipt text-secondary me-2"></i> Facturación (Ventas)
        </h4>
        <div class="d-flex gap-2">
            <a href="/contable/modules/clientes/cuenta_corriente.php" class="btn btn-outline-success d-flex align-items-center">
                <i class="bi bi-journal-text me-2"></i> Cuenta corriente
            </a>
            <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal()">
                <i class="bi bi-plus-circle me-2"></i> Nueva Factura
            </button>
        </div>
    </div>

    <!-- Pestañas de Navegación de Estados de Facturas -->
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

    <div class="card p-3 shadow-sm">
        <div class="table-responsive">
            <table id="tablaFacturas" class="table table-bordered table-striped w-100">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Nro. Factura</th>
                        <th>Cliente</th>
                        <th>Centro Costo</th>
                        <th>Neto</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Saldo</th>
                        <th>Vence</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFactura" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModal"><i class="bi bi-receipt me-2"></i>Factura Venta</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formFactura" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id">

                    <div id="seccionEscaneo" class="p-3 mb-3 bg-light border rounded text-center">
                        <label class="form-label fw-bold text-secondary mb-2">✨ Asistente de Carga Automática (Subir PDF de AFIP)</label>
                        <input type="file" id="archivo_escanear" name="archivo_escanear" class="form-control form-control-sm mx-auto" style="max-width: 400px;" accept="application/pdf">
                        <div class="form-text mt-1">Subí el comprobante digital original para autocompletar el formulario al instante.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Emisión</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo Comprobante</label>
                            <select name="tipo_comprobante_id" id="tipo_comprobante_id" class="form-control" required></select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Punto de Venta / Número</label>
                            <div class="input-group">
                                <input type="number" name="punto_venta" id="punto_venta" class="form-control" placeholder="00002" style="max-width: 90px;" required>
                                <span class="input-group-text">-</span>
                                <input type="number" name="nro_factura" id="nro_factura" class="form-control" placeholder="00000139" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Fecha Vencimiento Pago</label>
                            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <div class="alta-rapida-wrap">
                                <select name="cliente_id" id="cliente_id" class="form-control" required></select>
                                <button type="button" class="btn btn-outline-secondary" data-alta-rapida="cliente" title="Nuevo cliente"><i class="bi bi-plus-lg"></i></button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-2 fw-semibold text-dark">Centro de Costo</label>
                            <select name="centro_costo_id" id="centro_costo_id" class="form-select border-secondary text-dark" style="background-color: #fef9e7" required></select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-dark"><i class="bi bi-cone-striped me-1"></i> Asignar a Obra (Opcional)</label>
                            <select name="obra_id" id="obra_id" class="form-select border-dark"></select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Estado de Pago</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="DEBE">🔴 DEBE</option>
                                <option value="PARCIAL">🔵 PARCIAL (con cobros)</option>
                                <option value="PAGADO">🟢 PAGADO</option>
                                <option value="VER">🟡 VER (Revisión)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Detalle (Lo que dice la factura)</label>
                            <textarea name="detalle" id="detalle" class="form-control" rows="3" placeholder="Descripción de los artículos o servicios prestados..." required></textarea>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Importe Neto</label>
                            <input type="text" inputmode="decimal" name="neto" id="neto" class="form-control text-end input-monto" value="0,00" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">IVA</label>
                            <input type="text" inputmode="decimal" name="iva" id="iva" class="form-control text-end input-monto" value="0,00" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Importe Total</label>
                            <input type="text" inputmode="decimal" name="total" id="total" class="form-control text-end input-monto" value="0,00" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Alícuota IIBB</label>
                            <select name="iibb_actividad_id" id="iibb_actividad_id" class="form-select"></select>
                            <div class="form-text small mt-1" id="iibb_importe"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observaciones Internas (Usuario)</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Notas del usuario que carga..."></textarea>
                        </div>

                        <div class="col-12" id="seccionArchivoReal" style="display:none;">
                            <label class="form-label">Comprobante Adjunto Definitivo</label>
                            <input type="file" name="archivo" id="archivo" class="form-control" accept="application/pdf,image/*">
                            <div id="archivo_actual" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark" id="btnGuardar">Guardar Factura</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- MODAL GESTIÓN DE RETENCIONES -->
<div class="modal fade" id="modalRetenciones" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-percent me-2"></i> Retenciones Aplicadas - Factura <span id="lblFacturaRetencion"></span></h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Formulario de Carga Directa -->
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold mb-3 text-secondary">Cargar Certificado de Retención</h6>
                        <form id="formRetencion" enctype="multipart/form-data">
                            <input type="hidden" name="ret_factura_id" id="ret_factura_id">
                            
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Tipo de Retención</label>
                                <select name="tipo_retencion_id" id="ret_tipo_retencion_id" class="form-select form-select-sm" required>
                                    <option value="">Cargando opciones...</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Nro. de Certificado / Comprobante</label>
                                <input type="text" name="nro_certificado" class="form-control form-control-sm" placeholder="Ej: 001-23948" required>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Fecha Emisión</label>
                                    <input type="date" name="fecha_retencion" id="ret_fecha" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Importe ($)</label>
                                    <input type="number" step="0.01" name="importe_retencion" class="form-control form-control-sm" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Adjuntar Comprobante (PDF / Imagen)</label>
                                <input type="file" name="ret_archivo" class="form-control form-control-sm" accept="application/pdf,image/*">
                            </div>
                            <button type="submit" class="btn btn-sm btn-dark w-100"><i class="bi bi-plus-circle me-1"></i> Registrar Retención</button>
                        </form>
                    </div>
                    
                    <!-- Listado en Tiempo Real -->
                    <div class="col-md-7">
                        <h6 class="fw-bold mb-3 text-secondary">Historial de Retenciones Declaradas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped align-middle" style="font-size:0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Certificado</th>
                                        <th class="text-end">Monto</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyRetenciones">
                                    <!-- Carga dinámica vía AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL COBROS DE UNA FACTURA (pagos parciales o totales) -->
<div class="modal fade" id="modalCobros" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-cash-coin me-2"></i> Cobros - Factura <span id="lblFacturaCobro"></span></h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Resumen de saldo -->
                <div class="row g-2 mb-3 text-center small">
                    <div class="col-6 col-md-3"><div class="border rounded p-2"><div class="text-muted">Total factura</div><div class="fw-bold" id="cobResTotal">$ 0,00</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2"><div class="text-muted">Cobrado</div><div class="fw-bold text-success" id="cobResCobrado">$ 0,00</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2"><div class="text-muted">Retenciones</div><div class="fw-bold text-secondary" id="cobResRetenido">$ 0,00</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 bg-light"><div class="text-muted">Saldo pendiente</div><div class="fw-bold fs-6" id="cobResSaldo">$ 0,00</div></div></div>
                </div>

                <div class="row g-4">
                    <!-- Formulario de nuevo cobro -->
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold mb-3 text-secondary">Registrar cobro</h6>
                        <form id="formCobro" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="cob_factura_id" id="cob_factura_id">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Fecha</label>
                                    <input type="date" name="cob_fecha" id="cob_fecha" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Importe ($)</label>
                                    <input type="text" inputmode="decimal" name="cob_importe" id="cob_importe" class="form-control form-control-sm text-end fw-bold" placeholder="0,00" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Medio de pago</label>
                                <select name="cob_medio_pago_id" id="cob_medio_pago_id" class="form-select form-select-sm"></select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Ingresar en caja <span class="text-muted fw-normal">(opcional)</span></label>
                                <select name="cob_caja_id" id="cob_caja_id" class="form-select form-select-sm"></select>
                                <div class="form-text small">Si elegís una caja, el ingreso se registra solo en Caja y se borra junto con el cobro.</div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">N° de recibo / comprobante</label>
                                <input type="text" name="cob_comprobante" class="form-control form-control-sm" maxlength="100">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Observaciones</label>
                                <input type="text" name="cob_observaciones" class="form-control form-control-sm" maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Adjuntar comprobante (PDF / imagen)</label>
                                <input type="file" name="cob_archivo" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <button type="submit" class="btn btn-sm btn-success w-100" id="btnGuardarCobro"><i class="bi bi-plus-circle me-1"></i> Registrar cobro</button>
                        </form>
                    </div>

                    <!-- Cobros registrados -->
                    <div class="col-md-7">
                        <h6 class="fw-bold mb-3 text-secondary">Cobros registrados</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped align-middle" style="font-size:0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Medio / Caja</th>
                                        <th>Recibo</th>
                                        <th class="text-end">Importe</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyCobros"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>

<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

let tabla;
let modalFactura;
let globalClientesParaMapeo = [];
let estadoPestañaActual = 'POR_COBRAR';
let buscadoresTom = {};

// ==========================================
// VARIABLES DE RETENCIONES
// ==========================================
let modalRetencionesBS;

// ==========================================
// FORMATO DE IMPORTES (1.234.567,89)
// ==========================================
function parseMonto(v) {
    let s = String(v === null || v === undefined ? '' : v).trim().replace(/[^\d.,-]/g, '');
    if (s === '') return 0;
    if (s.includes(',')) {
        // Formato argentino: el punto es de miles y la coma decimal
        s = s.replace(/\./g, '').replace(',', '.');
    } else if (!/^-?\d+\.\d{1,2}$/.test(s)) {
        // Sin coma: un punto seguido de 1 o 2 dígitos es decimal (ej: 1500.5), si no es de miles
        s = s.replace(/\./g, '');
    }
    let n = parseFloat(s);
    return isNaN(n) ? 0 : n;
}

function formatMonto(n) {
    return Number(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ==========================================
// ALÍCUOTA IIBB
// ==========================================
function etiquetaIIBB(alicuota, actividad, tratamiento) {
    let tieneAlicuota = alicuota !== null && alicuota !== undefined && alicuota !== '';
    let pct = tieneAlicuota
        ? Number(alicuota).toLocaleString('es-AR', { maximumFractionDigits: 3 }) + ' %'
        : (tratamiento || 'Sin alícuota');
    return pct + ' - ' + actividad;
}

function actualizarImporteIIBB() {
    let opt = $('#iibb_actividad_id option:selected');
    let ali = opt.attr('data-alicuota');
    if (!opt.val() || ali === undefined || ali === '') {
        $('#iibb_importe').text('');
        return;
    }
    let importe = parseMonto($('#neto').val()) * parseFloat(ali) / 100;
    $('#iibb_importe').text('IIBB: $ ' + formatMonto(importe));
}

$.fn.dataTable.ext.search.push(
    function(settings, data, dataIndex, rowData) {
        let estado = rowData.estado ? rowData.estado.toUpperCase() : 'DEBE';

        if (estadoPestañaActual === 'POR_COBRAR') {
            return (estado === 'DEBE' || estado === 'VER' || estado === 'PARCIAL');
        } else if (estadoPestañaActual === 'PAGADAS') {
            return (estado === 'PAGADO');
        }
        return true;
    }
);

document.addEventListener("DOMContentLoaded", function() {
    modalFactura = new bootstrap.Modal(document.getElementById('modalFactura'));
    
    // ==========================================
    // INICIALIZACIÓN DEL MODAL DE RETENCIONES
    // ==========================================
    modalRetencionesBS = new bootstrap.Modal(document.getElementById('modalRetenciones'));
    
    // Handler para guardar retención de forma asíncrona
    $('#formRetencion').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let facId = $('#ret_factura_id').val();
        
        $.ajax({
            url: '/contable/ajax/facturacion.php?accion=guardar_retencion',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(r) {
                $('#formRetencion')[0].reset();
                $('#ret_factura_id').val(facId);
                // Setea fecha actual por comodidad en la re-carga
                $('#ret_fecha').val(new Date().toISOString().split('T')[0]); 
                refrescarTablaRetenciones(facId);
                tabla.ajax.reload(null, false); // la retención cambia el saldo y el estado
            }
        });
    });

    cargarSelects();

    tabla = $('#tablaFacturas').DataTable({
        ajax: '/contable/ajax/facturacion.php?accion=listar',
        order: [[0, 'desc']],
        autoWidth: false,
        responsive: true,
        deferRender: true,
        // Cuando falta ancho, se ocultan primero las columnas de número más alto
        // (pasan al desplegable de la fila). Acciones nunca debe quedar oculta.
        columnDefs: [
            { targets: 12, responsivePriority: 1 },     // Acciones
            { targets: 2,  responsivePriority: 2 },     // N° factura
            { targets: 3,  responsivePriority: 3 },     // Cliente
            { targets: 7,  responsivePriority: 4 },     // Total
            { targets: 8,  responsivePriority: 5 },     // Saldo
            { targets: 9,  responsivePriority: 6 },     // Vence
            { targets: 10, responsivePriority: 7 },     // Estado
            { targets: 0,  responsivePriority: 8 },     // Fecha
            { targets: [5, 6, 1, 11], responsivePriority: 10000 } // Neto, IVA, Tipo, Usuario
        ],
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: ' Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] }
            },
            {
                extend: 'print',
                text: ' Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] }
            },
            { 
                extend: 'colvis', 
                text: 'Columnas', 
                className: 'btn btn-sm btn-secondary' 
            }
        ],
        drawCallback: function(settings) {
            actualizarBadgesPestañas(settings.aoData);
        },
        columns: [
            { 
                data: 'fecha',
                render: function(d){
                    if(!d) return '-';
                    let p = d.split('-');
                    return p.length === 3 ? `${p[2]}-${p[1]}-${p[0]}` : d;
                }
            },
            { data: 'tipo_comprobante_nombre' },
            { 
                data: null,
                render: function(row){
                    let pv = String(row.punto_venta || 0).padStart(5, '0');
                    let nf = String(row.nro_factura || 0).padStart(8, '0');
                    return `${pv}-${nf}`;
                }
            },
            {
                // Nombres largos en dos líneas, para que la tabla no oculte otras columnas
                data: 'cliente_nombre',
                render: (d, type) => type === 'display' ? `<div class="celda-cliente">${$('<div>').text(d || '').html()}</div>` : d
            },
            { data: 'centro_costo_nombre', className: 'none'},
            { 
                data: 'neto',
                className: 'text-end',
                render: function(d) {
                    return '$ ' + Number(d || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                }
            },
            { 
                data: 'iva',
                className: 'text-end',
                render: function(d) {
                    return '$ ' + Number(d || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                }
            },
            {
                data: 'total',
                className: 'text-end',
                render: function(d) {
                    return '$ ' + Number(d || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                }
            },
            {
                // Saldo = Total - Cobros - Retenciones
                data: 'saldo',
                className: 'text-end',
                render: function(d, type, row) {
                    let saldo = Number(d || 0);
                    if (type !== 'display') return saldo;
                    let estado = (row.estado || '').toUpperCase();
                    let cobrado = Number(row.cobrado || 0), retenido = Number(row.retenido || 0);
                    // Marcada como pagada a mano, sin cobros cargados: no se muestra un saldo engañoso
                    if (estado === 'PAGADO' && cobrado === 0 && retenido === 0) return '<span class="text-muted">-</span>';
                    let titulo = 'Cobrado: $ ' + formatMonto(cobrado) + ' · Retenciones: $ ' + formatMonto(retenido);
                    let clase = saldo <= 0.01 ? 'text-success' : (cobrado > 0 || retenido > 0 ? 'text-primary fw-bold' : '');
                    return `<span class="${clase}" title="${titulo}">$ ${formatMonto(saldo)}</span>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function(row) {
                    if (!row.fecha_vencimiento) return '<span class="text-muted">-</span>';
                    
                    let fechaVto = new Date(row.fecha_vencimiento + 'T00:00:00');
                    let hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);
                    
                    let diferenciaMs = fechaVto - hoy;
                    let diasRestantes = Math.ceil(diferenciaMs / (1000 * 60 * 60 * 24));
                    let estadoFactura = row.estado ? row.estado.toUpperCase() : 'DEBE';
                    
                    if (estadoFactura === 'PAGADO') {
                        return '<span class="text-muted">✔︎</span>';
                    }
                    
                    if (diasRestantes < 0) {
                        let diasPasados = Math.abs(diasRestantes);
                        return `<span class="text-danger fw-bold">Vencida ${diasPasados} ${diasPasados === 1 ? 'día' : 'días'}</span>`;
                    } else if (diasRestantes === 0) {
                        return '<span class="text-warning fw-bold">Vence hoy</span>';
                    } else {
                        return `<span class="text-secondary">${diasRestantes} ${diasRestantes === 1 ? 'día' : 'días'}</span>`;
                    }
                }
            },
            {
                data: 'estado',
                render: function(d) {
                    let texto = d ? d.toUpperCase() : 'DEBE';
                    let colorLed = 'bg-danger';
                    
                    if(texto === 'PAGADO') colorLed = 'bg-success';
                    else if(texto === 'VER') colorLed = 'bg-warning';
                    else if(texto === 'PARCIAL') colorLed = 'bg-primary';
                    
                    return `
                        <div class="d-inline-flex align-items-center text-secondary fw-semibold" style="font-size: 0.9rem;">
                            <span class="rounded-circle ${colorLed}" style="width: 8px; height: 8px; display: inline-block; margin-right: 8px;"></span>
                            ${texto}
                        </div>
                    `;
                }
            },
            {
                data: 'usuario_nombre',
                visible: true,
                render: function(d) {
                    return d ? `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i> ${d}</span>` : '<span class="text-muted">Sistema</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-end text-nowrap',
                render: function(d) {
                    let btnArchivo = d.archivo ? `<a href="/contable/uploads/facturacion/${d.archivo}" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver Adjunto"><i class="bi bi-file-earmark-pdf"></i></a>` : '';

                    return `
                        <div class="d-inline-flex gap-1 acciones-factura">
                            ${btnArchivo}
                            <button class="btn btn-sm btn-outline-success" title="Cobros (pagos del cliente)" onclick="abrirModalCobros(${d.id}, '${String(d.punto_venta).padStart(5, '0')}-${String(d.nro_factura).padStart(8, '0')}')">
                                <i class="bi bi-cash-coin"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-dark" title="Gestionar Retenciones" onclick="abrirModalRetenciones(${d.id}, '${d.punto_venta}-${d.nro_factura}')">
                                <i class="bi bi-percent"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" title="Ver Factura" onclick='verFactura(${JSON.stringify(d)})'>
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" title="Editar Factura" onclick='editarFactura(${JSON.stringify(d)})'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar Factura" onclick="eliminarFactura(${d.id})">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $(window).on('resize', function () {
        tabla.columns.adjust();
    });

    $('#neto, #iva').on('input', function(){
        let n = parseMonto($('#neto').val());
        let i = parseMonto($('#iva').val());
        $('#total').val(formatMonto(n + i));
        actualizarImporteIIBB();
    });

    // Al salir del campo se muestra con punto de miles y coma decimal
    $('.input-monto').on('blur', function(){
        $(this).val(formatMonto(parseMonto($(this).val())));
    }).on('focus', function(){
        this.select();
    });

    $('#iibb_actividad_id').on('change', actualizarImporteIIBB);

    // ==========================================
    // PARSER AFIP MEDIANTE PDF.JS
    // ==========================================
    $('#archivo_escanear').on('change', function(e) {
        let file = e.target.files[0];
        if (!file) return;

        if (file.type !== "application/pdf") {
            alert("⚠️ Por favor, seleccioná un archivo PDF oficial de AFIP.");
            $(this).val('');
            return;
        }

        let labelOriginal = $('#seccionEscaneo label').text();
        $('#seccionEscaneo label').text('⏳ Extrayendo datos del comprobante...');

        let fileReader = new FileReader();
        fileReader.onload = function() {
            let typedarray = new Uint8Array(this.result);
            
            pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
                pdf.getPage(1).then(function(page) {
                    page.getTextContent().then(function(textContent) {
                        $('#seccionEscaneo label').text(labelOriginal);
                        try {
                            // Lectura por posición (cada valor está en la misma línea que su rótulo)
                            const items = textContent.items
                                .filter(it => it.str && it.str.trim() !== '')
                                .map(it => ({ str: it.str.trim(), x: it.transform[4], y: it.transform[5] }));
                            aplicarDatosPdfAfip(analizarPdfAfip(items), file);
                        } catch (err) {
                            console.error("Error procesando contenido del PDF: ", err);
                            Swal.fire('No se pudo leer el PDF', 'Completá los datos a mano.', 'warning');
                        }
                    });
                });
            });
        };
        fileReader.readAsArrayBuffer(file);
    });
});

// ==========================================
// ASISTENTE DE CARGA: COMPROBANTE PDF DE AFIP / ARCA
// En el PDF oficial cada valor está en la misma línea que su rótulo
// (ej: "Fecha de Emisión:" y "27/08/2026" a la misma altura), así que se
// lee por posición en lugar de por orden de aparición.
// ==========================================
const AFIP_CODIGOS_COMPROBANTE = {
    '1': ['FACTURA', 'A'], '2': ['NOTA DE DEBITO', 'A'], '3': ['NOTA DE CREDITO', 'A'],
    '6': ['FACTURA', 'B'], '7': ['NOTA DE DEBITO', 'B'], '8': ['NOTA DE CREDITO', 'B'],
    '11': ['FACTURA', 'C'], '12': ['NOTA DE DEBITO', 'C'], '13': ['NOTA DE CREDITO', 'C'],
    '19': ['FACTURA', 'E'], '51': ['FACTURA', 'M']
};
const RE_IMPORTE_AFIP = /^-?[\d.]*\d,\d{2}$/;   // 1819098,28 o 1.819.098,28
const RE_FECHA_AFIP = /(\d{2})\/(\d{2})\/(\d{4})/;

function normalizarTxt(s) {
    return String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/\s+/g, ' ').trim();
}

function analizarPdfAfip(items) {
    const conRotulo = re => items.filter(i => re.test(normalizarTxt(i.str)));
    const mismaLinea = (a, b) => Math.abs(a.y - b.y) <= 3;

    // Valor más cercano a la derecha del rótulo, en la misma línea
    const valorDe = (reRotulo, reValor) => {
        for (const r of conRotulo(reRotulo)) {
            const c = items.filter(i => i !== r && mismaLinea(i, r) && i.x > r.x && reValor.test(i.str)).sort((a, b) => a.x - b.x);
            if (c.length) return c[0].str;
        }
        return '';
    };
    const fechaISO = s => { const m = String(s).match(RE_FECHA_AFIP); return m ? `${m[3]}-${m[2]}-${m[1]}` : ''; };

    const d = {};
    d.fecha = fechaISO(valorDe(/^fecha de emision/, RE_FECHA_AFIP));
    d.vencimiento = fechaISO(valorDe(/^fecha de vto\. para el pago/, RE_FECHA_AFIP));
    d.punto_venta = parseInt(valorDe(/^punto de venta/, /^\d{1,5}$/), 10) || '';
    d.numero = parseInt(valorDe(/^comp\. nro/, /^\d{1,8}$/), 10) || '';
    d.cae = valorDe(/^cae n/, /^\d{14}$/);
    d.vto_cae = valorDe(/^fecha de vto\. de cae/, RE_FECHA_AFIP);

    // Tipo: por el código ("COD. 01") o, si no está, por el texto y la letra
    const cod = items.map(i => i.str.match(/^COD\.?\s*0*(\d{1,3})$/i)).find(Boolean);
    if (cod && AFIP_CODIGOS_COMPROBANTE[cod[1]]) {
        [d.tipo, d.letra] = AFIP_CODIGOS_COMPROBANTE[cod[1]];
    } else {
        const tipoTxt = items.find(i => /^(factura|nota de credito|nota de debito)$/.test(normalizarTxt(i.str)));
        const letra = items.find(i => /^[ABCEM]$/.test(i.str));
        if (tipoTxt) d.tipo = normalizarTxt(tipoTxt.str).toUpperCase();
        if (letra) d.letra = letra.str;
    }

    // Receptor (cliente): CUIT y nombre en la línea de "Apellido y Nombre / Razón Social"
    const rotReceptor = conRotulo(/^apellido y nombre \/ razon social/)[0];
    if (rotReceptor) {
        const enLinea = items.filter(i => mismaLinea(i, rotReceptor));
        const cuit = enLinea.find(i => /^\d{11}$/.test(i.str));
        const nombre = enLinea.filter(i => i.x > rotReceptor.x).sort((a, b) => a.x - b.x)[0];
        d.cuit_cliente = cuit ? cuit.str : '';
        d.nombre_cliente = nombre ? nombre.str : '';
    }

    // Detalle: solo la columna "Producto / Servicio", entre los encabezados y los totales
    const hProd = conRotulo(/^producto \/ servicio$/)[0];
    const hCant = conRotulo(/^cantidad$/)[0];
    if (hProd) {
        const xMax = hCant ? hCant.x - 5 : hProd.x + 180;
        const yMin = Math.max(0, ...conRotulo(/^(importe otros tributos|importe neto gravado|importe total|subtotal:)/).filter(i => i.y < hProd.y).map(i => i.y));
        d.detalle = items
            .filter(i => i.y < hProd.y - 3 && i.y > yMin && i.x >= hProd.x - 5 && i.x < xMax)
            .sort((a, b) => (b.y - a.y) || (a.x - b.x))
            .map(i => i.str).join(' ').replace(/\s+/g, ' ').trim();
    }

    // Importes: neto, IVA (sumando todas las alícuotas) y total
    const num = s => s ? parseMonto(s) : null;
    d.total = num(valorDe(/^importe total/, RE_IMPORTE_AFIP));
    d.neto = num(valorDe(/^importe neto gravado/, RE_IMPORTE_AFIP));
    let iva = 0, hayIva = false;
    conRotulo(/^iva \d+(\.\d+)?%/).forEach(r => {
        const v = items.filter(i => i !== r && mismaLinea(i, r) && i.x > r.x && RE_IMPORTE_AFIP.test(i.str)).sort((a, b) => a.x - b.x)[0];
        if (v) { iva += parseMonto(v.str); hayIva = true; }
    });
    const ivaContenido = num(valorDe(/^iva contenido/, RE_IMPORTE_AFIP)); // facturas B (transparencia fiscal)
    if (hayIva) d.iva = Math.round(iva * 100) / 100;
    else if (ivaContenido !== null) d.iva = ivaContenido;
    if (d.neto === null) {
        // Facturas B / C: sin neto gravado discriminado
        const subtotal = num(valorDe(/^subtotal:/, RE_IMPORTE_AFIP));
        const base = subtotal !== null ? subtotal : d.total;
        if (base !== null) d.neto = Math.round((base - (d.iva || 0)) * 100) / 100;
        if (d.iva === undefined) d.iva = 0;
    }
    return d;
}

// Vuelca lo leído en el formulario y avisa qué se completó y qué no
function aplicarDatosPdfAfip(d, archivoPdf) {
    const ok = [], faltan = [];
    const setear = (sel, val, nombre) => { if (val !== '' && val !== null && val !== undefined) { $(sel).val(val); ok.push(nombre); } else faltan.push(nombre); };

    setear('#fecha', d.fecha, 'fecha de emisión');
    setear('#fecha_vencimiento', d.vencimiento, 'vencimiento');
    setear('#punto_venta', d.punto_venta, 'punto de venta');
    setear('#nro_factura', d.numero, 'número');

    // Tipo de comprobante (ej: "FACTURA A")
    let tipoOk = false;
    if (d.tipo && d.letra) {
        const buscado = normalizarTxt(d.tipo + ' ' + d.letra);
        const opt = $('#tipo_comprobante_id option').toArray().find(o => normalizarTxt(o.text) === buscado);
        if (opt) { $('#tipo_comprobante_id').val(opt.value); tipoOk = true; ok.push('tipo'); }
    }
    if (!tipoOk) faltan.push('tipo' + (d.tipo ? ' (' + d.tipo + ' ' + (d.letra || '') + ' no está en la lista)' : ''));

    // Cliente por el CUIT del receptor
    let avisoCliente = '';
    if (d.cuit_cliente) {
        const cliente = globalClientesParaMapeo.find(c => String(c.cuit || '').replace(/\D/g, '') === d.cuit_cliente);
        if (cliente) {
            if (buscadoresTom['#cliente_id']) buscadoresTom['#cliente_id'].setValue(cliente.id);
            else $('#cliente_id').val(cliente.id);
            ok.push('cliente');
        } else {
            faltan.push('cliente');
            avisoCliente = `<br>⚠️ El cliente <b>${escapeHtmlFac(d.nombre_cliente || '')}</b> (CUIT ${escapeHtmlFac(d.cuit_cliente)}) no está cargado en Clientes.`;
        }
    } else {
        faltan.push('cliente');
    }

    setear('#detalle', d.detalle, 'detalle');
    setear('#neto', d.neto !== null && d.neto !== undefined ? formatMonto(d.neto) : '', 'neto');
    setear('#iva', d.iva !== null && d.iva !== undefined ? formatMonto(d.iva) : '', 'IVA');
    setear('#total', d.total !== null && d.total !== undefined ? formatMonto(d.total) : '', 'total');
    actualizarImporteIIBB();

    // CAE en observaciones (si están vacías)
    if (d.cae && !$('#observaciones').val().trim()) {
        $('#observaciones').val('CAE: ' + d.cae + (d.vto_cae ? ' - Vto. CAE: ' + d.vto_cae : ''));
    }

    // El mismo PDF queda como comprobante adjunto definitivo (si no se eligió otro)
    const adj = document.getElementById('archivo');
    if (archivoPdf && adj && adj.files.length === 0) {
        try {
            const dt = new DataTransfer();
            dt.items.add(archivoPdf);
            adj.files = dt.files;
            ok.push('adjunto');
        } catch (e) { /* navegador sin DataTransfer: se adjunta a mano */ }
    }

    const cartel = document.createElement('div');
    cartel.className = 'alert ' + (faltan.length ? 'alert-warning' : 'alert-success') + ' mt-2 py-1 small text-start eval-aviso';
    cartel.innerHTML = '✨ <b>Análisis completado.</b> Se completó: ' + ok.join(', ') + '.' +
        (faltan.length ? '<br>Revisá a mano: ' + faltan.join(', ') + '.' : '') + avisoCliente +
        '<br>Verificá los campos antes de guardar.';
    $('.eval-aviso').remove();
    $('#seccionEscaneo').append(cartel);
}

function aplicarBuscadores() {
    const IDs = ['#cliente_id', '#obra_id'];

    IDs.forEach(id => {
        let el = document.querySelector(id);
        if (!el) return;

        if (buscadoresTom[id]) {
            buscadoresTom[id].destroy();
        }

        buscadoresTom[id] = new TomSelect(el, {
            create: false,
            sortField: { field: "text", order: "asc" },
            placeholder: "-- Seleccionar o Buscar --",
            allowEmptyOption: true
        });
    });
}

function cargarSelects(callback = null) {
    let peticiones = [];

    // Se quitan los buscadores para recargar las opciones del select original
    Object.keys(buscadoresTom).forEach(id => {
        buscadoresTom[id].destroy();
        delete buscadoresTom[id];
    });

    peticiones.push($.get('/contable/ajax/clientes.php?accion=listar', function(r) {
        let s = $('#cliente_id').empty().append('<option value="">Seleccione Cliente</option>');
        globalClientesParaMapeo = r.data ? r.data : [];
        globalClientesParaMapeo.forEach(c => s.append(`<option value="${c.id}">${c.nombre}</option>`));
    }, 'json'));

    peticiones.push($.get('/contable/ajax/tipos_comprobante.php?accion=listar', function(r) {
        let s = $('#tipo_comprobante_id').empty().append('<option value="">Seleccione Tipo</option>');
        if(r.data) r.data.forEach(x => s.append(`<option value="${x.id}">${x.nombre}</option>`));
    }, 'json'));

    peticiones.push($.get('/contable/ajax/centros.php?accion=listar', function(r) {
        let s = $('#centro_costo_id').empty().append('<option value="">Seleccione Centro</option>');
        if(r.data) r.data.forEach(x => s.append(`<option value="${x.id}">${x.nombre}</option>`));
    }, 'json'));

    peticiones.push($.get('/contable/ajax/obras.php?accion=listar', function(r) {
        let s = $('#obra_id').empty().append('<option value="">-- Sin Obra Asignada --</option>');
        if(r.data) r.data.forEach(x => s.append(`<option value="${x.id}">🚧 ${x.nombre}  [ID: ${x.id}]</option>`));
    }, 'json'));

    peticiones.push($.get('/contable/ajax/configuracion.php?accion=iibb_listar', function(r) {
        let s = $('#iibb_actividad_id').empty().append('<option value="">-- Sin IIBB --</option>');
        let activas = (r.data || []).filter(x => x.activo == 1);
        activas.forEach(x => {
            $('<option>')
                .val(x.id)
                .text(etiquetaIIBB(x.alicuota, x.actividad, x.tratamiento))
                .attr('data-alicuota', x.alicuota !== null ? x.alicuota : '')
                .appendTo(s);
        });
        // En una factura nueva, si hay una sola actividad activa se elige sola
        if (!$('#id').val() && activas.length === 1) s.val(activas[0].id);
        actualizarImporteIIBB();
    }, 'json'));

    // Primero se cargan los valores (al editar) y después se crean los buscadores,
    // así Tom Select arranca con la opción ya elegida
    $.when.apply($, peticiones).then(function() {
        if(callback) callback();
        aplicarBuscadores();
    });
}

window.abrirModal = function() {
    $('.eval-aviso').remove();
    $('#formFactura').find('input, textarea, select').prop('disabled', false);
    $('#btnGuardar, #seccionEscaneo').show();
    $('#seccionArchivoReal').hide();
    $('#tituloModal').text('Nueva Factura de Venta');
    $('#formFactura')[0].reset();
    $('#estado').val('DEBE');
    $('#id').val('');
    $('#archivo_actual').html('');
    $('#iibb_importe').text('');

    cargarSelects();
    modalFactura.show();
}

window.verFactura = function(data) {
    window.editarFactura(data);
    $('#tituloModal').text('Detalle de Factura (Solo Lectura)');
    $('#formFactura').find('input, textarea, select').prop('disabled', true);
    $('#btnGuardar, #seccionEscaneo').hide();
}

window.editarFactura = function(data) {
    $('.eval-aviso').remove();
    $('#formFactura').find('input, textarea, select').prop('disabled', false);
    $('#btnGuardar').show();
    $('#seccionEscaneo').hide(); 
    $('#seccionArchivoReal').show();
    $('#tituloModal').text('Editar Factura');
    $('#formFactura')[0].reset();
    
    $('#id').val(data.id);
    $('#fecha').val(data.fecha);
    $('#punto_venta').val(data.punto_venta);
    $('#nro_factura').val(data.nro_factura);
    $('#fecha_vencimiento').val(data.fecha_vencimiento);
    $('#detalle').val(data.detalle);
    $('#neto').val(formatMonto(data.neto));
    $('#iva').val(formatMonto(data.iva));
    $('#total').val(formatMonto(data.total));
    $('#iibb_importe').text('');
    $('#observaciones').val(data.observaciones);
    $('#estado').val(data.estado ? data.estado : 'DEBE');

    cargarSelects(function() {
        $('#tipo_comprobante_id').val(data.tipo_comprobante_id);
        $('#cliente_id').val(data.cliente_id);
        $('#centro_costo_id').val(data.centro_costo_id);
        $('#obra_id').val(data.obra_id || "");

        // IIBB: se muestra la alícuota guardada en la factura (puede diferir de la actual)
        // y si la actividad ya no está activa se agrega igual al select
        let selIIBB = $('#iibb_actividad_id');
        if (data.iibb_actividad_id) {
            let opt = selIIBB.find(`option[value="${data.iibb_actividad_id}"]`);
            if (!opt.length) opt = $('<option>').val(data.iibb_actividad_id).appendTo(selIIBB);
            opt.text(etiquetaIIBB(data.iibb_alicuota, data.iibb_actividad || 'Actividad eliminada', data.iibb_tratamiento))
               .attr('data-alicuota', data.iibb_alicuota !== null ? data.iibb_alicuota : '');
            selIIBB.val(data.iibb_actividad_id);
        } else {
            selIIBB.val('');
        }
        actualizarImporteIIBB();
    });

    if(data.archivo){
        $('#archivo_actual').html(`<a href="/contable/uploads/facturacion/${data.archivo}" target="_blank" class="btn btn-sm btn-dark">Ver archivo actual</a>`);
    } else {
        $('#archivo_actual').html('');
    }
    
    modalFactura.show();
}

$('#formFactura').submit(function(e) {
    e.preventDefault();
    let formData = new FormData(this);

    // Los importes se muestran como 1.234,56 pero al servidor van como número (1234.56)
    ['neto', 'iva', 'total'].forEach(campo => {
        formData.set(campo, parseMonto($('#' + campo).val()).toFixed(2));
    });

    let inputReal = document.getElementById('archivo');
    let inputEscaneo = document.getElementById('archivo_escanear');
    
    if ((!inputReal || inputReal.files.length === 0) && (inputEscaneo && inputEscaneo.files.length > 0)) {
        formData.set('archivo', inputEscaneo.files[0]);
    }

    $.ajax({
        url: '/contable/ajax/facturacion.php?accion=guardar',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(resp) {
            tabla.ajax.reload(null, false);
            modalFactura.hide();
        }
    });
});

window.eliminarFactura = function(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer y eliminará el registro contable.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#212529', 
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/contable/ajax/facturacion.php?accion=eliminar', { id: id }, function(resp) {
                Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: 'El comprobante fue borrado con éxito.',
                    timer: 1500,
                    showConfirmButton: false
                });
                tabla.ajax.reload(null, false);
            }).fail(function() {
                Swal.fire('Error', 'No se pudo eliminar el registro contable.', 'error');
            });
        }
    });
}

window.filtrarPestaña = function(tipoPestaña) {
    estadoPestañaActual = tipoPestaña;
    tabla.draw();
}

function actualizarBadgesPestañas(datosFilas) {
    let contadorCobrar = 0;
    let contadorPagadas = 0;

    datosFilas.forEach(function(fila) {
        let factura = fila._aData;
        let estado = factura.estado ? factura.estado.toUpperCase() : 'DEBE';

        if (estado === 'DEBE' || estado === 'VER' || estado === 'PARCIAL') {
            contadorCobrar++;
        } else if (estado === 'PAGADO') {
            contadorPagadas++;
        }
    });

    $('#cant-cobrar').text(contadorCobrar);
    $('#cant-pagadas').text(contadorPagadas);
}

// ==========================================
// FUNCIONES GLOBALES DE RETENCIONES
// ==========================================
window.abrirModalRetenciones = function(facturaId, nroFacturaCompleto) {
    $('#formRetencion')[0].reset();
    $('#ret_factura_id').val(facturaId);
    $('#lblFacturaRetencion').text(nroFacturaCompleto);
    $('#ret_fecha').val(new Date().toISOString().split('T')[0]); // Fecha de hoy por defecto
    
    // 1. Poblamos el select desde el nuevo módulo de configuración
    $.get('/contable/ajax/tipos_retenciones.php?accion=listar', function(r) {
        let select = $('#ret_tipo_retencion_id').empty();
        select.append('<option value="">Seleccione un tipo...</option>');
        if(r.data && r.data.length > 0) {
            r.data.forEach(tipo => {
                select.append(`<option value="${tipo.id}">${tipo.nombre}</option>`);
            });
        } else {
            select.append('<option value="">No hay retenciones configuradas</option>');
        }
    }, 'json');

    // 2. Cargamos el historial de retenciones de la factura
    refrescarTablaRetenciones(facturaId);
    modalRetencionesBS.show();
}

function refrescarTablaRetenciones(facturaId) {
    $.get('/contable/ajax/facturacion.php?accion=listar_retenciones', { factura_id: facturaId }, function(r) {
        let tbody = $('#tbodyRetenciones').empty();
        if(r.success && r.data.length > 0) {
            r.data.forEach(x => {
                let fecha = x.fecha_retencion.split('-').reverse().join('/');
                let monto = '$ ' + Number(x.importe).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                let btnAdjunto = x.archivo ? `<a href="/contable/uploads/retenciones/${x.archivo}" target="_blank" class="btn btn-xs btn-outline-danger py-0 px-1" title="Ver Adjunto"><i class="bi bi-file-pdf"></i></a>` : '<span class="text-muted small">Sin archivo</span>';
                
                tbody.append(`
                    <tr>
                        <td>${fecha}</td>
                        <td><span class="badge bg-light text-dark border">${x.tipo_nombre}</span></td>
                        <td>${x.nro_certificado}</td>
                        <td class="text-end fw-bold">${monto}</td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                ${btnAdjunto}
                                <button class="btn btn-xs btn-outline-danger py-0 px-1" onclick="eliminarRetencion(${x.id}, ${facturaId})"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                `);
            });
        } else {
            tbody.append('<tr><td colspan="5" class="text-center text-muted py-3">No hay retenciones cargadas para esta factura.</td></tr>');
        }
    }, 'json');
}

window.eliminarRetencion = function(id, facturaId) {
    if(confirm('¿Desea eliminar este registro de retención?')) {
        $.post('/contable/ajax/facturacion.php?accion=eliminar_retencion', { id: id }, function() {
            refrescarTablaRetenciones(facturaId);
            tabla.ajax.reload(null, false); // la retención cambia el saldo y el estado
        });
    }
}

// ==========================================
// COBROS DE UNA FACTURA
// ==========================================
let modalCobrosBS;
const SESION_ROL_FAC = <?= json_encode(strtolower($_SESSION['rol'] ?? '')) ?>;

function escapeHtmlFac(s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

window.abrirModalCobros = function(facturaId, nroFactura) {
    if (!modalCobrosBS) modalCobrosBS = new bootstrap.Modal(document.getElementById('modalCobros'));
    $('#formCobro')[0].reset();
    $('#cob_factura_id').val(facturaId);
    $('#lblFacturaCobro').text(nroFactura);
    $('#cob_fecha').val(new Date().toISOString().split('T')[0]);

    // Medios de pago (Configuración → Medios de pago)
    $.get('/contable/ajax/medios_pago.php?accion=listar', function(r) {
        let s = $('#cob_medio_pago_id').empty().append('<option value="">-- Sin especificar --</option>');
        (r.data || []).forEach(m => s.append($('<option>').val(m.id).text(m.nombre)));
    }, 'json');

    // Cajas activas donde el usuario puede cargar movimientos
    $.get('/contable/ajax/cajas.php?accion=listar', function(r) {
        const puedeBloqueadas = SESION_ROL_FAC === 'admin' || SESION_ROL_FAC === 'contador';
        let s = $('#cob_caja_id').empty().append('<option value="">-- No registrar en caja --</option>');
        (r.data || []).forEach(c => {
            if (c.activa != 1) return;
            if (!puedeBloqueadas && c.permite_movimientos == 0) return;
            s.append($('<option>').val(c.id).text(c.nombre));
        });
    }, 'json');

    refrescarCobros(facturaId, true);
    modalCobrosBS.show();
}

function refrescarCobros(facturaId, completarImporte) {
    $.get('/contable/ajax/facturacion.php?accion=listar_cobros', { factura_id: facturaId }, function(r) {
        let tbody = $('#tbodyCobros').empty();
        if (!r.success) {
            tbody.append(`<tr><td colspan="5" class="text-center text-danger py-3">${escapeHtmlFac(r.message || 'No se pudieron cargar los cobros.')}</td></tr>`);
            return;
        }

        const res = r.resumen || { total: 0, cobrado: 0, retenido: 0, saldo: 0 };
        $('#cobResTotal').text('$ ' + formatMonto(res.total));
        $('#cobResCobrado').text('$ ' + formatMonto(res.cobrado));
        $('#cobResRetenido').text('$ ' + formatMonto(res.retenido));
        $('#cobResSaldo').text('$ ' + formatMonto(res.saldo)).toggleClass('text-success', res.saldo <= 0.01).toggleClass('text-danger', res.saldo > 0.01);

        // Sin saldo no se pueden cargar más cobros
        const sinSaldo = res.saldo <= 0.01;
        $('#formCobro :input').prop('disabled', sinSaldo);
        if (completarImporte || sinSaldo) $('#cob_importe').val(sinSaldo ? '' : formatMonto(res.saldo));

        if (r.data.length === 0) {
            tbody.append('<tr><td colspan="5" class="text-center text-muted py-3">No hay cobros cargados para esta factura.</td></tr>');
            return;
        }
        r.data.forEach(x => {
            const fecha = x.fecha.split('-').reverse().join('/');
            const medio = [x.medio_pago, x.caja ? '<i class="bi bi-wallet2"></i> ' + escapeHtmlFac(x.caja) : ''].filter(Boolean).map(t => t.startsWith('<') ? t : escapeHtmlFac(t)).join('<br>') || '<span class="text-muted">-</span>';
            const btnAdjunto = x.archivo ? `<a href="/contable/uploads/cobros/${encodeURIComponent(x.archivo)}" target="_blank" class="btn btn-xs btn-outline-dark py-0 px-1" title="Ver comprobante"><i class="bi bi-file-earmark"></i></a>` : '';
            tbody.append(`
                <tr title="${escapeHtmlFac(x.observaciones || '')}">
                    <td>${fecha}</td>
                    <td class="small">${medio}</td>
                    <td>${escapeHtmlFac(x.comprobante || '-')}</td>
                    <td class="text-end fw-bold">$ ${formatMonto(x.importe)}</td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            ${btnAdjunto}
                            <button class="btn btn-xs btn-outline-danger py-0 px-1" title="Eliminar cobro" onclick="eliminarCobro(${x.id}, ${facturaId})"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>`);
        });
    }, 'json');
}

document.getElementById('formCobro').addEventListener('submit', function(e) {
    e.preventDefault();
    const facId = $('#cob_factura_id').val();
    let fd = new FormData(this);
    fd.set('cob_importe', parseMonto($('#cob_importe').val())); // "1.500,50" -> 1500.5
    const btn = $('#btnGuardarCobro').prop('disabled', true);

    $.ajax({
        url: '/contable/ajax/facturacion.php?accion=guardar_cobro',
        type: 'POST',
        data: fd,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(r) {
            if (!r.success) {
                Swal.fire('No se guardó', r.message || 'No se pudo guardar el cobro.', 'warning');
                return;
            }
            const caja = $('#cob_caja_id').val(), medio = $('#cob_medio_pago_id').val();
            $('#formCobro')[0].reset();
            $('#cob_caja_id').val(caja); $('#cob_medio_pago_id').val(medio); // se mantienen para el próximo
            $('#cob_fecha').val(new Date().toISOString().split('T')[0]);
            refrescarCobros(facId, true);
            tabla.ajax.reload(null, false);
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cobro registrado', timer: 1500, showConfirmButton: false });
        },
        error: function() { Swal.fire('Error', 'Ocurrió un problema de comunicación al guardar.', 'error'); },
        complete: function() { btn.prop('disabled', false); }
    });
});

window.eliminarCobro = function(id, facturaId) {
    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar este cobro?',
        text: 'Si se registró en una caja, también se borra ese ingreso.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        reverseButtons: true
    }).then(function(res) {
        if (!res.isConfirmed) return;
        $.post('/contable/ajax/facturacion.php?accion=eliminar_cobro', { id: id }, function(r) {
            if (!r.success) {
                Swal.fire('No se eliminó', r.message || 'No se pudo eliminar el cobro.', 'warning');
                return;
            }
            refrescarCobros(facturaId, true);
            tabla.ajax.reload(null, false);
        }, 'json');
    });
}
</script>