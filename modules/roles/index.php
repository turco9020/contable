<?php
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Control de acceso estricto
if (!esAdmin()) {
    header("Location: /contable/index.php?status=no_autorizado");
    exit;
}

// Obtener la lista de roles
$queryRoles = "SELECT id, nombre FROM roles ORDER BY id DESC";
$resultadoRoles = $conn->query($queryRoles);
?>

<div class="content">

    <!-- CABECERA DE LA PÁGINA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-shield-lock text-secondary me-2"></i> Gestión de Roles
        </h4>
        <button type="button" class="btn btn-dark d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalRol">
            <i class="bi bi-shield-plus me-2"></i> Nuevo Rol
        </button>
    </div>

    <!-- CONTENEDOR DE LA TABLA -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle w-100 mb-0" id="tablaRoles">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 15%;">ID</th>
                            <th>Nombre del Rol</th>
                            <th style="width: 15%;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultadoRoles->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td class="fw-bold text-uppercase">
                                    <i class="bi bi-shield-check me-2 text-secondary"></i>
                                    <?= htmlspecialchars($row['nombre']) ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar Rol" onclick="eliminarRol(<?= $row['id'] ?>)">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECCIÓN INFORMATIVA SOBRIA: ALCANCE DE ROLES -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white fw-bold py-3">
            <i class="bi bi-info-circle me-2"></i> Descripción de Alcances y Permisos del Sistema
        </div>

        <div class="card-body p-4 bg-light">
            <div class="row g-3">
                <!-- TARJETA ADMIN -->
                <div class="col-md-3">
                    <div class="p-3 bg-white rounded border border-secondary-subtle h-100">
                        <span class="fw-bold text-dark d-block mb-2 border-bottom pb-1">
                            <i class="bi bi-shield-fill me-1 text-dark"></i> ADMIN
                        </span>
                        <p class="small text-muted mb-0">
                            Acceso total al sistema (ve y edita todo), administración de usuarios, gestión de roles y confirmaciones críticas de eliminación.
                        </p>
                    </div>
                </div>

                <!-- TARJETA CONTADOR -->
                <div class="col-md-3">
                    <div class="p-3 bg-white rounded border border-secondary-subtle h-100">
                        <span class="fw-bold text-dark d-block mb-2 border-bottom pb-1">
                            <i class="bi bi-calculator me-1 text-secondary"></i> CONTADOR
                        </span>
                        <p class="small text-muted mb-0">
                            Ve y edita todo (excepto configuraciones y usuarios/roles). Gestión de gastos, facturación, proveedores, clientes, reportes contables y exportaciones a Excel/PDF.
                        </p>
                    </div>
                </div>

                <!-- TARJETA ARQUITECTO -->
                <div class="col-md-3">
                    <div class="p-3 bg-white rounded border border-secondary-subtle h-100">
                        <span class="fw-bold text-dark d-block mb-2 border-bottom pb-1">
                            <i class="bi bi-building me-1 text-secondary"></i> ARQUITECTO
                        </span>
                        <p class="small text-muted mb-0">
                            Gestión y seguimiento de obras, asignación de centros de costo y control de presupuestos asignados.
                        </p>
                    </div>
                </div>

                <!-- TARJETA OPERADOR -->
                <div class="col-md-3">
                    <div class="p-3 bg-white rounded border border-secondary-subtle h-100">
                        <span class="fw-bold text-dark d-block mb-2 border-bottom pb-1">
                            <i class="bi bi-person-gear me-1 text-secondary"></i> OPERADOR
                        </span>
                        <p class="small text-muted mb-0">
                            Solo puede cargar gastos, ver caja y cargar/editar proveedores.
                        </p>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN DASHBOARD PERSONALIZADO -->
            <div class="mt-4 p-3 bg-white rounded border border-secondary-subtle">
                <span class="fw-bold text-dark d-block mb-2 border-bottom pb-1">
                    <i class="bi bi-key-fill me-1 text-secondary"></i> DASHBOARD PERSONALIZADOS
                </span>
                <ul class="small text-muted mb-0 ps-3">
                    <li class="mb-1">
                        Cada rol tiene asignado un dashboard personalizado con acceso a la información correspondiente.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA AGREGAR NUEVO ROL -->
<div class="modal fade" id="modalRol" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalRolLabel"><i class="bi bi-shield-plus me-2"></i> Registrar Rol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRol">
                <div class="modal-body p-4">
                    <div id="alertaModalRol" class="alert alert-danger d-none"></div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nombre del Rol</label>
                        <input type="text" name="nombre_rol" class="form-control fw-bold" placeholder="Ej: arquitecto, operador" required>
                        <div class="form-text">El sistema lo registrará automáticamente en minúsculas.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark px-4">Guardar Rol</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
include '../../includes/footer.php'; 
?>

<!-- LÓGICA JAVASCRIPT / DATATABLES / AJAX -->
<script>
$(document).ready(function() {

    // POPUP DE ADVERTENCIA AL INGRESAR A LA PÁGINA
    Swal.fire({
        title: '¡AVISO IMPORTANTE!',
        text: 'Antes de realizar cualquier modificación en usuarios o roles, por favor lee atentamente la descripción y alcance de cada uno en la sección inferior.',
        icon: 'warning',
        confirmButtonText: 'Entendido, continuar',
        confirmButtonColor: '#212529',
        allowOutsideClick: false
    });

    $('#tablaRoles').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
    });

    // Convierte el input a minúsculas automáticamente al escribir
    $(document).on('input', '#formRol input', function(){
        this.value = this.value.toLowerCase();
    });

    // Procesar formulario por AJAX
    $('#formRol').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'procesar_rol.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    $('#alertaModalRol').removeClass('d-none').text(response.message);
                }
            },
            error: function() {
                $('#alertaModalRol').removeClass('d-none').text('Error interno del servidor.');
            }
        });
    });
});

function eliminarRol(id) {
    Swal.fire({
        title: '¿Confirmación crítica?',
        text: 'Borrar este rol puede alterar los permisos de los usuarios asignados. Escribe la palabra "ELIMINAR" para continuar:',
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Escribe ELIMINAR aquí...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#212529',
        confirmButtonText: 'Confirmar eliminación',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return '¡Debes escribir la palabra de confirmación!';
            }
            if (value !== 'ELIMINAR') {
                return 'La palabra no coincide. Intenta de nuevo (en mayúsculas).';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'eliminar_rol.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            title: '¡Rol Eliminado!',
                            text: 'El registro fue removido con éxito.',
                            icon: 'success',
                            confirmButtonColor: '#212529'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'No se pudo eliminar',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#212529'
                        });
                    }
                }
            });
        }
    });
}
</script>