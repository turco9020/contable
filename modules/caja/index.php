<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/sidebar.php';
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-wallet2 text-secondary me-2"></i>Caja
        </h5>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal()">
            <i class="bi bi-plus-circle me-2"></i>Nuevo Movimiento
        </button>
    </div>

    <div class="row mb-4" id="cardsSaldos">
        <!-- Cargado dinámicamente -->
    </div>

    <div class="card p-3 shadow-sm">
        <table id="tablaMovimientos" class="table table-bordered table-striped w-100">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Caja</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th>Comprobante</th>
                    <th>Importe</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

</div>

<!-- Modal Movimiento -->
<div class="modal fade" id="modalMovimiento">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Movimiento de Caja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="formMovimiento" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id">

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" id="fecha" name="fecha" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Caja</label>
                            <select id="caja_id" name="caja_id" class="form-control" required></select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo</label>
                            <select id="tipo" name="tipo" class="form-control">
                                <option value="INGRESO">INGRESO</option>
                                <option value="EGRESO">EGRESO</option>
                                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Concepto</label>
                        <input type="text" id="concepto" name="concepto" class="form-control" required>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Comprobante</label>
                            <input type="text" id="comprobante" name="comprobante" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Importe</label>
                            <input type="number" step="0.01" id="importe" name="importe" class="form-control" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mt-3" id="contenedor_archivo">
                        <label class="form-label">Archivo</label>
                        <input type="file" id="archivo" name="archivo" class="form-control">
                        <div id="archivo_actual" class="mt-2"></div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" id="btnGuardar" class="btn btn-dark w-100">Guardar</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/modal_gasto.php'; ?>
<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/footer.php'; ?>
<script src="/contable/assets/js/gastos_modal.js"></script>

<script>
// INYECCIÓN DE DATOS DE SESIÓN PARA LA VALIDACIÓN VISUAL
const SESION_USUARIO_ID = <?php echo json_encode($_SESSION['id'] ?? null); ?>;
const SESION_ROL = <?php echo json_encode($_SESSION['rol'] ?? ''); ?>;

let tabla;
let modalMovimiento;

function cargarCajasIndex(){ 
    $.get('/contable/ajax/cajas.php?accion=listar', function(r){
        let select = $('#formMovimiento #caja_id'); 
        select.empty();
        r.data.forEach(c => {
            select.append(`<option value="${c.id}">${c.nombre}</option>`);
        });
    }, 'json');
}

