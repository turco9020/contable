<?php
include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<div class="topbar d-flex justify-content-between">
<h5>Subcategorías</h5>
<button class="btn btn-dark" onclick="abrirModal()">+ Nuevo</button>
</div>

<div class="content">
<table id="tabla" class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Categoría</th>
<th>Acciones</th>
</tr>
</thead>
</table>
</div>

<div class="modal fade" id="modal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<form id="form">

<input type="hidden" name="id" id="id">

<select name="categoria_id" id="categoria_id" class="form-control mb-2" required></select>

<input name="nombre" id="nombre" class="form-control mb-2" placeholder="Nombre" required>

<button class="btn btn-dark w-100">Guardar</button>

</form>

</div>
</div>
</div>
</div>

<script>

function cargarCategorias(callback){
    $.get('/contable/ajax/categorias.php?accion=listar', function(res){

        let select = $('#categoria_id');
        select.empty();

        res.data.forEach(c=>{
            select.append(`<option value="${c.id}">${c.nombre}</option>`);
        });

        if(callback) callback();

    },'json');
}

window.abrirModal = function(){
    document.getElementById('form').reset();
    document.getElementById('id').value = '';

    cargarCategorias();

    new bootstrap.Modal(document.getElementById('modal')).show();
}

window.onload = function(){

    let tabla = $('#tabla').DataTable({
        ajax:'/contable/ajax/subcategorias.php?accion=listar',
        columns:[
            {data:'id'},
            {data:'nombre'},
            {data:'categoria'},
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

    document.getElementById('form').addEventListener('submit', function(e){
        e.preventDefault();

        $.post('/contable/ajax/subcategorias.php?accion=guardar', $(this).serialize(), function(){
            tabla.ajax.reload();
            bootstrap.Modal.getInstance(document.getElementById('modal')).hide();
        });
    });

    window.editar = function(d){

        document.getElementById('id').value = d.id;
        document.getElementById('nombre').value = d.nombre;

        // cargar categorías y luego seleccionar la correcta
        cargarCategorias(function(){
            $('#categoria_id').val(d.categoria_id);
        });

        new bootstrap.Modal(document.getElementById('modal')).show();
    }

    window.eliminar = function(id){

        if(!confirm("¿Eliminar subcategoría?")) return;

        let confirmacion = prompt("Escribí ELIMINAR para confirmar");

        if(confirmacion !== "ELIMINAR"){
            alert("Acción cancelada");
            return;
        }

        $.post('/contable/ajax/subcategorias.php?accion=eliminar', {id:id}, function(){
            tabla.ajax.reload();
        });
    }

}
</script>

<?php include '../../../includes/footer.php'; ?>