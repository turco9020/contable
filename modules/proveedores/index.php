<?php
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content">

    <!-- CABECERA DEL MÓDULO (Estilo unificado) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-truck text-secondary me-2"></i> Gestión de Proveedores
        </h4>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal()">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Proveedor
        </button>
    </div>

    <!-- CONTENEDOR DE LA TABLA (Card Limpia) -->
    <div class="card p-3 shadow-sm border-0">
        <div class="table-responsive">
            <table id="tablaProveedores" class="table table-bordered table-striped w-100">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>CUIT</th>
                        <th>Condición</th>
                        <th>Producto/Servicio</th>
                        <th>Teléfono</th>
                        <!-- Ocultas pero exportables -->
                        <th>Dirección</th>
                        <th>Localidad</th>
                        <th>Provincia</th>
                        <th>CP</th>
                        <th>Whatsapp</th>
                        <th>Contacto</th>
                        <th>CBU</th>
                        <th>Alias</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- MODAL COMPLETO (Estilo Gastos) -->
<div class="modal fade" id="modalProveedor" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModal">
                    <i class="bi bi-truck me-2"></i>Gestión de Proveedor
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formProveedor" enctype="multipart/form-data">

                    <input type="hidden" name="id" id="id">

                    <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">1. Información General</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Nombre / Razón Social</label>
                            <input type="text" name="nombre" id="nombre" class="form-control fw-bold" placeholder="Nombre completo o Razón Social" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">CUIT</label>
                            <input type="text" name="cuit" id="cuit" class="form-control fw-bold" placeholder="00-00000000-0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Condición Fiscal</label>
                            <select name="condicion_fiscal" id="condicion_fiscal" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="RESPONSABLE INSCRIPTO">RESPONSABLE INSCRIPTO</option>
                                <option value="MONOTRIBUTISTA">MONOTRIBUTISTA</option>
                                <option value="EXENTO">EXENTO</option>
                                <option value="NN">NN</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Producto / Servicio</label>
                            <input type="text" name="producto_servicio" id="producto_servicio" class="form-control" placeholder="Rubro o actividad principal" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Persona de Contacto</label>
                            <input type="text" name="contacto" id="contacto" class="form-control" placeholder="Nombre del contacto comercial/operativo">
                        </div>
                    </div>

                    <!-- SECCIÓN 2: UBICACIÓN Y CONTACTO -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">2. Ubicación y Medios de Contacto</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Dirección</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Calle y número" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Localidad</label>
                            <input type="text" name="localidad" id="localidad" class="form-control" placeholder="Ciudad / Localidad" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Provincia</label>
                            <input type="text" name="provincia" id="provincia" class="form-control" placeholder="Provincia" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">CP</label>
                            <input type="text" name="cp" id="cp" class="form-control" placeholder="Código Postal">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" placeholder="Teléfono de línea o fijo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">WhatsApp</label>
                            <input type="text" name="whatsapp" id="whatsapp" class="form-control" placeholder="Número de celular con código de área">
                        </div>
                    </div>

                    <!-- SECCIÓN 3: DATOS BANCARIOS Y ADJUNTO -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">3. Información Bancaria y Documentación</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">CBU / CVU</label>
                            <input type="text" name="cbu" id="cbu" class="form-control" placeholder="22 dígitos" maxlength="22">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Alias</label>
                            <input type="text" name="alias" id="alias" class="form-control" placeholder="Ej: EMPRESA.PAGO.BANCO">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Archivo Adjunto</label>
                            <input type="file" name="archivo" id="archivo" class="form-control">
                            <div id="archivo_actual" class="mt-2"></div>
                            <button type="button" id="btnEliminarArchivo" class="btn btn-sm btn-outline-danger w-100 mt-2" style="display:none;">
                                <i class="bi bi-trash me-1"></i> Eliminar archivo actual
                            </button>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Notas adicionales sobre este proveedor..."></textarea>
                        </div>
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark px-5">Guardar Proveedor</button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
let tabla;
let modalProveedor;

