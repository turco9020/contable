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
<div class="modal fade" id="modalMovimiento" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModalMovimiento">
                    <i class="bi bi-wallet2 me-2"></i>Movimiento de Caja
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formMovimiento" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id">

                    <!-- SECCIÓN 1: DATOS DEL MOVIMIENTO -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">1. Datos del Movimiento</h6>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Fecha</label>
                            <input type="date" id="fecha" name="fecha" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tipo</label>
                            <select id="tipo" name="tipo" class="form-select">
                                <option value="INGRESO">🟢 INGRESO</option>
                                <option value="EGRESO">🔴 EGRESO</option>
                                <option value="TRANSFERENCIA">↔️ TRANSFERENCIA</option>
                            </select>
                        </div>

                        <!-- Caja Normal (Ingreso / Egreso) -->
                        <div class="col-md-6" id="div_caja_normal">
                            <label class="form-label small fw-bold">Caja</label>
                            <select id="caja_id" name="caja_id" class="form-select" required>
                                <option value="" selected disabled>Seleccionar</option>
                            </select>
                        </div>

                        <!-- Cajas para Transferencias -->
                        <div class="col-md-3 d-none" id="div_caja_origen">
                            <label class="form-label small fw-bold ">Caja Origen</label>
                            <select id="caja_origen_id" name="caja_origen_id" class="form-select border border-danger">
                                <option value="" selected disabled>Seleccionar</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-none" id="div_caja_destino">
                            <label class="form-label small fw-bold">Caja Destino</label>
                            <select id="caja_destino_id" name="caja_destino_id" class="form-select border border-success">
                                <option value="" selected disabled>Seleccionar</option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Concepto</label>
                            <input type="text" id="concepto" name="concepto" class="form-control" placeholder="Ej: Depósito inicial, Pago de flete, etc." required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Comprobante</label>
                            <input type="text" id="comprobante" name="comprobante" class="form-control" placeholder="Ej: F-0001-00001234">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" class="form-control" rows="2" placeholder="Detalles adicionales o notas del movimiento..."></textarea>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: IMPORTE Y ADJUNTO -->
                    <div class="row g-3 p-3 bg-light rounded-3 border align-items-end mb-4">
                        <div class="col-12 mb-1">
                            <h6 class="text-uppercase text-dark fw-bold small"><i class="bi bi-cash-coin me-1"></i> Importe y Comprobante</h6>
                        </div>

                        <div class="col-md-6" id="contenedor_archivo">
                            <label class="form-label small fw-bold">Archivo Adjunto</label>
                            <input type="file" id="archivo" name="archivo" class="form-control">
                            <div id="archivo_actual" class="mt-2"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">Importe</label>
                            <input type="text" inputmode="decimal" id="importe" name="importe" class="form-control text-end fw-bold text-dark fs-5 border-dark bg-white" placeholder="0,00" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnGuardar" class="btn btn-dark px-5">Guardar Registro</button>
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
        let selectOrigen = $('#caja_origen_id');
        let selectDestino = $('#caja_destino_id');

        select.empty().append('<option value="" selected disabled>-- Seleccionar Caja --</option>');
        selectOrigen.empty().append('<option value="" selected disabled>-- Seleccionar Caja Origen --</option>');
        selectDestino.empty().append('<option value="" selected disabled>-- Seleccionar Caja Destino --</option>');

        r.data.forEach(c => {
            select.append(`<option value="${c.id}">${c.nombre}</option>`);
            selectOrigen.append(`<option value="${c.id}">${c.nombre}</option>`);
            selectDestino.append(`<option value="${c.id}">${c.nombre}</option>`);
        });
    }, 'json');
}

let cajaFiltroSeleccionada = null;

