<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/sidebar.php';
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>🏦 Gestión de Cheques</h3>
        <button class="btn btn-dark" onclick="abrirModal()">
            + Nuevo Cheque
        </button>
    </div>

    <!-- Pestañas de Navegación de Estados -->
    <ul class="nav nav-tabs mb-3" id="tabCheques" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold text-dark" id="recibidos-tab" data-bs-toggle="tab" data-bs-target="#recibidos" type="button" role="tab" onclick="filtrarPestaña('RECIBIDOS')">
                📥 Recibidos <span class="badge bg-secondary ms-1" id="cant-recibidos">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-dark" id="emitidos-tab" data-bs-toggle="tab" data-bs-target="#emitidos" type="button" role="tab" onclick="filtrarPestaña('EMITIDOS')">
                📤 Emitidos <span class="badge bg-secondary ms-1" id="cant-emitidos">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-dark" id="cedidos-tab" data-bs-toggle="tab" data-bs-target="#cedidos" type="button" role="tab" onclick="filtrarPestaña('ENDOSADO')">
                🔄 Cedidos/Endosados <span class="badge bg-secondary ms-1" id="cant-cedidos">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-dark" id="finalizados-tab" data-bs-toggle="tab" data-bs-target="#finalizados" type="button" role="tab" onclick="filtrarPestaña('FINALIZADOS')">
                ✔︎ Cobrados / Pagados <span class="badge bg-secondary ms-1" id="cant-finalizados">0</span>
            </button>
        </li>
    </ul>

    <div class="card p-3 shadow-sm">
        <div class="table-responsive">
            <table id="tablaCheques" class="table table-bordered table-striped w-100">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Emisión</th>
                        <th>Pago/Vto.</th>
                        <th>N° Cheque</th>
                        <th>Importe</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Beneficiario</th>
                        <th>Observaciones</th>
                        <th>Dias</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <!-- NUEVO: Pie de tabla para alojar los totales -->
                <tfoot class="table-light fw-bold">
                <tr>
                    <th colspan="4" class="text-end">Total General:</th>
                    <th id="totalImporte" class="text-end text-dark">$ 0,00</th>
                    <th colspan="6"></th> <!-- Columnas restantes vacías -->
                </tr>
            </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DE CARGA / EDICIÓN -->
<div class="modal fade" id="modalCheque" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Registrar Cheque</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCheque" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de Cheque</label>
                            <select name="tipo" id="tipo" class="form-select" required onchange="adaptarFormularioPorEstado()">
                                <option value="TERCERO">Físico - Tercero</option>
                                <option value="PROPIO">Físico - Propio</option>
                                <option value="ECHEQ_TERCERO">Echeq - Tercero</option>
                                <option value="ECHEQ_PROPIO">Echeq - Propio</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estado Actual</label>
                            <select name="estado" id="estado" class="form-select" required onchange="adaptarFormularioPorEstado()">
                                <option value="RECIBIDO">📥 RECIBIDO (En cartera)</option>
                                <option value="EMITIDO">📤 EMITIDO (Pendiente de cobro)</option>
                                <option value="ENDOSADO">🔄 ENDOSADO / CEDIDO</option>
                                <option value="COBRADO">🟢 COBRADO</option>
                                <option value="PAGADO">🔴 PAGADO</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nro. de Cheque</label>
                            <input type="text" name="nro_cheque" id="nro_cheque" class="form-control" placeholder="Ej: 0048259" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Emisión</label>
                            <input type="date" name="fecha_emision" id="fecha_emision" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Pago (Vto.)</label>
                            <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Importe ($)</label>
                            <input type="number" step="0.01" name="importe" id="importe" class="form-control" value="0.00" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold" id="labelBeneficiario">Beneficiario</label>
                            <select name="beneficiario" id="beneficiario" class="form-select" required></select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Notas internas..."></textarea>
                        </div>
                        
                        <!-- NUEVO: Campo para adjuntar Foto/Imagen del Cheque -->
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold">Foto / Comprobante del Cheque</label>
                            <input type="file" id="archivo" name="archivo" class="form-control" accept="image/*,application/pdf">
                            <div id="archivo_actual" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark" id="btnGuardar">Guardar Cheque</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/footer.php'; ?>

<script>
let tabla;
let modalCheque;
let pestañaActual = 'RECIBIDOS';
let listaProveedores = [];