document.addEventListener("DOMContentLoaded", function(){

    modalProveedor = new bootstrap.Modal(document.getElementById('modalProveedor'));

    tabla = $('#tablaProveedores').DataTable({
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

        ajax: '/contable/ajax/proveedores.php?accion=listar',

        columns: [
            {data:'id', defaultContent:''},
            {data:'nombre', defaultContent:''},
            {data:'cuit', defaultContent:''},
            {data:'condicion_fiscal', defaultContent:''},
            {data:'producto_servicio', defaultContent:''},
            {data:'telefono', defaultContent:''},

            // COLUMNAS OCULTAS PERO EXPORTABLES
            {data:'direccion', visible:false, defaultContent:''},
            {data:'localidad', visible:false, defaultContent:''},
            {data:'provincia', visible:false, defaultContent:''},
            {data:'cp', visible:false, defaultContent:''},
            {data:'whatsapp', visible:false, defaultContent:''},
            {data:'contacto', visible:false, defaultContent:''},
            {data:'cbu', visible:false, defaultContent:''},
            {data:'alias', visible:false, defaultContent:''},
            {data:'observaciones', visible:false, defaultContent:''},

            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: function(data){
                    let btnArchivo = data.archivo ? `<a href="/contable/uploads/proveedores/${data.archivo}" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver Adjunto"><i class="bi bi-file-earmark-pdf"></i></a>` : '';

                    return `
                        <div class="d-inline-flex gap-1">
                            ${btnArchivo}
                            <button class="btn btn-sm btn-outline-secondary" title="Ver Proveedor" onclick='ver(${JSON.stringify(data)})'>
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" title="Editar Proveedor" onclick='editar(${JSON.stringify(data)})'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar Proveedor" onclick="eliminar(${data.id})">
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
        $('#formProveedor')[0].reset();
        $('#id').val('');
        $('#archivo_actual').html('');
        $('#btnEliminarArchivo').hide();
        $('#formProveedor input, textarea, select').prop('disabled', false);
        $('#formProveedor button[type="submit"]').show();
        modalProveedor.show();
    }

    // ENVÍO DE FORMULARIO CON MULTIPART
    $('#formProveedor').submit(function(e){
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: '/contable/ajax/proveedores.php?accion=guardar',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(){
                tabla.ajax.reload();
                modalProveedor.hide();
            }
        });
    });

    window.editar = function(data){
        $('#formProveedor')[0].reset();
        
        for(let k in data){
            if(document.getElementById(k) && k !== 'archivo'){
                document.getElementById(k).value = data[k];
            }
        }

        $('#formProveedor input, textarea, select').prop('disabled', false);
        $('#formProveedor button[type="submit"]').show();

        if (data.archivo) {
            $('#archivo_actual').html(`
                <div class="alert alert-info py-1 px-2 mb-0 d-flex justify-content-between align-items-center">
                    <small class="text-truncate">Archivo: <b>${data.archivo}</b></small>
                    <a href="/contable/uploads/proveedores/${data.archivo}" target="_blank" class="btn btn-xs btn-dark">Ver</a>
                </div>`);
            $('#btnEliminarArchivo').show().data('id', data.id);
        } else {
            $('#archivo_actual').html('');
            $('#btnEliminarArchivo').hide();
        }

        modalProveedor.show();
    }

    window.ver = function(data){
        window.editar(data);
        setTimeout(() => {
            $('#formProveedor input, textarea, select').prop('disabled', true);
            $('#formProveedor button[type="submit"], #btnEliminarArchivo').hide();
        }, 100);
    }

    // ELIMINAR ARCHIVO ADJUNTO
    $(document).on('click', '#btnEliminarArchivo', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: '¿Eliminar archivo adjunto?',
            text: 'El documento se borrará permanentemente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#212529',
            confirmButtonText: 'Eliminar archivo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/contable/ajax/proveedores.php?accion=eliminar_archivo', { id }, function() {
                    Swal.fire({
                        title: 'Adjunto Removido',
                        text: 'El archivo fue eliminado con éxito.',
                        icon: 'success',
                        confirmButtonColor: '#212529'
                    });
                    $('#archivo_actual').html('');
                    $('#btnEliminarArchivo').hide();
                    tabla.ajax.reload();
                });
            }
        });
    });

    // ELIMINAR PROVEEDOR COMPLETO
    window.eliminar = function(id) {
        Swal.fire({
            title: '¿Confirmación crítica?',
            text: 'Para eliminar este proveedor permanentemente, escribe la palabra "ELIMINAR" a continuación:',
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
                $.post('/contable/ajax/proveedores.php?accion=eliminar', { id: id }, function() {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El proveedor ha sido borrado correctamente.',
                        icon: 'success',
                        confirmButtonColor: '#212529'
                    });
                    tabla.ajax.reload();
                });
            }
        });
    }

    // MAYÚSCULAS AUTOMÁTICAS
    $(document).on('input', 'input:not([type="file"]), textarea', function(){
        this.value = this.value.toUpperCase();
    });

    // FORMATO Y VALIDACIÓN DE CUIT Y CBU
    $('#cuit').on('input', function(){
        let val = this.value.replace(/\D/g, '').slice(0,11);

        if(val.length > 2 && val.length <= 10)
            val = val.replace(/^(\d{2})(\d+)/, '$1-$2');
        else if(val.length > 10)
            val = val.replace(/^(\d{2})(\d{8})(\d{1})$/, '$1-$2-$3');

        this.value = val;
    });

    $('#cbu').on('input', function(){
        this.value = this.value.replace(/\D/g, '').slice(0, 22);
    });

});
</script>