function cargarSaldos(){
    $.get('/contable/ajax/movimientos_caja.php?accion=saldos', function(data){
        let html = '';
        
        // Card de "Todas las cajas" para resetear el filtro
        html += `
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 card-caja h-100" onclick="filtrarPorCaja(null, 'Todas')" style="cursor: pointer;">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small text-uppercase">Todas las Cajas</h6>
                            <span class="badge bg-secondary">Ver Todo</span>
                        </div>
                        <i class="bi bi-wallet2 fs-3 text-secondary"></i>
                    </div>
                </div>
            </div>
        `;

        data.forEach(caja => {
            let valorSaldo = Number(caja.saldo);
            let saldoFmt = valorSaldo.toLocaleString('es-AR', {minimumFractionDigits:2, maximumFractionDigits:2});
            let colorClase = valorSaldo < 0 ? 'text-danger' : 'text-dark';

            html += `
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 card-caja h-100" onclick="filtrarPorCaja(${caja.id}, '${caja.nombre}')" style="cursor: pointer;">
                        <div class="card-body">
                            <h6 class="text-muted mb-1 small text-uppercase">${caja.nombre}</h6>
                            <h4 class=" mb-0 ${colorClase}">$ ${saldoFmt}</h4>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#cardsSaldos').html(html);
    }, 'json');
}

// Función que aplica el filtro dinámico a la columna "Caja" de DataTables
function filtrarPorCaja(cajaId, nombreCaja){
    cajaFiltroSeleccionada = cajaId;

    if (cajaId === null) {
        // Limpia el filtro y muestra todo
        tabla.column(1).search('').draw();
    } else {
        // Filtra por coincidencia exacta del nombre de la caja en la columna 1
        tabla.column(1).search('^' + $.fn.dataTable.util.escapeRegex(nombreCaja) + '$', true, false).draw();
    }
}

// Alternar interfaz de acuerdo al Tipo seleccionado
$('#tipo').on('change', function(){
    let tipo = $(this).val();
    if(tipo === 'TRANSFERENCIA'){
        $('#div_caja_normal').addClass('d-none');
        $('#caja_id').prop('required', false);

        $('#div_caja_origen, #div_caja_destino').removeClass('d-none');
        $('#caja_origen_id, #caja_destino_id').prop('required', true);
    } else {
        $('#div_caja_normal').removeClass('d-none');
        $('#caja_id').prop('required', true);

        $('#div_caja_origen, #div_caja_destino').addClass('d-none');
        $('#caja_origen_id, #caja_destino_id').prop('required', false);
    }
});

document.addEventListener("DOMContentLoaded", function(){
    modalMovimiento = new bootstrap.Modal(document.getElementById('modalMovimiento'));

    cargarCajasIndex(); 
    cargarSaldos();

    // FORMATEADOR EN TIEMPO REAL PARA EL IMPORTE
    $('#importe').on('input', function() {
        let valor = $(this).val().replace(/[^\d,]/g, ''); // Deja solo números y coma
        let partes = valor.split(',');

        // Separa miles con punto en la parte entera
        partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, "."); 

        // Limita los decimales a 2 dígitos
        if (partes.length > 2) {
            partes = [partes[0], partes[1]];
        }
        if (partes[1]) {
            partes[1] = partes[1].substring(0, 2);
        }

        $(this).val(partes.join(','));
    });

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
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'print',
                text: ' Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, ]
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
                render: function(data, type, row){
                    if (!data) return '-';

                    // Si DataTables nos pide 'sort' o 'type' (para ordenar/filtrar en segundo plano), 
                    // devolvemos el valor original YYYY-MM-DD para que ordene cronológicamente de verdad.
                    if (type === 'sort' || type === 'type') {
                        return data; 
                    }

                    // Para mostrar en pantalla ('display'), lo formateamos a DD-MM-YYYY
                    let partes = data.split('-');
                    if (partes.length !== 3) return data;
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

                    // CONTROL DE PERMISOS MODIFICADO
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
    $('#formMovimiento')[0].reset();
    
    // Forzamos el reset de los selectores a la opción por defecto
    $('#tipo').val('INGRESO').trigger('change');
    $('#caja_id').val('');
    $('#caja_origen_id').val('');
    $('#caja_destino_id').val('');

    $('#btnGuardar').show();
    $('#contenedor_archivo').show(); 
    $('#id').val('');
    $('#archivo').val('');
    $('#archivo_actual').html('');
    modalMovimiento.show();
}

$('#formMovimiento').submit(function(e){
    e.preventDefault();
    let formData = new FormData(this);

    // Limpiamos formato visual "1.500,50" -> "1500.50" para guardar en la BD
    let importeRaw = $('#importe').val().replace(/\./g, '').replace(',', '.');
    formData.set('importe', importeRaw);

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

    // Formateamos el importe numérico recibido de la BD a formato visual es-AR (ej: 1500.50 -> 1.500,50)
    let importeFmt = Number(data.importe).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    $('#importe').val(importeFmt);

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