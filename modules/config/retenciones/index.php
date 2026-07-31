<?php
// Subimos un nivel más (../../../..) para salir de: retenciones -> config -> modules -> contable
include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<div class="topbar d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold"><i class="bi text-secondary me-2"></i>Tipos de Retenciones</h5>
    <button class="btn btn-dark" onclick="abrirModal()">+ Nuevo Tipo</button>
</div>

<div class="content">
    <div class="card p-3 shadow-sm">
        <table id="tabla" class="table table-bordered table-striped w-100">
            <thead class="table-dark">
                <tr>
                    <th style="width: 15%;">ID</th>
                    <th>Nombre de la Retención</th>
                    <th style="width: 25%;" class="text-center">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- MODAL CRUD -->
<div class="modal fade" id="modal" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModal">Tipo de Retención</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form">
                    <input type="hidden" name="id" id="id">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nombre del Impuesto / Concepto</label>
                        <input name="nombre" id="nombre" class="form-control" placeholder="Ej: Ganancias - Régimen general" required>
                    </div>
                    <button class="btn btn-dark w-100">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let modalInstance;

window.abrirModal = function(){
    document.getElementById('form').reset();
    document.getElementById('id').value = '';
    document.getElementById('tituloModal').innerText = 'Nueva Retención';
    modalInstance.show();
}

window.onload = function(){
    modalInstance = new bootstrap.Modal(document.getElementById('modal'));

    let tabla = $('#tabla').DataTable({
        ajax: '/contable/ajax/tipos_retenciones.php?accion=listar',
        autoWidth: false,
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            {
                data: null,
                orderable: false,
                render: function(d) {
                    return `
                        <button class="btn btn-sm btn-primary me-1" onclick='editar(${JSON.stringify(d)})'>Editar</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${d.id})">Eliminar</button>
                    `;
                }
            }
        ]
    });

    document.getElementById('form').addEventListener('submit', function(e){
        e.preventDefault();

        $.post('/contable/ajax/tipos_retenciones.php?accion=guardar', $(this).serialize(), function(r){
            tabla.ajax.reload();
            modalInstance.hide();
        });
    });

    window.editar = function(d){
        document.getElementById('id').value = d.id;
        document.getElementById('nombre').value = d.nombre;
        document.getElementById('tituloModal').innerText = 'Editar Retención';
        modalInstance.show();
    }

    window.eliminar = function(id){
        if(!confirm("¿Eliminar este tipo de retención? Si existen facturas asociadas a esta retención podría generar errores.")) return;

        let confirmacion = prompt("Escribí ELIMINAR para confirmar");
        if(confirmacion !== "ELIMINAR"){
            alert("Acción cancelada");
            return;
        }

        $.post('/contable/ajax/tipos_retenciones.php?accion=eliminar', {id: id}, function(r){
            tabla.ajax.reload();
        });
    }
}
</script>

<?php 
// Subimos también cuatro niveles para el footer
include '../../../includes/footer.php'; 
?>