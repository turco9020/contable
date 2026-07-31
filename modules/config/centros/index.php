<?php
include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<div class="topbar d-flex justify-content-between">
<h5 class="fw-bold"><i class="bi text-secondary me-2"></i>Centros de Costo</h5>
<button class="btn btn-dark" onclick="abrirModal()">+ Nuevo</button>
</div>

<div class="content">
<table id="tabla" class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Acciones</th>
</tr>
</thead>
</table>
</div>

<!-- MODAL -->
<div class="modal fade" id="modal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<form id="form">
<input type="hidden" name="id" id="id">
<input name="nombre" id="nombre" class="form-control mb-2" placeholder="Nombre" required>
<button class="btn btn-dark w-100">Guardar</button>
</form>

</div>
</div>
</div>
</div>
<script>

window.abrirModal = function(){
    document.getElementById('form').reset();
    document.getElementById('id').value = '';
    new bootstrap.Modal(document.getElementById('modal')).show();
}

window.onload = function(){

    let tabla = $('#tabla').DataTable({
        ajax:'/contable/ajax/centros.php?accion=listar',
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

    document.getElementById('form').addEventListener('submit', function(e){
        e.preventDefault();

        let datos = $(this).serialize();

        console.log(datos); // 👈 DEBUG

        $.post('/contable/ajax/centros.php?accion=guardar', datos, function(res){
            console.log(res);
            tabla.ajax.reload();
            bootstrap.Modal.getInstance(document.getElementById('modal')).hide();
        });
    });

    window.editar = function(d){
        document.getElementById('id').value = d.id;
        document.getElementById('nombre').value = d.nombre;

        new bootstrap.Modal(document.getElementById('modal')).show();
    }

    window.eliminar = function(id){

        if(!confirm("¿Eliminar centro de costo?")) return;

        // segunda confirmación (clave)
        let confirmacion = prompt("Escribí ELIMINAR para confirmar");

        if(confirmacion !== "ELIMINAR"){
            alert("Acción cancelada");
            return;
        }

        $.post('/contable/ajax/centros.php?accion=eliminar', {id:id}, function(){
            tabla.ajax.reload();
        });
    }

}
</script>

<?php include '../../../includes/footer.php'; ?>