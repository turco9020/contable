<?php
// Subimos tres niveles para salir de: configuracion -> config -> modules -> contable
include '../../../includes/header.php';
include '../../../includes/sidebar.php';

if (!esAdmin()) {
    echo '<div class="content"><div class="alert alert-danger">No tenés permisos para acceder a esta sección.</div></div>';
    include '../../../includes/footer.php';
    exit;
}
?>

<div class="topbar d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold"><i class="bi bi-gear text-secondary me-2"></i>Configuración</h5>
</div>

<div class="content">

    <!-- PESTAÑAS -->
    <ul class="nav nav-tabs mb-3" id="tabConfiguracion" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold text-dark" id="tab-iibb" data-bs-toggle="tab" data-bs-target="#panel-iibb" type="button" role="tab">
                <i class="bi bi-percent me-1 text-secondary"></i> Porcentajes IIBB
            </button>
        </li>
    </ul>

    <div class="tab-content" id="tabConfiguracionContent">

        <!-- PESTAÑA 1: PORCENTAJES IIBB -->
        <div class="tab-pane fade show active" id="panel-iibb" role="tabpanel">
            <div class="card p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-secondary mb-0">Actividades y alícuotas de Ingresos Brutos</h6>
                    <button class="btn btn-dark btn-sm" onclick="abrirModalIIBB()">+ Nueva Actividad</button>
                </div>
                <table id="tablaIIBB" class="table table-bordered table-striped w-100">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 6%;">ID</th>
                            <th style="width: 12%;">Código</th>
                            <th>Actividad</th>
                            <th style="width: 22%;">Alícuota / tratamiento</th>
                            <th style="width: 8%;" class="text-center">Estado</th>
                            <th style="width: 16%;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- MODAL IIBB -->
<div class="modal fade" id="modalIIBB" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModalIIBB">Actividad IIBB</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formIIBB">
                    <input type="hidden" name="id" id="iibb_id">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Código</label>
                            <input name="codigo" id="iibb_codigo" class="form-control" maxlength="20" placeholder="Ej: 410011" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Actividad</label>
                            <input name="actividad" id="iibb_actividad" class="form-control" maxlength="255" placeholder="Ej: Construcción de edificios residenciales" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Alícuota (%)</label>
                            <input type="number" step="0.001" min="0" max="100" name="alicuota" id="iibb_alicuota" class="form-control" placeholder="Ej: 3.5">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Tratamiento <span class="text-muted fw-normal">(opcional)</span></label>
                            <input name="tratamiento" id="iibb_tratamiento" class="form-control" maxlength="100" placeholder="Ej: Exento, No gravado, Alícuota reducida">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="activo" id="iibb_activo" checked>
                                <label class="form-check-label small" for="iibb_activo">Activa</label>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-dark w-100 mt-3">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const URL_AJAX_CONFIG = '/contable/ajax/configuracion.php';

// =====================================================================
// PESTAÑA: PORCENTAJES IIBB
// =====================================================================
let modalIIBB, tablaIIBB;

function escapeHtml(s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

window.abrirModalIIBB = function(){
    document.getElementById('formIIBB').reset();
    document.getElementById('iibb_id').value = '';
    document.getElementById('iibb_activo').checked = true;
    document.getElementById('tituloModalIIBB').innerText = 'Nueva Actividad';
    modalIIBB.show();
}

window.addEventListener('load', function(){
    modalIIBB = new bootstrap.Modal(document.getElementById('modalIIBB'));

    tablaIIBB = $('#tablaIIBB').DataTable({
        ajax: URL_AJAX_CONFIG + '?accion=iibb_listar',
        autoWidth: false,
        order: [[1, 'asc']],
        columns: [
            { data: 'id' },
            { data: 'codigo', render: function(d) { return escapeHtml(d); } },
            { data: 'actividad', render: function(d) { return escapeHtml(d); } },
            {
                data: null,
                render: function(d, type) {
                    const tieneAlicuota = d.alicuota !== null && d.alicuota !== '';
                    if (type === 'sort' || type === 'type') return tieneAlicuota ? parseFloat(d.alicuota) : -1;

                    let txt = tieneAlicuota
                        ? '<span class="fw-bold">' + new Intl.NumberFormat('es-AR', { maximumFractionDigits: 3 }).format(d.alicuota) + ' %</span>'
                        : '';
                    if (d.tratamiento) txt += (txt ? ' <span class="text-muted">· </span>' : '') + '<span class="text-muted">' + escapeHtml(d.tratamiento) + '</span>';
                    return txt || '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'activo',
                className: 'text-center',
                render: function(d) {
                    return d == 1
                        ? '<span class="badge bg-success">Activa</span>'
                        : '<span class="badge bg-secondary">Inactiva</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(d) {
                    return `
                        <button class="btn btn-sm btn-primary me-1" onclick='editarIIBB(${JSON.stringify(d).replace(/'/g, "&#39;")})'>Editar</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarIIBB(${d.id})">Eliminar</button>
                    `;
                }
            }
        ]
    });

    document.getElementById('formIIBB').addEventListener('submit', function(e){
        e.preventDefault();

        $.post(URL_AJAX_CONFIG + '?accion=iibb_guardar', $(this).serialize(), function(r){
            if (r.status === 'OK') {
                tablaIIBB.ajax.reload(null, false);
                modalIIBB.hide();
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Actividad guardada', timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Error', r.msg || 'No se pudo guardar la actividad.', 'error');
            }
        }, 'json').fail(function(){
            Swal.fire('Error', 'Ocurrió un problema de comunicación al guardar.', 'error');
        });
    });

    window.editarIIBB = function(d){
        document.getElementById('iibb_id').value = d.id;
        document.getElementById('iibb_codigo').value = d.codigo;
        document.getElementById('iibb_actividad').value = d.actividad;
        document.getElementById('iibb_alicuota').value = d.alicuota !== null ? parseFloat(d.alicuota) : '';
        document.getElementById('iibb_tratamiento').value = d.tratamiento || '';
        document.getElementById('iibb_activo').checked = d.activo == 1;
        document.getElementById('tituloModalIIBB').innerText = 'Editar Actividad';
        modalIIBB.show();
    }

    window.eliminarIIBB = function(id){
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar esta actividad?',
            text: 'Si ya se usa en otras páginas, conviene marcarla como inactiva en lugar de eliminarla.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            reverseButtons: true,
            focusCancel: true
        }).then(function(res){
            if (!res.isConfirmed) return;

            $.post(URL_AJAX_CONFIG + '?accion=iibb_eliminar', {id: id}, function(r){
                if (r.status === 'OK') {
                    tablaIIBB.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', r.msg || 'No se pudo eliminar la actividad.', 'error');
                }
            }, 'json');
        });
    }
});
</script>

<?php
include '../../../includes/footer.php';
?>
