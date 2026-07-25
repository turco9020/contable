<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/sidebar.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

// Consultamos los usuarios activos para poder listarlos en el desplegable
$usuarios_db = [];
$res_users = $conn->query("SELECT id, usuario FROM usuarios ORDER BY usuario");
if($res_users){
    while($u = $res_users->fetch_assoc()){
        $usuarios_db[] = $u;
    }
}
?>

<div class="topbar d-flex justify-content-between align-items-center">
    <h5>Cajas</h5>
    <button class="btn btn-dark" onclick="abrirModal()">+ Nueva Caja</button>
</div>

<div class="content">

    <table id="tablaCajas" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Asignada a</th>
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

    <label>Asignar a Usuario (Caja Chica)</label>
    <select class="form-control mb-2" id="usuario_id" name="usuario_id">
    <option value="">Ninguno (Caja Central)</option>
        <?php foreach($usuarios_db as $usr): ?>
        <option value="<?php echo $usr['id']; ?>"><?php echo htmlspecialchars($usr['usuario']); ?></option>
        <?php endforeach; ?>
    </select>

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
                data:'usuario_nombre',
                render:function(d){
                    return d ? d : '<span class="text-muted">CENTRAL</span>';
                }
            },
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
    $('#usuario_id').val(''); // Reseteamos a Caja Central por defecto

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
    $('#usuario_id').val(d.usuario_id ? d.usuario_id : ''); // Asigna el usuario o deja vacío si es NULL
    $('#activa').val(d.activa);

    modalCaja.show();
}

// ELIMINAR

window.eliminar = function(id){

    if(!confirm('¿CUIDADO!!!!! - Eliminar caja?')) return;

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