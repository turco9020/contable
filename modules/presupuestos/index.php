<?php
require_once '../../config/database.php';

echo "<script>localStorage.setItem('menuOperaciones', 'closed'); localStorage.setItem('menuConfig', 'closed');</script>";

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content p-4 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-file-earmark-text text-secondary me-2"></i> Módulo de Presupuestos
        </h4>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal()">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Presupuesto
        </button>
    </div>

    <!-- FILTROS -->
    <div class="card p-3 shadow-sm mb-3 border-0">
        <div class="row g-2">
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Desde</label>
                <input type="date" id="f_desde" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Hasta</label>
                <input type="date" id="f_hasta" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted">Cliente</label>
                <select id="f_cliente" class="form-select form-select-sm"></select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Estado</label>
                <select id="f_estado" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    <option value="BORRADOR">BORRADOR</option>
                    <option value="ENVIADO">ENVIADO</option>
                    <option value="APROBADO">APROBADO</option>
                    <option value="RECHAZADO">RECHAZADO</option>
                </select>
            </div>
            <div class="col-md-3 d-flex justify-content-end align-items-end">
                <button class="btn btn-sm btn-dark me-2 px-3" onclick="aplicarFiltros()">Filtrar</button>
                <button class="btn btn-sm btn-outline-secondary px-3" onclick="limpiarFiltros()">Limpiar</button>
            </div>
        </div>
    </div>

    <!-- TABLA PRINCIPAL -->
    <div class="card p-3 shadow-sm border-0">
        <div class="table-responsive">
            <table id="tablaPresupuestos" class="table table-bordered table-striped w-100">
                <thead class="table-dark">
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Obra</th>
                        <th>Título / Proyecto</th>
                        <th>Costo Directo</th>
                        <th>Coef.</th>
                        <th>Total Cliente</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PRESUPUESTO -->
<div class="modal fade" id="modalPresupuesto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalPresupuestoTitulo"><i class="bi bi-file-earmark-plus me-2"></i> Presupuesto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPresupuesto">
                <div class="modal-body">
                    <input type="hidden" id="presupuesto_id" name="id">

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Cliente</label>
                            <select class="form-select" id="cliente_id" name="cliente_id" required></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Obra (Opcional)</label>
                            <select class="form-select" id="obra_id" name="obra_id"></select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="BORRADOR">BORRADOR</option>
                                <option value="ENVIADO">ENVIADO</option>
                                <option value="APROBADO">APROBADO</option>
                                <option value="RECHAZADO">RECHAZADO</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Título / Proyecto / Referencia</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ej: PROVISIÓN Y MONTAJE ESTRUCTURA METÁLICA">
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- TABLA DINÁMICA DE ITEMS -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-secondary"><i class="bi bi-list-task me-1"></i> Detalle de Ítems / Costos Directos</h6>
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="agregarRenglon()">
                            <i class="bi bi-plus-lg me-1"></i> Agregar Ítem
                        </button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered align-middle" id="tablaItems">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45%;">Descripción / Tarea</th>
                                    <th style="width: 12%;">Unidad</th>
                                    <th style="width: 12%;">Cantidad</th>
                                    <th style="width: 15%;">Costo Unit. ($)</th>
                                    <th style="width: 15%;">Costo Subtotal ($)</th>
                                    <th style="width: 5%;" class="text-center"><i class="bi bi-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyItems">
                                <!-- Filas JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- SECCIÓN RESUMEN COEFICIENTE -->
                    <div class="row justify-content-end align-items-center bg-light p-3 rounded mx-0 mb-3 border">
                        <div class="col-md-3 text-end">
                            <label class="fw-bold small text-muted">Costo Directo Total:</label>
                            <div class="fs-6 fw-bold text-dark" id="lblCostoDirecto">$ 0,00</div>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold small text-muted">Coeficiente Resumen:</label>
                            <input type="number" step="0.001" min="1" class="form-control form-control-sm fw-bold text-center" id="coeficiente" name="coeficiente" value="1.350" oninput="calcularPresupuesto()">
                        </div>
                        <div class="col-md-4 text-end">
                            <label class="fw-bold small text-primary">PRECIO TOTAL CLIENTE:</label>
                            <div class="fs-4 fw-bold text-primary" id="lblMontoTotal">$ 0,00</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Condiciones Comerciales / Observaciones</label>
                            <textarea class="form-control" id="condiciones" name="condiciones" rows="3" placeholder="Validez de oferta, plazos de ejecución, forma de pago..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark px-4"><i class="bi bi-save me-1"></i> Guardar Presupuesto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
