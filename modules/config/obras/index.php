<?php
include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<div class="topbar d-flex justify-content-between align-items-center">
    <h5>Obras</h5>
    <button class="btn btn-dark" onclick="abrirModal()">+ Nueva</button>
</div>

<div class="content">

<div class="card p-3 shadow-sm">
<table id="tablaObras" class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Cliente</th>
<th>Fecha Inicio</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
</table>
</div>

</div>

<!-- MODAL -->
<div class="modal fade" id="modalObra">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5>Obra</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form id="formObra">

<input type="hidden" name="id" id="id">

<label>Nombre</label>
<input name="nombre" id="nombre" class="form-control mb-2">

<label>Cliente</label>
<select name="cliente_id" id="cliente_id" class="form-control mb-2"></select>

<label>Fecha inicio</label>
<input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control mb-2">

<label>Detalle</label>
<textarea name="detalle" id="detalle" class="form-control mb-2"></textarea>

<label>Estado</label>
<select name="estado" id="estado" class="form-control mb-2">
<option value="ACTIVA">ACTIVA</option>
<option value="FINALIZADA">FINALIZADA</option>
</select>

<button class="btn btn-dark w-100">Guardar</button>

</form>

</div>
</div>
</div>
</div>

<script>

// ================= CLIENTES =================
function cargarClientes(){

    $.get('/contable/ajax/clientes.php?accion=listar', function(r){

        let s = $('#cliente_id');
        s.empty().append('<option value="">Seleccione</option>');

        r.data.forEach(x=>{
            s.append(`<option value="${x.id}">${x.nombre}</option>`);
        });

    }, 'json');
}

// ================= MODAL =================
function abrirModal(){

    $('#formObra')[0].reset();
    $('#id').val('');

    cargarClientes();

    new bootstrap.Modal(document.getElementById('modalObra')).show();
}

// ================= INIT =================
document.addEventListener("DOMContentLoaded", function(){

let tabla = $('#tablaObras').DataTable({

    ajax:'/contable/ajax/obras.php?accion=listar',

    columns:[
        {data:'id'},
        {data:'nombre'},
        {data:'cliente'},
        {
            data:'fecha_inicio',
            render:function(d){
                if(!d) return '';
                let p=d.split('-');
                return `${p[2]}/${p[1]}/${p[0]}`;
            }
        },
        {data:'estado'},
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

// ================= GUARDAR =================
$('#formObra').submit(function(e){
    e.preventDefault();

    $.post('/contable/ajax/obras.php?accion=guardar', $(this).serialize(), ()=>{
        tabla.ajax.reload();
        bootstrap.Modal.getInstance(document.getElementById('modalObra')).hide();
    });
});

// ================= EDITAR =================
window.editar = function(d){

    cargarClientes();

    setTimeout(function(){

        $('#id').val(d.id);
        $('#nombre').val(d.nombre);
        $('#cliente_id').val(d.cliente_id);
        $('#fecha_inicio').val(d.fecha_inicio);
        $('#detalle').val(d.detalle);
        $('#estado').val(d.estado);

    },200);

    new bootstrap.Modal(document.getElementById('modalObra')).show();
}

// ================= ELIMINAR =================
window.eliminar = function(id){

    if(!confirm('Eliminar obra?')) return;
    if(prompt('Escribí OK') !== 'OK') return;

    $.post('/contable/ajax/obras.php?accion=eliminar',{id},()=>{
        tabla.ajax.reload();
    });
}

});
</script>

<?php include '../../../includes/footer.php'; ?>