<?php
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="topbar d-flex justify-content-between align-items-center">
    <h5>Proveedores</h5>
    <button class="btn btn-dark" onclick="abrirModal()">+ Nuevo</button>
</div>

<div class="content">

    <div class="card p-3 shadow-sm">
        <table id="tablaProveedores" class="table table-bordered table-striped">
        <thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>CUIT</th>
    <th>Condición</th>
    <th>Producto/Servicio</th>
    <th>Teléfono</th>

    <!-- ocultas -->
    <th>Dirección</th>
    <th>Localidad</th>
    <th>Provincia</th>
    <th>CP</th>
    <th>Whatsapp</th>
    <th>Contacto</th>
    <th>Observaciones</th>

    <th>Acciones</th>
</tr>
</thead>
        </table>
    </div>

</div>

<!-- MODAL COMPLETO -->
<div class="modal fade" id="modalProveedor">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Proveedor</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formProveedor">

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
                            <input type="text" name="producto_servicio" id="producto_servicio" class="form-control mb-2" placeholder="Producto / Servicio">
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

<script>
document.addEventListener("DOMContentLoaded", function(){

let tabla = $('#tablaProveedores').DataTable({

    dom: 'Bfrtip',

    buttons: [
        {
            extend: 'excel',
            text: 'Excel',
            className: 'btn btn-sm btn-secondary',
            exportOptions: {
                columns: ':not(:last-child)' // excluye acciones
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

        // 👇 COLUMNAS OCULTAS PERO EXPORTABLES
        {data:'direccion', visible:false, defaultContent:''},
        {data:'localidad', visible:false, defaultContent:''},
        {data:'provincia', visible:false, defaultContent:''},
        {data:'cp', visible:false, defaultContent:''},
        {data:'whatsapp', visible:false, defaultContent:''},
        {data:'contacto', visible:false, defaultContent:''},
        {data:'observaciones', visible:false, defaultContent:''},

        {
            data:null,
            orderable:false,
            render: function(data){
                return `
                    <button class="btn btn-sm btn-secondary" onclick='ver(${JSON.stringify(data)})'>Ver</button>
                    <button class="btn btn-sm btn-primary" onclick='editar(${JSON.stringify(data)})'>Editar</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${data.id})">Eliminar</button>
                `;
            }
        }
    ]

});
    window.abrirModal = function(){
        $('#formProveedor')[0].reset();
        $('#id').val('');
        $('#formProveedor input, textarea, select').prop('disabled', false);
        $('#formProveedor button').show();
        new bootstrap.Modal(document.getElementById('modalProveedor')).show();
    }

    $('#formProveedor').submit(function(e){
        e.preventDefault();

        $.post('/contable/ajax/proveedores.php?accion=guardar', $(this).serialize(), function(){
            tabla.ajax.reload();
            bootstrap.Modal.getInstance(document.getElementById('modalProveedor')).hide();
        });
    });

    window.editar = function(data){
        for(let k in data){
            if(document.getElementById(k)){
                document.getElementById(k).value = data[k];
            }
        }

        $('#formProveedor input, textarea, select').prop('disabled', false);
        $('#formProveedor button').show();

        new bootstrap.Modal(document.getElementById('modalProveedor')).show();
    }

    window.ver = function(data){
        for(let k in data){
            if(document.getElementById(k)){
                document.getElementById(k).value = data[k];
            }
        }

        $('#formProveedor input, textarea, select').prop('disabled', true);
        $('#formProveedor button').hide();

        new bootstrap.Modal(document.getElementById('modalProveedor')).show();
    }

    window.eliminar = function(id){
        if(confirm("Eliminar proveedor?")){
            $.post('/contable/ajax/proveedores.php?accion=eliminar', {id:id}, function(){
                tabla.ajax.reload();
            });
        }
    }

    // MAYÚSCULAS
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

<?php include '../../includes/footer.php'; ?>