let tablaPresupuestos;
let modalPresupuesto;
let tomClientes, tomObras, tomFiltroCliente;

function renderMoneda(val) {
    if (!val || val == 0) return '$ 0,00';
    let num = parseFloat(val);
    if (isNaN(num)) return '$ 0,00';
    return '$ ' + num.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function cargarClientes() {
    return $.get('/contable/ajax/clientes.php?accion=listar', r => {
        let options = '<option value="">-- Seleccionar --</option>';
        r.data.forEach(c => { options += `<option value="${c.id}">${c.nombre}</option>`; });
        $('#cliente_id').html(options);
        $('#f_cliente').html('<option value="">-- Todos los Clientes --</option>' + options);
    }, 'json');
}

function cargarObras() {
    return $.get('/contable/ajax/obras.php?accion=listar', r => {
        let options = '<option value="">-- Seleccionar --</option>';
        r.data.forEach(o => { options += `<option value="${o.id}">${o.nombre}</option>`; });
        $('#obra_id').html(options);
    }, 'json');
}

function agregarRenglon(desc = '', uni = 'GL', cant = 1, cost = 0) {
    let tr = `
        <tr>
            <td><input type="text" class="form-control form-control-sm item-desc" value="${desc}" required placeholder="Detalle del trabajo..."></td>
            <td><input type="text" class="form-control form-control-sm item-uni" value="${uni}"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm item-cant text-end" value="${cant}" oninput="calcularPresupuesto()"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm item-cost text-end" value="${cost}" oninput="calcularPresupuesto()"></td>
            <td><input type="text" class="form-control form-control-sm item-subt text-end bg-light" readonly value="$ 0,00"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="$(this).closest('tr').remove(); calcularPresupuesto();">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#tbodyItems').append(tr);
    calcularPresupuesto();
}

function calcularPresupuesto() {
    let costoDirectoTotal = 0;

    $('#tbodyItems tr').each(function() {
        let cant = parseFloat($(this).find('.item-cant').val()) || 0;
        let cost = parseFloat($(this).find('.item-cost').val()) || 0;
        let subt = cant * cost;

        costoDirectoTotal += subt;
        $(this).find('.item-subt').val(renderMoneda(subt));
    });

    let coef = parseFloat($('#coeficiente').val()) || 1;
    let montoTotalCliente = costoDirectoTotal * coef;

    $('#lblCostoDirecto').text(renderMoneda(costoDirectoTotal));
    $('#lblMontoTotal').text(renderMoneda(montoTotalCliente));
}

function abrirModal() {
    $('#formPresupuesto')[0].reset();
    $('#presupuesto_id').val('');
    $('#tbodyItems').empty();
    agregarRenglon();

    if (tomClientes) tomClientes.setValue('');
    if (tomObras) tomObras.setValue('');

    $('#modalPresupuestoTitulo').html('<i class="bi bi-file-earmark-plus me-2"></i> Nuevo Presupuesto');
    modalPresupuesto.show();
}

function aplicarFiltros() {
    tablaPresupuestos.ajax.reload();
}

function limpiarFiltros() {
    $('#f_desde, #f_hasta, #f_estado').val('');
    if (tomFiltroCliente) tomFiltroCliente.setValue('');
    tablaPresupuestos.ajax.reload();
}

document.addEventListener("DOMContentLoaded", function() {
    modalPresupuesto = new bootstrap.Modal(document.getElementById('modalPresupuesto'));

    $.when(cargarClientes(), cargarObras()).done(() => {
        tomClientes = new TomSelect('#cliente_id', { create: false, placeholder: '-- Seleccionar --' });
        tomObras = new TomSelect('#obra_id', { create: false, placeholder: '-- Seleccionar --' });
        tomFiltroCliente = new TomSelect('#f_cliente', { create: false, placeholder: '-- Todos los Clientes --' });
    });

    tablaPresupuestos = $('#tablaPresupuestos').DataTable({
        ajax: {
            url: '/contable/modules/presupuestos/ajax/presupuestos.php?accion=listar',
            data: function(d) {
                d.f_desde = $('#f_desde').val();
                d.f_hasta = $('#f_hasta').val();
                d.f_cliente = $('#f_cliente').val();
                d.f_estado = $('#f_estado').val();
            }
        },
        columns: [
            { data: 'numero', className: 'fw-bold' },
            { data: 'fecha', render: d => d ? d.split('-').reverse().join('/') : '' },
            { data: 'cliente_nombre', defaultContent: '-' },
            { data: 'obra_nombre', defaultContent: '-' },
            { data: 'titulo' },
            { data: 'costo_directo', className: 'text-end', render: d => renderMoneda(d) },
            { data: 'coeficiente', className: 'text-center', render: d => parseFloat(d).toFixed(3) },
            { data: 'monto_total', className: 'text-end fw-bold text-primary', render: d => renderMoneda(d) },
            { 
                data: 'estado',
                render: function(d) {
                    let badge = 'bg-secondary';
                    if (d === 'ENVIADO') badge = 'bg-info text-dark';
                    if (d === 'APROBADO') badge = 'bg-success';
                    if (d === 'RECHAZADO') badge = 'bg-danger';
                    return `<span class="badge ${badge}">${d}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: function(data) {
                    return `
                        <div class="d-inline-flex gap-1">
                            <a href="/contable/modules/presupuestos/pdf.php?id=${data.id}" target="_blank" class="btn btn-sm btn-outline-dark" title="Imprimir PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-primary" onclick="editar(${data.id})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${data.id})" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#formPresupuesto').submit(function(e) {
        e.preventDefault();

        let items = [];
        $('#tbodyItems tr').each(function() {
            items.push({
                descripcion: $(this).find('.item-desc').val(),
                unidad: $(this).find('.item-uni').val(),
                cantidad: $(this).find('.item-cant').val(),
                costo_unitario: $(this).find('.item-cost').val()
            });
        });

        if (items.length === 0) {
            Swal.fire('Error', 'Debe agregar al menos un ítem al presupuesto.', 'warning');
            return;
        }

        let formData = {
            id: $('#presupuesto_id').val(),
            fecha: $('#fecha').val(),
            cliente_id: $('#cliente_id').val(),
            obra_id: $('#obra_id').val(),
            titulo: $('#titulo').val(),
            coeficiente: $('#coeficiente').val(),
            condiciones: $('#condiciones').val(),
            estado: $('#estado').val(),
            items: items
        };

        $.post('/contable/modules/presupuestos/ajax/presupuestos.php?accion=guardar', formData, function(res) {
            if (res.status === 'OK') {
                Swal.fire({ title: '¡Guardado!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
                modalPresupuesto.hide();
                tablaPresupuestos.ajax.reload();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    window.editar = function(id) {
        $.get('/contable/modules/presupuestos/ajax/presupuestos.php?accion=obtener', { id }, function(res) {
            if (res.status === 'OK') {
                let p = res.data;
                $('#presupuesto_id').val(p.id);
                $('#fecha').val(p.fecha);
                $('#titulo').val(p.titulo);
                $('#coeficiente').val(p.coeficiente);
                $('#condiciones').val(p.condiciones);
                $('#estado').val(p.estado);

                if (tomClientes) tomClientes.setValue(p.cliente_id);
                if (tomObras) tomObras.setValue(p.obra_id);

                $('#tbodyItems').empty();
                if (p.items && p.items.length > 0) {
                    p.items.forEach(it => {
                        agregarRenglon(it.descripcion, it.unidad, it.cantidad, it.costo_unitario);
                    });
                } else {
                    agregarRenglon();
                }

                $('#modalPresupuestoTitulo').html('<i class="bi bi-pencil me-2"></i> Editar Presupuesto ' + p.numero);
                modalPresupuesto.show();
            }
        }, 'json');
    };

    window.eliminar = function(id) {
        Swal.fire({
            title: '¿Eliminar Presupuesto?',
            text: 'Esta acción borrará el presupuesto y sus ítems.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Eliminar'
        }).then((res) => {
            if (res.isConfirmed) {
                $.post('/contable/modules/presupuestos/ajax/presupuestos.php?accion=eliminar', { id }, function() {
                    tablaPresupuestos.ajax.reload();
                });
            }
        });
    };
});
</script>