document.addEventListener("DOMContentLoaded", function() {
    modalCheque = new bootstrap.Modal(document.getElementById('modalCheque'));

    obtenerProveedores();

    tabla = $('#tablaCheques').DataTable({
        // 'dom' define la disposición de los elementos. Agrega los botones arriba a la izquierda
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: ' Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] // Exporta todo menos Días y Acciones
                }
            },
            {
                extend: 'print',
                text: ' Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            }
        ],
        ajax: {
            url: '/contable/ajax/cheques.php?accion=listar',
            type: 'GET',
            data: function(d) {
                d.pestana = pestañaActual;
            },
            dataSrc: function(json) {
                if (json.contadores) {
                    $('#cant-recibidos').text(json.contadores.recibidos || 0);
                    $('#cant-emitidos').text(json.contadores.emitidos || 0);
                    $('#cant-cedidos').text(json.contadores.cedidos || 0);
                    $('#cant-finalizados').text(json.contadores.finalizados || 0);
                }
                return json.data;
            }
        },
        order: [[0, 'desc']],
        autoWidth: false,
        responsive: true,
        columns: [
            { data: 'id', className: 'text-center' },
            { 
                data: 'fecha_emision',
                render: function(d){
                    if(!d) return '-';
                    let p = d.split('-');
                    return p.length === 3 ? `${p[2]}-${p[1]}-${p[0]}` : d;
                }
            },
            { 
                data: 'fecha_pago',
                render: function(d){
                    if(!d) return '-';
                    let p = d.split('-');
                    return p.length === 3 ? `${p[2]}-${p[1]}-${p[0]}` : d;
                }
            },
            { data: 'nro_cheque' },
            { 
                data: 'importe',
                className: 'text-end fw-semibold',
                render: function(d) {
                    return '$ ' + Number(d).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                }
            },
            { 
                data: 'tipo',
                render: function(d) {
                    switch(d) {
                        case 'TERCERO': return 'Físico - Tercero';
                        case 'PROPIO': return 'Físico - Propio';
                        case 'ECHEQ_TERCERO': return 'Echeq - Tercero';
                        case 'ECHEQ_PROPIO': return 'Echeq - Propio';
                        default: return d;
                    }
                }
            },
            {
                data: 'estado',
                render: function(d) {
                    let texto = d ? d.toUpperCase() : 'RECIBIDO';
                    let colorLed = 'bg-warning';
                    if(texto === 'COBRADO' || texto === 'PAGADO') colorLed = 'bg-success';
                    else if(texto === 'EMITIDO') colorLed = 'bg-info';
                    else if(texto === 'ENDOSADO' || texto === 'CEDIDO') colorLed = 'bg-primary';
                    
                    return `
                        <div class="d-inline-flex align-items-center text-secondary fw-semibold" style="font-size: 0.9rem;">
                            <span class="rounded-circle ${colorLed}" style="width: 8px; height: 8px; display: inline-block; margin-right: 8px;"></span>
                            ${texto}
                        </div>
                    `;
                }
            },
            { data: 'beneficiario' },
            { data: 'observaciones' },
            {
                data: 'fecha_pago',
                className: 'text-center fw-bold',
                render: function(d, type, row) {
                    if (!d) return '-';
                    if (row.estado === 'COBRADO' || row.estado === 'PAGADO') return '<span class="text-muted">-</span>';

                    let fechaPago = new Date(d + 'T00:00:00');
                    let hoy = new Date();
                    hoy.setHours(0,0,0,0);

                    let diferenciaTiempo = fechaPago.getTime() - hoy.getTime();
                    let diasDiferencia = Math.ceil(diferenciaTiempo / (1000 * 60 * 60 * 24));

                    if (diasDiferencia < 0) return `<span class="text-danger">- ${Math.abs(diasDiferencia)}</span>`;
                    if (diasDiferencia <= 7) return `<span class="badge bg-danger text-white px-2 py-1">${diasDiferencia}</span>`;
                    return `<span class="text-dark">${diasDiferencia}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(d) {
                    let btnArchivo = d.archivo ? `<a href="/contable/uploads/cheques/${d.archivo}" target="_blank" class="btn btn-sm btn-outline-secondary">📁</a>` : '';
                    return `
                        <div class="d-flex gap-1">
                            ${btnArchivo}
                            <button class="btn btn-sm btn-secondary" onclick='verCheque(${JSON.stringify(d)})'>Ver</button>
                            <button class="btn btn-sm btn-primary" onclick='editarCheque(${JSON.stringify(d)})'>Editar</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarCheque(${d.id})">Eliminar</button>
                        </div>
                    `;
                }
            }
        ],
        // NUEVO: Función Callback para sumar la columna Importe (Columna índice 4) en tiempo real
        footerCallback: function(row, data, start, end, display) {
            let api = this.api();

            // Helper para limpiar strings y convertirlos a flotantes puros
            let intVal = function(i) {
                return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
            };

            // Calcular el total sobre los registros que están filtrados y visibles en la pestaña activa
            let total = api
                .column(4, { page: 'current' }) // Índice 4 es la columna Importe
                .data()
                .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Actualizar el pie de la tabla con el formato de moneda Argentina
            $('#totalImporte').html('$ ' + total.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }
    });

    $(window).on('resize', function () {
        tabla.columns.adjust();
    });
});

function obtenerProveedores() {
    $.getJSON('/contable/ajax/get_proveedores.php', function(data) {
        listaProveedores = data;
        adaptarFormularioPorEstado();
    }).fail(function() {
        listaProveedores = [];
    });
}

function filtrarPestaña(pestaña) {
    pestañaActual = pestaña;
    tabla.ajax.reload();
}

function adaptarFormularioPorEstado() {
    let estado = $('#estado').val();
    let tipo = $('#tipo').val();
    let $selectBeneficiario = $('#beneficiario');
    
    $selectBeneficiario.empty();

    if (estado === 'RECIBIDO') {
        $('#labelBeneficiario').text('Beneficiario (Nosotros)');
        $selectBeneficiario.append('<option value="RECURSOS GLOBALES" selected>RECURSOS GLOBALES</option>');
        $selectBeneficiario.prop('disabled', true);
    } else {
        $('#labelBeneficiario').text('Beneficiario / Destinatario (Proveedor)');
        $selectBeneficiario.prop('disabled', false);
        $selectBeneficiario.append('<option value="">-- Seleccionar Proveedor --</option>');
        
        listaProveedores.forEach(function(prov) {
            $selectBeneficiario.append(`<option value="${prov.nombre}">${prov.nombre}</option>`);
        });

        if ($('#id').val() === '' && tipo.includes('PROPIO') && estado === 'RECIBIDO') {
            $('#estado').val('EMITIDO');
        }
    }
}

window.abrirModal = function() {
    $('#formCheque input, textarea, select').prop('disabled', false);
    $('#btnGuardar').show();
    $('#tituloModal').text('Registrar Nuevo Cheque');
    $('#formCheque')[0].reset();
    $('#id').val('');
    $('#archivo').show().val('');
    $('#archivo_actual').html('');
    
    $('#tipo').val('TERCERO');
    $('#estado').val('RECIBIDO');
    
    adaptarFormularioPorEstado();
    modalCheque.show();
}

window.verCheque = function(data) {
    window.editarCheque(data);
    $('#tituloModal').text('Detalle de Cheque (Solo Lectura)');
    $('#formCheque input, textarea, select').prop('disabled', true);
    $('#btnGuardar').hide();
}

window.editarCheque = function(data) {
    $('#formCheque input, textarea, select').prop('disabled', false);
    $('#btnGuardar').show();
    $('#tituloModal').text('Editar Cheque');
    $('#formCheque')[0].reset();
    
    $('#id').val(data.id);
    $('#tipo').val(data.tipo);
    $('#estado').val(data.estado);
    $('#nro_cheque').val(data.nro_cheque);
    $('#fecha_emision').val(data.fecha_emision);
    $('#fecha_pago').val(data.fecha_pago);
    $('#importe').val(data.importe);
    $('#observaciones').val(data.observaciones);
    
    if(data.archivo){
        $('#archivo_actual').html(`<a href="/contable/uploads/cheques/${data.archivo}" target="_blank" class="btn btn-sm btn-dark">Ver archivo actual</a>`);
    }else{
        $('#archivo_actual').html('');
    }
    $('#archivo').val('');
    
    adaptarFormularioPorEstado();
    $('#beneficiario').val(data.beneficiario);
    
    modalCheque.show();
}

$('#formCheque').submit(function(e) {
    e.preventDefault();
    
    // 1. Creamos el FormData directamente del formulario REAL tal cual está.
    // Como el beneficiario puede estar disabled, FormData no lo va a incluir automáticamente si está bloqueado.
    let formData = new FormData(this);
    
    // 2. Resolvemos el valor del beneficiario de forma manual según el estado
    // Si está deshabilitado (RECIBIDO), forzamos "RECURSOS GLOBALES"
    if ($('#estado').val() === 'RECIBIDO') {
        formData.set('beneficiario', 'RECURSOS GLOBALES');
    } else {
        // Si está habilitado, nos aseguramos de tomar lo que el usuario seleccionó en la UI
        formData.set('beneficiario', $('#beneficiario').val());
    }
    
    // 3. Enviamos los datos limpios. El navegador no se queja porque nunca alteramos el DOM ni los archivos.
    $.ajax({
        url: '/contable/ajax/cheques.php?accion=guardar',
        method: 'POST',
        data: formData,
        contentType: false, // Requerido para archivos binarios
        processData: false, // Requerido para archivos binarios
        dataType: 'json',
        success: function(resp) {
            if(resp.success) {
                tabla.ajax.reload();
                modalCheque.hide();
            } else {
                alert('Error al guardar: ' + resp.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en la petición AJAX:", textStatus, errorThrown);
        }
    });
});

window.eliminarCheque = function(id) {
    if (!confirm('¿Eliminar este cheque del sistema?')) return;
    if (prompt('Escribí OK para confirmar') !== 'OK') return;

    $.post('/contable/ajax/cheques.php?accion=eliminar', { id }, function(resp) {
        tabla.ajax.reload();
    }, 'json');
}
</script>