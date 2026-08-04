<?php
// Incluimos el header general (que a su vez incluye la base de datos y la sesión mediante auth.php)
include '../../includes/header.php';
include '../../includes/sidebar.php';

// DOBLE CANDADO: Aunque el sidebar oculte el enlace, si intentan ingresar por URL y no son admin, los rebota
if (!esAdmin()) {
    header("Location: /contable/index.php?status=no_autorizado");
    exit;
}

// Obtener la lista de usuarios con sus respectivos roles para la tabla (Agregamos u.rol_id)
$queryUsuarios = "SELECT u.id, u.usuario, u.rol_id, r.nombre as rol_nombre 
                  FROM usuarios u 
                  JOIN roles r ON u.rol_id = r.id 
                  ORDER BY u.id DESC";
$resultadoUsuarios = $conn->query($queryUsuarios);

// Obtener los roles disponibles para cargarlos dinámicamente en el select del Modal
$queryRoles = "SELECT id, nombre FROM roles ORDER BY nombre ASC";
$resultadoRoles = $conn->query($queryRoles);

// Volvemos a generar el array de roles para el modal secundario o reset, guardando en variable limpia
$rolesSelect = [];
while ($rol = $resultadoRoles->fetch_assoc()) {
    $rolesSelect[] = $rol;
}
?>

<div class="content">

    <!-- CABECERA DE LA PÁGINA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-people text-secondary me-2"></i> Gestión de Usuarios
        </h4>
        <button type="button" class="btn btn-dark d-flex align-items-center" onclick="abrirModalUsuario('NUEVO')">
            <i class="bi bi-person-plus-fill me-2"></i> Nuevo Usuario
        </button>
    </div>

    <!-- CONTENEDOR DE LA TABLA -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle w-100 mb-0" id="tablaUsuarios">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 15%;">ID</th>
                            <th>Usuario</th>
                            <th>Rol Asignado</th>
                            <th style="width: 15%;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultadoUsuarios as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td class="fw-bold text-lowercase">
                                    <i class="bi bi-person-circle me-2 text-secondary"></i>
                                    <?= htmlspecialchars($row['usuario']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary px-2 py-1 text-uppercase"><?= htmlspecialchars($row['rol_nombre']) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-sm btn-outline-secondary" title="Ver" onclick="abrirModalUsuario('VER', <?= $row['id'] ?>, '<?= htmlspecialchars($row['usuario']) ?>', <?= $row['rol_id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="abrirModalUsuario('EDITAR', <?= $row['id'] ?>, '<?= htmlspecialchars($row['usuario']) ?>', <?= $row['rol_id'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarUsuario(<?= $row['id'] ?>)">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECCIÓN INFORMATIVA SOBRIA: ALCANCE DE USUARIOS Y CONTRASEÑAS -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white fw-bold py-3">
            <i class="bi bi-info-circle me-2"></i> Guía de Alcance de Usuarios y Manejo de Contraseñas
        </div>
        <div class="card-body p-4 bg-light">
            <div class="row">
                <div class="col-12">
                    <div class="p-3 bg-white rounded border border-secondary-subtle">
                        <span class="fw-bold text-dark d-block mb-2 border-bottom pb-1">
                            <i class="bi bi-key-fill me-1 text-secondary"></i> POLÍTICA DE CREDENCIALES Y ALCANCE DE ACCESO
                        </span>
                        <ul class="small text-muted mb-0 ps-3">
                            <li class="mb-1"><strong>Usuarios:</strong> Cada usuario puede ver las Cajas asignadas a él, Gastos, Facturas y cheques que cree, no puede ver los de otros.</li>
                            <li class="mb-1"><strong>Creación de Usuarios:</strong> Cada usuario está asociado directamente a un Rol que condiciona sus permisos de acceso a las distintas funcionalidades en el sistema.</li>
                            <li class="mb-1"><strong>Mantener Contraseña Actual:</strong> Al presionar <span class="badge bg-outline-primary text-dark border">Editar</span> un usuario existente, deje el campo de contraseña <u>en blanco</u> si no requiere cambiarla. El sistema mantendrá la clave encriptada guardada previamente.</li>
                            <li><strong>Modificación de Contraseña:</strong> Para restablecer las credenciales, ingrese la nueva contraseña en el campo correspondiente (mínimo 6 caracteres). El cambio surtirá efecto en el siguiente inicio de sesión.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- MODAL PARA NUEVO / VER / EDITAR USUARIO -->
<div class="modal fade" id="modalUsuario" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalUsuarioLabel"><i class="bi bi-person-plus me-2"></i> Registrar Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUsuario">
                <div class="modal-body p-4">
                    <div id="alertaModal" class="alert alert-danger d-none"></div>
                    <input type="hidden" name="id" id="usuario_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nombre de Usuario</label>
                        <input type="text" name="user" id="input_user" class="form-control" placeholder="Ej: jgomez" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="pass" id="input_pass" class="form-control" placeholder="Mínimo 6 caracteres" required>
                            <button class="btn btn-outline-secondary" type="button" id="btnTogglePassword" onclick="toggleMostrarContrasena()">
                                <i class="bi bi-eye-fill" id="iconoPassword"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted d-none" id="ayudaPassword">Dejar en blanco si no desea modificar la contraseña actual.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Asignar Rol</label>
                        <select name="rol_id" id="select_rol" class="form-select" required>
                            <option value="" selected disabled>Seleccione un rol...</option>
                            <?php foreach ($rolesSelect as $rol): ?>
                                <option value="<?= $rol['id'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark px-4" id="btnGuardarUsuario">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
let modalUsuarioBS;

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

    modalUsuarioBS = new bootstrap.Modal(document.getElementById('modalUsuario'));

    $('#tablaUsuarios').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
    });

    // Convierte el nombre de usuario a minúsculas automáticamente
    $(document).on('input', '#input_user', function(){
        this.value = this.value.toLowerCase();
    });

    $('#formUsuario').on('submit', function(e) {
        e.preventDefault();
        
        let urlDestino = $('#usuario_id').val() ? 'editar_usuario.php' : 'procesar_usuario.php';
        
        $.ajax({
            url: urlDestino,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    $('#alertaModal').removeClass('d-none').text(response.message);
                }
            },
            error: function() {
                $('#alertaModal').removeClass('d-none').text('Error interno del servidor al procesar la solicitud.');
            }
        });
    });
});

