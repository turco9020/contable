<?php
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content">

    <!-- CABECERA DEL MÓDULO (Estilo unificado) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-people text-secondary me-2"></i> Gestión de Clientes
        </h4>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal()">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Cliente
        </button>
    </div>

    <!-- CONTENEDOR DE LA TABLA (Card Limpia con Exportación) -->
    <div class="card p-3 shadow-sm border-0">
        <div class="table-responsive">
            <table id="tablaClientes" class="table table-bordered table-striped w-100">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre / Razón Social</th>
                        <th>CUIT</th>
                        <th>Condición Fiscal</th>
                        <th>Teléfono</th>
                        <th>WhatsApp</th>
                        <!-- Ocultas pero exportables -->
                        <th>Dirección</th>
                        <th>Localidad</th>
                        <th>Provincia</th>
                        <th>CP</th>
                        <th>Contacto</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- MODAL ESTRUCTURADO (Estilo Proveedores) -->
<div class="modal fade" id="modalCliente" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModal">
                    <i class="bi bi-person-badge me-2"></i>Gestión de Cliente
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formCliente">

                    <input type="hidden" name="id" id="id">

                    <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">1. Información General</h6>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Nombre / Razón Social</label>
                            <input type="text" name="nombre" id="nombre" class="form-control fw-bold" placeholder="Nombre completo o Razón Social" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">CUIT</label>
                            <input type="text" name="cuit" id="cuit" class="form-control fw-bold" placeholder="00-00000000-0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Condición Fiscal</label>
                            <select name="condicion_fiscal" id="condicion_fiscal" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <option value="RESPONSABLE INSCRIPTO">RESPONSABLE INSCRIPTO</option>
                                <option value="MONOTRIBUTISTA">MONOTRIBUTISTA</option>
                                <option value="EXENTO">EXENTO</option>
                                <option value="CONSUMIDOR FINAL">CONSUMIDOR FINAL</option>
                                <option value="NN">NN</option>
                            </select>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: UBICACIÓN Y CONTACTO -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">2. Ubicación y Medios de Contacto</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Dirección</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Calle y número">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Localidad</label>
                            <input type="text" name="localidad" id="localidad" class="form-control" placeholder="Ciudad / Localidad">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Provincia</label>
                            <input type="text" name="provincia" id="provincia" class="form-control" placeholder="Provincia">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">CP</label>
                            <input type="text" name="cp" id="cp" class="form-control" placeholder="Código Postal">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Persona de Contacto</label>
                            <input type="text" name="contacto" id="contacto" class="form-control" placeholder="Nombre del contacto comercial/operativo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" placeholder="Teléfono de línea o fijo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">WhatsApp</label>
                            <input type="text" name="whatsapp" id="whatsapp" class="form-control" placeholder="Número de celular con código de área">
                        </div>
                    </div>

                    <!-- SECCIÓN 3: INFORMACIÓN COMPLEMENTARIA -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">3. Observaciones</h6>
                        </div>
                        <div class="col-12">
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Notas adicionales sobre este cliente..."></textarea>
                        </div>
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark px-5">Guardar Cliente</button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
let tabla;
let modalCliente;

document.addEventListener("DOMContentLoaded", function(){

    modalCliente = new bootstrap.Modal(document.getElementById('modalCliente'));

    tabla = $('#tablaClientes').DataTable({
        responsive: true,
        scrollX: false,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: 'Excel',
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            },
            {
                extend: 'pdf',
                text: 'PDF',
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            },
            {
                extend: 'print',
                text: 'Imprimir',
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            }
        ],

        ajax: '/contable/ajax/clientes.php?accion=listar',

        columns: [
            {data:'id', defaultContent:''},
            {data:'nombre', defaultContent:''},
            {data:'cuit', defaultContent:''},
            {data:'condicion_fiscal', defaultContent:''},
            {data:'telefono', defaultContent:''},
            {data:'whatsapp', defaultContent:''},

            // COLUMNAS OCULTAS PERO EXPORTABLES
            {data:'direccion', visible:false, defaultContent:''},
            {data:'localidad', visible:false, defaultContent:''},
            {data:'provincia', visible:false, defaultContent:''},
            {data:'cp', visible:false, defaultContent:''},
            {data:'contacto', visible:false, defaultContent:''},
            {data:'observaciones', visible:false, defaultContent:''},

            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: function(data){
                    return `
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary" title="Ver Cliente" onclick='ver(${JSON.stringify(data)})'>
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" title="Editar Cliente" onclick='editar(${JSON.stringify(data)})'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar Cliente" onclick="eliminar(${data.id})">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $(window).on('resize', function () {
        tabla.columns.adjust().responsive.recalc();
    });

    window.abrirModal = function(){
        $('#formCliente')[0].reset();
        $('#id').val('');
        $('#formCliente input, #formCliente textarea, #formCliente select').prop('disabled', false);
        $('#formCliente button[type="submit"]').show();
        modalCliente.show();
    }

    // ENVÍO DE FORMULARIO
    $('#formCliente').submit(function(e){
        e.preventDefault();

        $.post('/contable/ajax/clientes.php?accion=guardar', $(this).serialize(), function(){
            tabla.ajax.reload();
            modalCliente.hide();
        });
    });

    window.editar = function(data){
        $('#formCliente')[0].reset();
        
        for(let k in data){
            if(document.getElementById(k)){
                document.getElementById(k).value = data[k];
            }
        }

        $('#formCliente input, #formCliente textarea, #formCliente select').prop('disabled', false);
        $('#formCliente button[type="submit"]').show();

        modalCliente.show();
    }

    window.ver = function(data){
        window.editar(data);
        setTimeout(() => {
            $('#formCliente input, #formCliente textarea, #formCliente select').prop('disabled', true);
            $('#formCliente button[type="submit"]').hide();
        }, 100);
    }

    // SWEETALERT2 CON ELIMINACIÓN CRÍTICA REQUERIDA
    window.eliminar = function(id){
        Swal.fire({
            title: '¿Confirmación crítica?',
            text: 'Para eliminar este cliente permanentemente, escribe la palabra "ELIMINAR" a continuación:',
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Escribe ELIMINAR aquí...',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#212529',
            confirmButtonText: 'Confirmar eliminación',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value) {
                    return '¡Debes escribir la palabra de confirmación!';
                }
                if (value !== 'ELIMINAR') {
                    return 'La palabra no coincide. Intenta de nuevo (en mayúsculas).';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/contable/ajax/clientes.php?accion=eliminar', {id: id}, function(){
                    tabla.ajax.reload();
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El cliente ha sido borrado correctamente.',
                        icon: 'success',
                        confirmButtonColor: '#212529'
                    });
                });
            }
        });
    }

    // MAYÚSCULAS AUTOMÁTICAS
    $(document).on('input', 'input, textarea', function(){
        this.value = this.value.toUpperCase();
    });

    // FORMATO DE CUIT
    $('#cuit').on('input', function(){
        let val = this.value.replace(/\D/g, '').slice(0,11);

        if(val.length > 2 && val.length <= 10)
            val = val.replace(/^(\d{2})(\d+)/, '$1-$2');
        else if(val.length > 10)
            val = val.replace(/^(\d{2})(\d{8})(\d{1})$/, '$1-$2-$3');

        this.value = val;
    });

});
</script>