function cargarSaldos(){
    $.get('/contable/ajax/movimientos_caja.php?accion=saldos', function(data){
        let html = '';
        data.forEach(caja => {
            let saldo = Number(caja.saldo).toLocaleString('es-AR', {minimumFractionDigits:2, maximumFractionDigits:2});
            html += `
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">${caja.nombre}</h6>
                            <h4 class="fw-bold mb-0">$ ${saldo}</h4>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#cardsSaldos').html(html);
    }, 'json');
}

document.addEventListener("DOMContentLoaded", function(){
    modalMovimiento = new bootstrap.Modal(document.getElementById('modalMovimiento'));

    cargarCajasIndex(); 
    cargarSaldos();

    tabla = $('#tablaMovimientos').DataTable({
        responsive: true,
        scrollX: false,
        autoWidth: false,
        ajax: '/contable/ajax/movimientos_caja.php?accion=listar',
        order: [[0, 'desc']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: ' Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            },
            {
                extend: 'print',
                text: ' Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            },
            { 
                extend: 'colvis', 
                text: 'Columnas', 
                className: 'btn btn-sm btn-secondary' 
            }
        ],
        columns: [
            {
                data: 'fecha',
                render: function(data){
                    if(!data) return '-';
                    let partes = data.split('-');
                    if(partes.length !== 3) return data;
                    return `${partes[2]}-${partes[1]}-${partes[0]}`;
                }
            },
            { data: 'caja' },
            {
                data: 'tipo',
                render: function(d){
                    if(d == 'INGRESO') return `<span class="badge bg-success">INGRESO</span>`;
                    if(d == 'EGRESO') return `<span class="badge bg-danger">EGRESO</span>`;
                    return `<span class="badge bg-primary">TRANSFERENCIA</span>`;
                }
            },
            {
                 data: 'concepto',
                 render: function(data, type, row){
                    if(row.origen == 'GASTO'){
                        return `<a href="#" class="text-decoration-none text-primary" onclick="verGasto(${row.referencia_id});return false;">${data}</a>`;
                    }
                    return data;
                 }
            },
            { data: 'comprobante' },
            {
                data: 'importe',
                render: function(d){
                    return '$ ' + Number(d).toLocaleString('es-AR', {minimumFractionDigits:2});
                }
            },
            {
                data: 'usuario_nombre',
                visible: true,
                render: function(d) {
                    return d ? `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i> ${d}</span>` : '<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i>Sistema</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: function(d){
                    let btnArchivo = '';
                    if(d.origen === 'GASTO'){
                        if(d.gasto_archivo) {
                            btnArchivo = `<a href="/contable/uploads/gastos/${d.gasto_archivo}" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver Adjunto Gasto"><i class="bi bi-file-earmark-pdf"></i></a>`;
                        }
                    } else {
                        if(d.archivo) {
                            btnArchivo = `<a href="/contable/uploads/caja/${d.archivo}" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver Adjunto Caja"><i class="bi bi-file-earmark-pdf"></i></a>`;
                        }
                    }

                    // CONTROL DE PERMISOS MODIFICADO:
                    // Si proviene de GASTO, se bloquea SIEMPRE para todos los usuarios.
                    // Si es MANUAL, se edita si es admin/contador o si el usuario actual es el creador.
                    const rol = SESION_ROL.toLowerCase();
                    const esGasto = (d.origen === 'GASTO');
                    const esEditable = !esGasto && (
                        (rol === 'admin' || rol === 'contador') || 
                        (d.origen === 'MANUAL' && d.usuario_id == SESION_USUARIO_ID)
                    );

                    if(esEditable){
                        return `
                            <div class="d-inline-flex gap-1 justify-content-end">
                                ${btnArchivo}
                                <button class="btn btn-sm btn-outline-secondary" title="Ver Movimiento" onclick='verManual(${JSON.stringify(d)})'>
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" title="Editar Movimiento" onclick='editar(${JSON.stringify(d)})'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar Movimiento" onclick="eliminar(${d.id})">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        // Si viene de GASTO o está bloqueado, únicamente se renderiza el botón del adjunto (si existe) y la etiqueta de Bloqueado
                        return `
                            <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                                ${btnArchivo}
                                <span class="badge bg-light text-muted border py-1.5 px-2" title="Movimiento originado en Gastos o generado por otro usuario" style="font-size: 0.75rem;">
                                    <i class="bi bi-lock-fill text-secondary me-1"></i> Bloqueado
                                </span>
                            </div>
                        `;
                    }
                }
            }
        ]
    });
    $(window).on('resize', function () {
        tabla.columns.adjust().responsive.recalc();
    });
});

window.abrirModal = function(){
    $('#formMovimiento input, textarea, select').prop('disabled', false);
    $('#btnGuardar').show();
    $('#contenedor_archivo').show(); 
    $('#formMovimiento')[0].reset();
    $('#id').val('');
    $('#archivo').val('');
    $('#archivo_actual').html('');
    modalMovimiento.show();
}

$('#formMovimiento').submit(function(e){
    e.preventDefault();
    let formData = new FormData(this);

    $.ajax({
        url: '/contable/ajax/movimientos_caja.php?accion=guardar',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(resp){
            tabla.ajax.reload();
            cargarSaldos();
            modalMovimiento.hide();
        }
    });
});

window.eliminar = function(id){
    Swal.fire({
        title: '¿Estás completamente seguro?',
        text: 'Esta acción eliminará el movimiento de caja de forma permanente. Para proceder, escribe "ELIMINAR" abajo:',
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Escribe ELIMINAR aquí...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#212529',
        confirmButtonText: 'Sí, eliminar movimiento',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return 'Debes ingresar la palabra de confirmación.';
            }
            if (value !== 'ELIMINAR') {
                return 'La palabra no coincide. Debe ser exactamente ELIMINAR.';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/contable/ajax/movimientos_caja.php?accion=eliminar', {id}, function(resp){
                tabla.ajax.reload();
                cargarSaldos();
                Swal.fire({
                    title: 'Eliminado',
                    text: 'El movimiento ha sido removido con éxito.',
                    icon: 'success',
                    confirmButtonColor: '#212529'
                });
            });
        }
    });
}

function verGasto(id){
    $.get('/contable/ajax/gastos.php', { accion:'obtener', id:id }, function(g){
        window.mostrarModalGasto(g);
    }, 'json');
}

window.editar = function(data){
    $('#formMovimiento input, textarea, select').prop('disabled', false);
    $('#btnGuardar').show();
    $('#contenedor_archivo').show(); 
    $('#formMovimiento')[0].reset();

    $('#id').val(data.id);
    $('#fecha').val(data.fecha);
    $('#formMovimiento #caja_id').val(data.caja_id); 
    $('#tipo').val(data.tipo);
    $('#concepto').val(data.concepto);
    $('#comprobante').val(data.comprobante);
    $('#importe').val(data.importe);
    $('#observaciones').val(data.observaciones);

    if(data.archivo){
        $('#archivo_actual').html(`<a href="/contable/uploads/caja/${data.archivo}" target="_blank" class="btn btn-sm btn-dark">Ver archivo actual</a>`);
    } else {
        $('#archivo_actual').html('');
    }

    $('#archivo').val('');
    modalMovimiento.show();
}

window.verManual = function(data){
    window.editar(data); 
    $('#formMovimiento input, textarea, select').prop('disabled', true);
    $('#btnGuardar').hide();
    
    if(!data.archivo){
        $('#contenedor_archivo').hide();
    }
}
</script>