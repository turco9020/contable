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
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                            <label class="form-label fw-semibold">Cliente Asignado</label>
                            <select name="cliente_id" id="cliente_id" class="form-select" required></select>
                        </div>

                        <!-- Fila 2 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Dirección de la Obra</label>
                            <input name="direccion" id="direccion" class="form-control" placeholder="Ej: Av. Santa Fe 1234">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">N° de OC</label>
                            <input name="nro_oc" id="nro_oc" class="form-control" placeholder="Ej: OC-2026-88">
                        </div>

                        <!-- Fila 3 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Fecha de Fin (Estimada/Real)</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control">
                        </div>

                        <!-- Fila 4 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tipo de Obra</label>
                            <select name="tipo_obra" id="tipo_obra" class="form-select" required>
                                <option value="PRIVADA">🔒 PRIVADA</option>
                                <option value="PUBLICA">🏛️ PUBLICA</option>
                                <option value="PARTICULAR">👤 PARTICULAR</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Estado Operativo</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="ACTIVA">🟢 ACTIVA</option>
                                <option value="FINALIZADA">🔴 FINALIZADA</option>
                            </select>
                        </div>

                        <!-- Fila 5: Detalles -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Detalles / Memoria Descriptiva</label>
                            <textarea name="detalle" id="detalle" class="form-control" rows="2" placeholder="Notas sobre el alcance del proyecto..."></textarea>
                        </div>

                        <hr class="my-3">

                        <!-- SECCIÓN ARCHIVOS -->
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
                        
                        <!-- Contenedor del Listado del Repositorio -->
                        <div class="col-md-12 d-none" id="contenedorListaArchivos">
                            <label class="form-label fw-semibold text-secondary"><i class="bi bi-folder2-open me-1"></i> Repositorio de Documentos</label>
                            <ul class="list-group" id="listaArchivosObra">
                                <!-- Dinámico -->
                            </ul>
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
    $('#listaArchivosObra').html('');
    $('#contenedorListaArchivos').addClass('d-none');
    $('#formObra').find('input, select, textarea').prop('disabled', false);
    $('#btnGuardarObra').show();
    $('#wrapperPresupuesto, #wrapperRepositorio').show();

    if(modo === 'NUEVO') {
        $('#modalObraLabel').html('<i class="bi bi-plus-circle me-2"></i> Registrar Nueva Obra');
        cargarClientes();
        modalObraBS.show();
    }
}

document.addEventListener("DOMContentLoaded", function(){
    modalObraBS = new bootstrap.Modal(document.getElementById('modalObra'));

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
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            {
                extend: 'print',
                text: ' Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            { 
                extend: 'colvis', 
                text: 'Columnas', 
                className: 'btn btn-sm btn-secondary' 
            }
        ],
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

    // ================= VER OBRA =================
    window.verObra = function(id) {
        window.editar(id);
        setTimeout(() => {
            $('#modalObraLabel').html('<i class="bi bi-eye me-2"></i> Detalles Completos de la Obra');
            $('#formObra').find('input:not([type="file"]), select, textarea').prop('disabled', true);
            $('#btnGuardarObra').hide();
            $('#wrapperPresupuesto, #wrapperRepositorio').hide();
            
            let d = null;
            tabla.rows().data().each(function(row) {
                if(row.id == id) {
                    d = row;
                }
            });

            if(d && d.presupuesto_archivo) {
                $('#contenedorListaArchivos').removeClass('d-none');
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

    // ================= EDITAR =================
    window.editar = function(id){
        let d = tabla.rows().data().toArray().find(x => x.id == id);
        if(!d) {
            console.error("No se encontraron los datos para la obra con ID: " + id);
            return;
        }

        $('#formObra')[0].reset();
        $('#verPresupuestoActual').html('');
        $('#listaArchivosObra').html('');
        $('#formObra').find('input, select, textarea').prop('disabled', false);
        $('#btnGuardarObra').show();
        $('#wrapperPresupuesto, #wrapperRepositorio').show();
        $('#modalObraLabel').html('<i class="bi bi-pencil me-2"></i> Editar Datos de Obra');

        cargarClientes(function() {
            $('#id').val(d.id);
            $('#nombre').val(d.nombre);
            $('#cliente_id').val(d.cliente_id);
            $('#direccion').val(d.direccion);
            $('#nro_oc').val(d.nro_oc);
            $('#fecha_inicio').val(d.fecha_inicio);
            $('#fecha_fin').val(d.fecha_fin);
            $('#tipo_obra').val(d.tipo_obra);
            $('#detalle').val(d.detalle);
            $('#estado').val(d.estado);

            if(d.presupuesto_archivo) {
                $('#verPresupuestoActual').html(`
                    <a href="/contable/uploads/obras/${d.presupuesto_archivo}" target="_blank" class="btn btn-xs btn-link text-primary p-0">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Ver presupuesto actual
                    </a>
                `);
            }
            cargarRepositorio(d.id);
        });
        modalObraBS.show();
    }

    function cargarRepositorio(obraId) {
        $.get('/contable/ajax/obras.php?accion=listar_archivos', { obra_id: obraId }, function(r) {
            if(r.success && r.archivos.length > 0) {
                $('#contenedorListaArchivos').removeClass('d-none');
                let lista = $('#listaArchivosObra');
                lista.empty();
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
            }
        }, 'json');
    }

    window.eliminarArchivoRepo = function(archivoId, obraId) {
        if(confirm('¿Desea remover este archivo del repositorio?')) {
            $.post('/contable/ajax/obras.php?accion=eliminar_archivo', { id: archivoId }, function() {
                cargarRepositorio(obraId);
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
});
</script>

<?php include '../../../includes/footer.php'; ?>