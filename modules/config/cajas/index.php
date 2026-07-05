<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/sidebar.php';
?>

<div class="content">

    <div class="d-flex justify-content-between mb-3">
        <h4>Cajas</h4>

        <button class="btn btn-dark" onclick="abrirModal()">
            + Nueva Caja
        </button>
    </div>

    <table id="tablaCajas" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Activa</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

</div>

<!-- MODAL -->

<div class="modal fade" id="modalCaja">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
    <h5>Caja</h5>
</div>

<div class="modal-body">

<form id="formCaja">

    <input type="hidden" id="id" name="id">

    <label>Nombre</label>
    <input type="text"
           class="form-control mb-2"
           id="nombre"
           name="nombre"
           required>

    <label>Descripción</label>
    <input type="text"
           class="form-control mb-2"
           id="descripcion"
           name="descripcion">

    <label>Activa</label>
    <select class="form-control mb-2"
            id="activa"
            name="activa">

        <option value="1">SI</option>
        <option value="0">NO</option>

    </select>

    <button class="btn btn-dark w-100">
        Guardar
    </button>

</form>

</div>

</div>
</div>
</div>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/footer.php';
?>

<script>

let tabla;
let modalCaja;

document.addEventListener("DOMContentLoaded", function(){

    modalCaja = new bootstrap.Modal(
        document.getElementById('modalCaja')
    );

    tabla = $('#tablaCajas').DataTable({

        ajax:'/contable/ajax/cajas.php?accion=listar',

        columns:[
            {data:'id'},
            {data:'nombre'},
            {data:'descripcion'},
            {
                data:'activa',
                render:function(d){
                    return d == 1 ? 'SI' : 'NO';
                }
            },
            {
                data:null,
                render:function(d){

                    return `
                    <button class="btn btn-sm btn-primary"
                        onclick='editar(${JSON.stringify(d)})'>
                        Editar
                    </button>

                    <button class="btn btn-sm btn-outline-danger"
                        onclick="eliminar(${d.id})">
                        Eliminar
                    </button>
                    `;
                }
            }
        ]
    });

});

// NUEVO

function abrirModal(){

    $('#formCaja')[0].reset();
    $('#id').val('');

    modalCaja.show();
}

// GUARDAR

$('#formCaja').submit(function(e){

    e.preventDefault();

    $.post(
        '/contable/ajax/cajas.php?accion=guardar',
        $(this).serialize(),
        function(){

            tabla.ajax.reload();

            modalCaja.hide();
        }
    );

});

// EDITAR

window.editar = function(d){

    $('#id').val(d.id);
    $('#nombre').val(d.nombre);
    $('#descripcion').val(d.descripcion);
    $('#activa').val(d.activa);

    modalCaja.show();
}

// ELIMINAR

window.eliminar = function(id){

    if(!confirm('¿Eliminar caja?')) return;

    if(prompt('Escribí OK') != 'OK') return;

    $.post(
        '/contable/ajax/cajas.php?accion=eliminar',
        {id},
        function(){

            tabla.ajax.reload();
        }
    );
}

</script>

