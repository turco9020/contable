<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/sidebar.php';
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>🧾 Facturación (Ventas)</h3>
        <button class="btn btn-dark" onclick="abrirModal()">
            + Nueva Factura
        </button>
    </div>

    <div class="card p-3 shadow-sm">
        <table id="tablaFacturas" class="table table-bordered table-striped w-100">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Nro. Factura</th>
                    <th>Cliente</th>
                    <th>Centro Costo</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

</div>

<div class="modal fade" id="modalFactura" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Factura de Venta</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formFactura" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Emisión</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo Comprobante</label>
                            <select name="tipo_comprobante_id" id="tipo_comprobante_id" class="form-control" required>
                                </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Punto de Venta / Número</label>
                            <div class="input-group">
                                <input type="number" name="punto_venta" id="punto_venta" class="form-control" placeholder="00005" style="max-width: 90px;" required>
                                <span class="input-group-text">-</span>
                                <input type="number" name="nro_factura" id="nro_factura" class="form-control" placeholder="00001234" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Fecha Vencimiento Pago</label>
                            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <select name="cliente_id" id="cliente_id" class="form-control" required>
                                </select>
                        </div>

                        <div class="col-md-4">
                              <label class="form-label fw-bold">⚠️ Centro de Costo</label>
                             <select name="centro_costo_id" id="centro_costo_id" class="form-select border-danger bg-warning bg-opacity-10 text-dark" required>
                           </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Detalle (Lo que dice la factura)</label>
                            <textarea name="detalle" id="detalle" class="form-control" rows="3" placeholder="Descripción de los artículos o servicios prestados..." required></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Importe Neto</label>
                            <input type="number" step="0.01" name="neto" id="neto" class="form-control" value="0.00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">IVA</label>
                            <input type="number" step="0.01" name="iva" id="iva" class="form-control" value="0.00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Importe Total</label>
                            <input type="number" step="0.01" name="total" id="total" class="form-control" value="0.00" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observaciones Internas</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Notas del usuario que carga..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Adjuntar Archivo</label>
                            <input type="file" name="archivo" id="archivo" class="form-control">
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

<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/footer.php'; ?>

<script>
let tabla;
let modalFactura;

document.addEventListener("DOMContentLoaded", function() {
    modalFactura = new bootstrap.Modal(document.getElementById('modalFactura'));

    // Cargar selects desde DB al iniciar
    cargarSelects();

    // Inicializar DataTable con los nuevos campos
    tabla = $('#tablaFacturas').DataTable({
        ajax: '/contable/ajax/facturacion.php?accion=listar',
        order: [[0, 'desc']],
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
                    let pv = String(row.punto_venta).padStart(5, '0');
                    let nf = String(row.nro_factura).padStart(8, '0');
                    return `${pv}-${nf}`;
                }
            },
            { data: 'cliente_nombre' },
            { data: 'centro_costo_nombre' },
            { 
                data: 'total',
                render: function(d) {
                    return '$ ' + Number(d).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                }
            },
            {
                data: null,
                orderable: false,
                render: function(d) {
                    let btnArchivo = d.archivo ? `<a href="/contable/uploads/facturacion/${d.archivo}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver Adjunto">📁</a>` : '';
                    return `
                        <div class="d-flex gap-1">
                            ${btnArchivo}
                            <button class="btn btn-sm btn-secondary" onclick='verFactura(${JSON.stringify(d)})'>Ver</button>
                            <button class="btn btn-sm btn-primary" onclick='editarFactura(${JSON.stringify(d)})'>Editar</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarFactura(${d.id})">Eliminar</button>
                        </div>
                    `;
                }
            }
        ]
    });

    // Auto-calcular Total dinámico cuando cambia Neto o IVA
    $('#neto, #iva').on('input', function(){
        let n = parseFloat($('#neto').val()) || 0;
        let i = parseFloat($('#iva').val()) || 0;
        $('#total').val((n + i).toFixed(2));
    });
});

function cargarSelects() {
    // 1. Clientes
    $.get('/contable/ajax/clientes.php?accion=listar', function(r) {
        let s = $('#cliente_id').empty().append('<option value="">Seleccione Cliente</option>');
        let registros = r.data ? r.data : (Array.isArray(r) ? r : []);
        registros.forEach(c => s.append(`<option value="${c.id}">${c.nombre}</option>`));
    }, 'json');

    // 2. Tipos de Comprobante
    $.get('/contable/ajax/tipos_comprobante.php?accion=listar', function(r) {
        let s = $('#tipo_comprobante_id').empty().append('<option value="">Seleccione Tipo</option>');
        let registros = r.data ? r.data : (Array.isArray(r) ? r : []);
        registros.forEach(x => s.append(`<option value="${x.id}">${x.nombre}</option>`));
    }, 'json');

    // 3. Centros de Costos (A prueba de fallos de estructura)
    $.get('/contable/ajax/centros.php?accion=listar', function(r) {
        let s = $('#centro_costo_id').empty().append('<option value="">Seleccione Centro</option>');
        
        // Si el archivo devuelve {data: [...]}, usamos r.data. Si devuelve el array directo [...], usamos r.
        let registros = r.data ? r.data : (Array.isArray(r) ? r : []);
        
        if (registros.length === 0) {
            console.warn("No se recibieron datos de centros_costos o el formato no es compatible.", r);
        }

        registros.forEach(x => {
            s.append(`<option value="${x.id}">${x.nombre}</option>`);
        });
    }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error crítico al cargar centros.php: ", textStatus, errorThrown);
    });
}

window.abrirModal = function() {
    $('#formFactura input, textarea, select').prop('disabled', false);
    $('#btnGuardar').show();
    $('#tituloModal').text('Nueva Factura de Venta');
    $('#formFactura')[0].reset();
    $('#id').val('');
    $('#archivo_actual').html('');
    modalFactura.show();
}

window.verFactura = function(data) {
    window.editarFactura(data);
    $('#tituloModal').text('Detalle de Factura (Solo Lectura)');
    $('#formFactura input, textarea, select').prop('disabled', true);
    $('#btnGuardar').hide();
}

window.editarFactura = function(data) {
    $('#formFactura input, textarea, select').prop('disabled', false);
    $('#btnGuardar').show();
    $('#tituloModal').text('Editar Factura');
    $('#formFactura')[0].reset();
    
    $('#id').val(data.id);
    $('#fecha').val(data.fecha);
    $('#tipo_comprobante_id').val(data.tipo_comprobante_id);
    $('#cliente_id').val(data.cliente_id);
    $('#punto_venta').val(data.punto_venta);
    $('#nro_factura').val(data.nro_factura);
    $('#fecha_vencimiento').val(data.fecha_vencimiento);
    $('#detalle').val(data.detalle);
    $('#neto').val(data.neto);
    $('#iva').val(data.iva);
    $('#total').val(data.total);
    $('#observaciones').val(data.observaciones);
    $('#centro_costo_id').val(data.centro_costo_id);

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

    $.ajax({
        url: '/contable/ajax/facturacion.php?accion=guardar',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(resp) {
            tabla.ajax.reload();
            modalFactura.hide();
        }
    });
});

window.eliminarFactura = function(id) {
    if (!confirm('¿Eliminar esta factura?')) return;
    if (prompt('Escribí OK para confirmar') !== 'OK') return;

    $.post('/contable/ajax/facturacion.php?accion=eliminar', { id }, function(resp) {
        tabla.ajax.reload();
    });
}
</script>