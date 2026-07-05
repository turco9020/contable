<?php
include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<div class="topbar d-flex justify-content-between align-items-center">
    <h5>Medios de Pago</h5>
    <button class="btn btn-dark" onclick="abrirModal()">+ Nuevo</button>
</div>

<div class="content">

<div class="card p-3 shadow-sm">
<table id="tablaMedios" class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Acciones</th>
</tr>
</thead>
</table>
</div>

</div>

<!-- MODAL -->
<div class="modal fade" id="modalMedio">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5>Medio de Pago</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form id="formMedio">

<input type="hidden" name="id" id="id">

<label>Nombre</label>
<input name="nombre" id="nombre" class="form-control mb-2" required>

<button class="btn btn-dark w-100">Guardar</button>

</form>

</div>
</div>
</div>
</div>

<?php include '../../../includes/footer.php'; ?>

<script>

function abrirModal(){
    $('#formMedio')[0].reset();
    $('#id').val('');
    new bootstrap.Modal(document.getElementById('modalMedio')).show();
}

document.addEventListener("DOMContentLoaded", function(){

let tabla = $('#tablaMedios').DataTable({

    ajax:'/contable/ajax/medios_pago.php?accion=listar',

    columns:[
        {data:'id'},
        {data:'nombre'},
        {
            data:null,
            render:function(d){
                return `
                <button class="btn btn-sm btn-primary" onclick='editar(${JSON.stringify(d)})'>Editar</button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${d.id})">Eliminar</button>
                `;
            }
        }
    ]
});

// GUARDAR
$('#formMedio').submit(function(e){
    e.preventDefault();

    $.post('/contable/ajax/medios_pago.php?accion=guardar', $(this).serialize(), ()=>{
        tabla.ajax.reload();
        bootstrap.Modal.getInstance(document.getElementById('modalMedio')).hide();
    });
});

// EDITAR
window.editar = function(d){
    $('#id').val(d.id);
    $('#nombre').val(d.nombre);
    new bootstrap.Modal(document.getElementById('modalMedio')).show();
}

// ELIMINAR
window.eliminar = function(id){

    if(!confirm('Eliminar?')) return;
    if(prompt('Escribí OK') !== 'OK') return;

    $.post('/contable/ajax/medios_pago.php?accion=eliminar',{id},()=>{
        tabla.ajax.reload();
    });
}

});

</script>