function abrirModalUsuario(modo, id = null, usuario = '', rol_id = null) {
    $('#alertaModal').addClass('d-none').text('');
    $('#formUsuario')[0].reset();
    
    $('#input_pass').attr('type', 'password');
    $('#iconoPassword').removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');

    $('#formUsuario').find('input, select').prop('disabled', false);
    $('#btnTogglePassword').prop('disabled', false);
    $('#btnGuardarUsuario').show();
    $('#ayudaPassword').addClass('d-none');
    $('#input_pass').prop('required', true);

    if (modo === 'NUEVO') {
        $('#modalUsuarioLabel').html('<i class="bi bi-person-plus me-2"></i> Registrar Usuario');
        $('#usuario_id').val('');
        modalUsuarioBS.show();
    } 
    else if (modo === 'VER') {
        $('#modalUsuarioLabel').html('<i class="bi bi-eye me-2"></i> Datos del Usuario');
        $('#usuario_id').val(id);
        $('#input_user').val(usuario);
        $('#select_rol').val(rol_id);
        $('#input_pass').val('********').prop('required', false);
        
        $('#formUsuario').find('input, select').prop('disabled', true);
        $('#btnTogglePassword').prop('disabled', true);
        $('#btnGuardarUsuario').hide();
        modalUsuarioBS.show();
    } 
    else if (modo === 'EDITAR') {
        $('#modalUsuarioLabel').html('<i class="bi bi-pencil me-2"></i> Editar Usuario');
        $('#usuario_id').val(id);
        $('#input_user').val(usuario);
        $('#select_rol').val(rol_id);
        
        $('#input_pass').prop('required', false);
        $('#ayudaPassword').removeClass('d-none');
        modalUsuarioBS.show();
    }
}

function toggleMostrarContrasena() {
    let inputPass = $('#input_pass');
    let icono = $('#iconoPassword');
    
    if (inputPass.attr('type') === 'password') {
        inputPass.attr('type', 'text');
        icono.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
    } else {
        inputPass.attr('type', 'password');
        icono.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
    }
}

function eliminarUsuario(id) {
    Swal.fire({
        title: '¿Confirmación requerida?',
        text: 'Para eliminar este usuario y revocar sus accesos permanentemente, escribe la palabra "ELIMINAR" a continuación:',
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
                url: 'eliminar_usuario.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El usuario ha sido borrado correctamente.',
                            icon: 'success',
                            confirmButtonColor: '#212529'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
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