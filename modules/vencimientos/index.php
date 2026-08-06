<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Cargar combos
$obras = $conn->query("SELECT id, nombre FROM obras ORDER BY nombre ASC");
$proveedores = $conn->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC");
$cajas = $conn->query("SELECT id, nombre FROM cajas WHERE activa = 1 ORDER BY nombre ASC");
?>

<div class="content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="bi bi-calendar-event me-2"></i>Control de Vencimientos</h3>
            <p class="text-muted small mb-0">Gestión de compromisos de pago, alertas y adjuntos.</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="nuevoVencimiento()">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Vencimiento
        </button>
    </div>

    <!-- TARJETAS DE RESUMEN / ESTADÍSTICAS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <span class="text-muted small fw-bold">VENCIDOS</span>
                    <h4 class="fw-bold text-danger mb-0" id="cntVencidos">$ 0.00</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <span class="text-muted small fw-bold">PRÓXIMOS (7 DÍAS)</span>
                    <h4 class="fw-bold text-warning mb-0" id="cntProximos">$ 0.00</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <span class="text-muted small fw-bold">PENDIENTES TOTALES</span>
                    <h4 class="fw-bold text-primary mb-0" id="cntPendientes">$ 0.00</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <span class="text-muted small fw-bold">PAGADOS ESTE MES</span>
                    <h4 class="fw-bold text-success mb-0" id="cntPagados">$ 0.00</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA CRUD -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle w-100" id="tablaVencimientos">
                    <thead class="table-dark">
                        <tr>
                            <th>Estado</th>
                            <th>Título / Detalle</th>
                            <th>Vencimiento</th>
                            <th>Categoría</th>
                            <th>Proveedor / Obra</th>
                            <th>Monto</th>
                            <th>Adjunto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL GUARDAR / EDITAR -->
<div class="modal fade" id="modalVencimiento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formVencimiento" enctype="multipart/form-data">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalTitle">Nuevo Vencimiento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="vencimiento_id">
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Título / Concepto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titulo" id="titulo" required placeholder="Ej: Pago Servicio EPE / Impuesto IIBB">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Monto ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="monto" id="monto" required placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fecha Vencimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_vencimiento" id="fecha_vencimiento" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Categoría</label>
                            <select class="form-select" name="categoria" id="categoria">
                                <option value="Impuestos">Impuestos</option>
                                <option value="Servicios">Servicios</option>
                                <option value="Proveedores">Proveedores</option>
                                <option value="Alquileres">Alquileres</option>
                                <option value="General" selected>General</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Aviso previo (Días)</label>
                            <input type="number" class="form-control" name="dias_aviso" id="dias_aviso" value="7" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Proveedor Asociado</label>
                            <select class="form-select" name="proveedor_id" id="proveedor_id">
                                <option value="">-- Seleccionar (Opcional) --</option>
                                <?php while ($p = $proveedores->fetch_assoc()): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Obra Asociada</label>
                            <select class="form-select" name="obra_id" id="obra_id">
                                <option value="">-- Seleccionar (Opcional) --</option>
                                <?php while ($o = $obras->fetch_assoc()): ?>
                                    <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descripción / Observaciones</label>
                            <textarea class="form-control" name="descripcion" id="descripcion" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Adjuntar Archivo (PDF, Imagen, Factura)</label>
                            <input type="file" class="form-control" name="archivo" id="archivo">
                            <div id="archivoActual" class="mt-1"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar Vencimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL MARCAR COMO PAGADO -->
<div class="modal fade" id="modalPagar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPagar">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle me-1"></i> Marcar Vencimiento como Pagado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="pagar_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de Pago</label>
                        <input type="datetime-local" class="form-control" name="fecha_pago" id="fecha_pago" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Caja de Egreso (Opcional)</label>
                        <select class="form-select" name="caja_id" id="pagar_caja_id">
                            <option value="">-- Ninguna / Solo cambiar estado --</option>
                            <?php 
                            $cajas->data_seek(0);
                            while ($c = $cajas->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
let dataTableVencimientos;

$(document).ready(function() {
    cargarTabla();

    $('#formVencimiento').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        
        $.ajax({
            url: 'guardar_vencimiento.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Éxito', res.message, 'success');
                    $('#modalVencimiento').modal('hide');
                    cargarTabla();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
    });

    $('#formPagar').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'cambiar_estado.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Pagado', res.message, 'success');
                    $('#modalPagar').modal('hide');
                    cargarTabla();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
    });
});

