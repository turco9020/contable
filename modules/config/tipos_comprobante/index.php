<?php
include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<div class="topbar d-flex justify-content-between align-items-center">
    <h5>Tipos de Comprobante</h5>
    <button class="btn btn-dark" onclick="abrirModal()">+ Nuevo</button>
</div>

<div class="content">

<div class="card p-3 shadow-sm">
<table id="tablaTipos" class="table table-bordered table-striped">
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
<div class="modal fade" id="modalTipo">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5>Tipo de Comprobante</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form id="formTipo">

<input type="hidden" name="id" id="id">

<label>Nombre</label>
<input name="nombre" id="nombre" class="form-control mb-2" required>

<button type="submit" class="btn btn-dark w-100">Guardar</button>

</form>

</div>
</div>
</div>
</div>

<?php include '../../../includes/footer.php'; ?>

<script>

let tabla;
let modalTipo;

// ================= INIT =================
document.addEventListener("DOMContentLoaded", function(){

    modalTipo = new bootstrap.Modal(document.getElementById('modalTipo'));

    tabla = $('#tablaTipos').DataTable({

        ajax:'/contable/ajax/tipos_comprobante.php?accion=listar',

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

});

// ================= MODAL =================
function abrirModal(){
    $('#formTipo')[0].reset();
    $('#id').val('');
    modalTipo.show();
}

// ================= GUARDAR =================
$('#formTipo').on('submit', function(e){
    e.preventDefault();

    $.ajax({
        url: '/contable/ajax/tipos_comprobante.php?accion=guardar',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(resp){

            console.log(resp);

            tabla.ajax.reload();

            // 👇 CLAVE: cerrar correctamente
            document.activeElement.blur();
            modalTipo.hide();
        },
        error: function(xhr){
            console.error('Error AJAX:', xhr.responseText);
        }
    });
});

// ================= EDITAR =================
function editar(d){
    $('#id').val(d.id);
    $('#nombre').val(d.nombre);
    modalTipo.show();
}

// ================= ELIMINAR =================
function eliminar(id){

    if(!confirm('Eliminar?')) return;
    if(prompt('Escribí OK') !== 'OK') return;

    $.ajax({
        url:'/contable/ajax/tipos_comprobante.php?accion=eliminar',
        method:'POST',
        data:{id},
        success: function(){
            tabla.ajax.reload();
        }
    });
}

</script>

