<?php
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content">
<!-- CABECERA DEL MÓDULO UNIFICADA -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold text-dark mb-0">
        <i class="bi bi-people text-secondary me-2"></i>Clientes
    </h5>
    <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal()">
        <i class="bi bi-plus-circle me-2"></i>Nuevo
    </button>
</div>

    <div class="card p-3 shadow-sm">
        <table id="tablaClientes" class="table table-bordered table-striped w-100">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>CUIT</th>
                    <th>Condición</th>
                    <th>Whatsapp</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

</div>

<!-- MODAL -->
<div class="modal fade" id="modalCliente">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formCliente">

                    <input type="hidden" name="id" id="id">

                    <div class="row">

                        <div class="col-md-6">
                            <input type="text" name="nombre" id="nombre" class="form-control mb-2 fw-bold" placeholder="Nombre" required>
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="cuit" id="cuit" class="form-control mb-2 fw-bold" placeholder="CUIT">
                        </div>

                        <div class="col-md-6">
                            <select name="condicion_fiscal" id="condicion_fiscal" class="form-control mb-2">
                                <option value="">Condición Fiscal</option>
                                <option>RESPONSABLE INSCRIPTO</option>
                                <option>MONOTRIBUTISTA</option>
                                <option>EXENTO</option>
                                <option>NN</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="direccion" id="direccion" class="form-control mb-2" placeholder="Dirección">
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="localidad" id="localidad" class="form-control mb-2" placeholder="Localidad">
                        </div>

                        <div class="col-md-4">
                            <input type="text" name="provincia" id="provincia" class="form-control mb-2" placeholder="Provincia">
                        </div>

                        <div class="col-md-2">
                            <input type="text" name="cp" id="cp" class="form-control mb-2" placeholder="CP">
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="whatsapp" id="whatsapp" class="form-control mb-2" placeholder="Whatsapp">
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="telefono" id="telefono" class="form-control mb-2" placeholder="Teléfono">
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="contacto" id="contacto" class="form-control mb-2" placeholder="Persona de contacto">
                        </div>

                        <div class="col-md-12">
                            <textarea name="observaciones" id="observaciones" class="form-control mb-2" placeholder="Observaciones"></textarea>
                        </div>

                    </div>

                    <button class="btn btn-dark w-100">Guardar</button>

                </form>
            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
let tabla;

document.addEventListener("DOMContentLoaded", function(){

    tabla = $('#tablaClientes').DataTable({
        responsive: true,
        scrollX: false,
        autoWidth: false,
        ajax: '/contable/ajax/clientes.php?accion=listar',
        columns: [
            {data:'id'},
            {data:'nombre'},
            {data:'cuit'},
            {data:'condicion_fiscal'},
            {data:'whatsapp'},
            {data:'telefono'},
            {
                data: null,
                orderable: false,
                className: 'text-center',
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
        document.getElementById('formCliente').reset();
        document.getElementById('id').value = '';

        $('#formCliente input, #formCliente textarea, #formCliente select').prop('disabled', false);
        $('#formCliente button').show();

        new bootstrap.Modal(document.getElementById('modalCliente')).show();
    }

    document.getElementById('formCliente').addEventListener('submit', function(e){
        e.preventDefault();

        $.post('/contable/ajax/clientes.php?accion=guardar', $(this).serialize(), function(){
            tabla.ajax.reload();
            bootstrap.Modal.getInstance(document.getElementById('modalCliente')).hide();
        });
    });

    window.editar = function(data){
        for(let key in data){
            if(document.getElementById(key)){
                document.getElementById(key).value = data[key];
            }
        }

        $('#formCliente input, #formCliente textarea, #formCliente select').prop('disabled', false);
        $('#formCliente button').show();

        new bootstrap.Modal(document.getElementById('modalCliente')).show();
    }

    window.ver = function(data){
        for(let key in data){
            if(document.getElementById(key)){
                document.getElementById(key).value = data[key];
            }
        }

        $('#formCliente input, #formCliente textarea, #formCliente select').prop('disabled', true);
        $('#formCliente button').hide();

        new bootstrap.Modal(document.getElementById('modalCliente')).show();
    }

    // SWEETALERT2 CON ELIMINACIÓN CRÍTICA REQUERIDA
    window.eliminar = function(id){
        Swal.fire({
            title: '¿Estás completamente seguro?',
            text: 'Esta acción no se puede deshacer. Para proceder, por favor escribe "ELIMINAR" en el campo de abajo:',
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Escribe ELIMINAR aquí...',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#212529',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes escribir la palabra de confirmación.';
                }
                if (value !== 'ELIMINAR') {
                    return 'La palabra no coincide. Debe ser exactamente ELIMINAR.';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/contable/ajax/clientes.php?accion=eliminar', {id: id}, function(){
                    tabla.ajax.reload();
                    Swal.fire({
                        title: 'Eliminado',
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

    // FORMATO CUIT
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