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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-shield-lock text-secondary me-2"></i> Gestión de Roles</h4>
        <button type="button" class="btn btn-dark d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalRol">
            <i class="bi bi-shield-plus me-2"></i> Nuevo Rol
        </button>
    </div>

    <!-- Contenedor de la Tabla -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle w-100" id="tablaRoles">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Rol</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultadoRoles->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td class="fw-semibold text-uppercase"><?= htmlspecialchars($row['nombre']) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarRol(<?= $row['id'] ?>)">
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
</div>

<!-- =======================================================================
     MODAL PARA AGREGAR NUEVO ROL
     ======================================================================= -->
<div class="modal fade" id="modalRol" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalRolLabel"><i class="bi bi-shield-plus me-2"></i> Registrar Rol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRol">
                <div class="modal-body">
                    <div id="alertaModalRol" class="alert alert-danger d-none"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre del Rol</label>
                        <input type="text" name="nombre_rol" class="form-control" placeholder="Ej: gerente, contador, auditor" required>
                        <div class="form-text">Se recomienda ingresarlo en minúsculas. El sistema lo procesará automáticamente.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark">Guardar Rol</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- =======================================================================
     LÓGICA JAVASCRIPT / DATATABLES / AJAX
     ======================================================================= -->
<script>
$(document).ready(function() {
    $('#tablaRoles').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
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