function cargarTabla() {
    $.ajax({
        url: 'obtener_vencimientos.php',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.status) return;

            // Actualizar Tarjetas
            $('#cntVencidos').text('$ ' + res.resumen.vencidos);
            $('#cntProximos').text('$ ' + res.resumen.proximos);
            $('#cntPendientes').text('$ ' + res.resumen.pendientes);
            $('#cntPagados').text('$ ' + res.resumen.pagados);

            if ($.fn.DataTable.isDataTable('#tablaVencimientos')) {
                $('#tablaVencimientos').DataTable().destroy();
            }

            let tbody = $('#tablaVencimientos tbody').empty();

            res.data.forEach(item => {
                let badgeEstado = '';
                if (item.estado === 'PAGADO') {
                    badgeEstado = '<span class="badge bg-success">PAGADO</span>';
                } else if (item.es_vencido) {
                    badgeEstado = '<span class="badge bg-danger">VENCIDO</span>';
                } else if (item.es_proximo) {
                    badgeEstado = '<span class="badge bg-warning text-dark">PRÓXIMO</span>';
                } else {
                    badgeEstado = '<span class="badge bg-secondary">PENDIENTE</span>';
                }

                let btnAdjunto = item.archivo 
                    ? `<a href="../../uploads/vencimientos/${item.archivo}" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-file-earmark-arrow-down"></i></a>` 
                    : '-';

                let acciones = '';
                if (item.estado === 'PENDIENTE') {
                    acciones += `<button class="btn btn-sm btn-success me-1" onclick="abrirModalPagar(${item.id})" title="Marcar como Pagado"><i class="bi bi-check-lg"></i></button>`;
                }
                acciones += `<button class="btn btn-sm btn-warning me-1" onclick="editarVencimiento(${item.id_json})" title="Editar"><i class="bi bi-pencil"></i></button>`;
                acciones += `<button class="btn btn-sm btn-danger" onclick="eliminarVencimiento(${item.id})" title="Eliminar"><i class="bi bi-trash"></i></button>`;

                let tr = `<tr>
                    <td>${badgeEstado}</td>
                    <td><strong>${item.titulo}</strong><br><small class="text-muted">${item.descripcion || ''}</small></td>
                    <td>${item.fecha_vencimiento_fmt}</td>
                    <td>${item.categoria}</td>
                    <td><small>Prov: ${item.proveedor || '-'}<br>Obra: ${item.obra || '-'}</small></td>
                    <td class="fw-bold">$ ${item.monto_fmt}</td>
                    <td class="text-center">${btnAdjunto}</td>
                    <td>${acciones}</td>
                </tr>`;
                tbody.append(tr);
            });

            dataTableVencimientos = $('#tablaVencimientos').DataTable({
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                dom: 'Bfrtip',
                buttons: ['copy', 'excel', 'pdf', 'print']
            });
        }
    });
}

function nuevoVencimiento() {
    $('#formVencimiento')[0].reset();
    $('#vencimiento_id').val('');
    $('#archivoActual').html('');
    $('#modalTitle').text('Nuevo Vencimiento');
    $('#modalVencimiento').modal('show');
}

function editarVencimiento(item) {
    $('#vencimiento_id').val(item.id);
    $('#titulo').val(item.titulo);
    $('#monto').val(item.monto);
    $('#fecha_vencimiento').val(item.fecha_vencimiento);
    $('#categoria').val(item.categoria);
    $('#dias_aviso').val(item.dias_aviso);
    $('#proveedor_id').val(item.proveedor_id);
    $('#obra_id').val(item.obra_id);
    $('#descripcion').val(item.descripcion);
    
    if (item.archivo) {
        $('#archivoActual').html(`<small class="text-info">Archivo actual: ${item.archivo}</small>`);
    } else {
        $('#archivoActual').html('');
    }

    $('#modalTitle').text('Editar Vencimiento');
    $('#modalVencimiento').modal('show');
}

function abrirModalPagar(id) {
    $('#pagar_id').val(id);
    let now = new Date().toISOString().slice(0, 16);
    $('#fecha_pago').val(now);
    $('#modalPagar').modal('show');
}

function eliminarVencimiento(id) {
    Swal.fire({
        title: '¿Eliminar vencimiento?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('eliminar_vencimiento.php', { id: id }, function(res) {
                if (res.status) {
                    Swal.fire('Eliminado', res.message, 'success');
                    cargarTabla();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json');
        }
    });
}
</script>