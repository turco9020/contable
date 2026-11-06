<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php'; // valida la sesión antes de procesar cualquier POST
require_once __DIR__ . '/includes/condiciones_html.php';

$db_conn = $conn ?? null;

// Usuario que realiza los cambios (igual que en Materiales)
$user_id = $_SESSION['id'] ?? 0;
$usuario_actual = 'Sistema';
if ($db_conn && $user_id > 0) {
    $stmt_u = $db_conn->prepare("SELECT usuario FROM usuarios WHERE id = ?");
    if ($stmt_u) {
        $stmt_u->bind_param("i", $user_id);
        $stmt_u->execute();
        if ($row_u = $stmt_u->get_result()->fetch_assoc()) {
            $usuario_actual = $row_u['usuario'];
        }
    }
}

$tabla_ok = false;
if ($db_conn) {
    $chk = $db_conn->query("SHOW TABLES LIKE 'presupuesto_condiciones_generales'");
    $tabla_ok = ($chk && $chk->num_rows > 0);
}

// =====================================================================
// ACCIONES (POST)
// =====================================================================
if ($tabla_ok && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // GUARDAR (nueva o edición)
    if ($_POST['action'] === 'guardar_condicion') {
        $id          = intval($_POST['id'] ?? 0);
        $nombre      = trim($_POST['nombre'] ?? '');
        $contenido   = limpiarCondicionesHtml($_POST['contenido'] ?? ''); // solo deja negrita, cursiva, subrayado y listas
        $por_defecto = isset($_POST['por_defecto']) ? 1 : 0;

        if ($nombre === '' || $contenido === '') {
            header('Location: condiciones.php?msg=incompleto');
            exit;
        }

        // Solo una base puede ser la de por defecto
        if ($por_defecto) {
            $db_conn->query("UPDATE presupuesto_condiciones_generales SET por_defecto = 0");
        }

        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_condiciones_generales
                                       SET nombre = ?, contenido = ?, por_defecto = ?, usuario_nombre = ?
                                       WHERE id = ?");
            $stmt->bind_param("ssisi", $nombre, $contenido, $por_defecto, $usuario_actual, $id);
        } else {
            $stmt = $db_conn->prepare("INSERT INTO presupuesto_condiciones_generales (nombre, contenido, por_defecto, usuario_nombre)
                                       VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $nombre, $contenido, $por_defecto, $usuario_actual);
        }
        $stmt->execute();
        header('Location: condiciones.php?msg=guardado');
        exit;
    }

    // MARCAR POR DEFECTO
    if ($_POST['action'] === 'marcar_defecto') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $db_conn->query("UPDATE presupuesto_condiciones_generales SET por_defecto = 0");
            $stmt = $db_conn->prepare("UPDATE presupuesto_condiciones_generales SET por_defecto = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        header('Location: condiciones.php?msg=defecto');
        exit;
    }

    // ELIMINAR (baja lógica)
    if ($_POST['action'] === 'eliminar_condicion') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_condiciones_generales SET activo = 0, por_defecto = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        header('Location: condiciones.php?msg=eliminado');
        exit;
    }
}

// Cierre preventivo de otros submenús en el sidebar
echo "<script>localStorage.setItem('menuOperaciones', 'closed'); localStorage.setItem('menuConfig', 'closed');</script>";

include '../../includes/header.php';
include '../../includes/sidebar.php';

// Bases activas: primero la de por defecto, después por nombre
$bases = [];
if ($tabla_ok) {
    $res = $db_conn->query("SELECT id, nombre, contenido, por_defecto, usuario_nombre,
                                   DATE_FORMAT(fecha_actualizacion, '%d/%m/%Y') AS fecha_act
                            FROM presupuesto_condiciones_generales
                            WHERE activo = 1
                            ORDER BY por_defecto DESC, nombre ASC");
    if ($res) {
        $bases = $res->fetch_all(MYSQLI_ASSOC);
    }
}

$mensajes = [
    'guardado'   => ['success', 'Base guardada correctamente.'],
    'defecto'    => ['success', 'Base marcada como predeterminada.'],
    'eliminado'  => ['success', 'Base eliminada.'],
    'incompleto' => ['warning', 'El nombre y el texto de la base son obligatorios.'],
];
$msg = $mensajes[$_GET['msg'] ?? ''] ?? null;
?>

<div class="content p-4 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-file-text text-secondary me-2"></i> Condiciones Generales
            </h4>
            <p class="text-muted small mb-0">Bases de texto para la pestaña Condiciones de cada presupuesto. Al aplicarlas en un presupuesto se copian y se pueden modificar sin cambiar la base.</p>
        </div>
        <?php if ($tabla_ok): ?>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModalNueva()">
            <i class="bi bi-plus-circle me-2"></i> Nueva Base
        </button>
        <?php endif; ?>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg[0] ?> alert-dismissible fade show py-2" role="alert">
            <?= htmlspecialchars($msg[1]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!$tabla_ok): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Falta crear la tabla <code>presupuesto_condiciones_generales</code> en la base de datos.
        </div>
    <?php else: ?>
    <div class="card p-3 shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0 w-100">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 240px;">Nombre</th>
                        <th>Texto</th>
                        <th class="text-center" style="width: 130px;">Actualizada</th>
                        <th class="text-center" style="width: 170px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bases)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No hay bases de condiciones cargadas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bases as $b): ?>
                        <?php $resumen = condicionesResumen($b['contenido']); // sin formato, solo el comienzo del texto ?>
                        <tr>
                            <td class="fw-semibold">
                                <?= htmlspecialchars($b['nombre']) ?>
                                <?php if ($b['por_defecto']): ?>
                                    <span class="badge bg-success ms-1">Por defecto</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($resumen) ?></td>
                            <td class="text-center small">
                                <?= htmlspecialchars($b['fecha_act'] ?? '-') ?>
                                <?php if (!empty($b['usuario_nombre'])): ?>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($b['usuario_nombre']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <?php if (!$b['por_defecto']): ?>
                                    <button class="btn btn-sm btn-outline-success" title="Marcar por defecto" onclick="marcarDefecto(<?= (int)$b['id'] ?>)">
                                        <i class="bi bi-star"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary" title="Editar" onclick='editarBase(<?= json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick='eliminarBase(<?= (int)$b['id'] ?>, <?= json_encode($b['nombre'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL CREAR / EDITAR BASE -->
<div class="modal fade" id="modalCondicion" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="formCondicion">
                <input type="hidden" name="action" value="guardar_condicion">
                <input type="hidden" name="id" id="cond_id" value="0">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalCondTitulo"><i class="bi bi-file-text me-2"></i> Nueva Base</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nombre de la base</label>
                        <input type="text" name="nombre" id="cond_nombre" class="form-control form-control-sm" maxlength="150" required placeholder="Ej: Obra privada / Licitación pública">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Texto de las condiciones</label>
                        <input type="hidden" name="contenido" id="cond_contenido">
                        <div id="cond_editor" style="min-height: 320px;"></div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="por_defecto" id="cond_defecto" value="1">
                        <label class="form-check-label small" for="cond_defecto">Usar por defecto en los presupuestos nuevos</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark px-4"><i class="bi bi-save me-1"></i> Guardar Base</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FORMULARIOS OCULTOS -->
<form id="formMarcarDefecto" method="POST" style="display: none;">
    <input type="hidden" name="action" value="marcar_defecto">
    <input type="hidden" name="id" id="defecto_id" value="0">
</form>
<form id="formEliminarCondicion" method="POST" style="display: none;">
    <input type="hidden" name="action" value="eliminar_condicion">
    <input type="hidden" name="id" id="eliminar_id" value="0">
</form>

<?php include '../../includes/footer.php'; ?>

<!-- Editor de texto con formato -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="/contable/modules/presupuestos/assets/js/condiciones_editor.js"></script>

<style>
#cond_editor .ql-editor { min-height: 320px; font-size: 0.95rem; }
</style>

<script>
let modalCondicion;
let editorCondicion;

document.addEventListener('DOMContentLoaded', function() {
    modalCondicion = new bootstrap.Modal(document.getElementById('modalCondicion'));
    editorCondicion = crearEditorCondiciones('#cond_editor');

    // Al guardar, el HTML del editor pasa al campo oculto
    document.getElementById('formCondicion').addEventListener('submit', function(e) {
        if (condicionesEditorVacio(editorCondicion)) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire('Falta el texto', 'Escribí el texto de las condiciones antes de guardar.', 'warning');
            }
            return;
        }
        document.getElementById('cond_contenido').value = getCondicionesEditor(editorCondicion);
    });
});

function abrirModalNueva() {
    document.getElementById('formCondicion').reset();
    setCondicionesEditor(editorCondicion, '');
    document.getElementById('cond_id').value = 0;
    document.getElementById('modalCondTitulo').innerHTML = '<i class="bi bi-file-text me-2"></i> Nueva Base';
    modalCondicion.show();
}

function editarBase(b) {
    document.getElementById('cond_id').value = b.id;
    document.getElementById('cond_nombre').value = b.nombre;
    setCondicionesEditor(editorCondicion, b.contenido);
    document.getElementById('cond_defecto').checked = String(b.por_defecto) === '1';
    document.getElementById('modalCondTitulo').innerHTML = '<i class="bi bi-pencil me-2"></i> Editar Base';
    modalCondicion.show();
}

function marcarDefecto(id) {
    document.getElementById('defecto_id').value = id;
    document.getElementById('formMarcarDefecto').submit();
}

function eliminarBase(id, nombre) {
    const enviar = function() {
        document.getElementById('eliminar_id').value = id;
        document.getElementById('formEliminarCondicion').submit();
    };
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar base?',
            text: '¿Estás seguro de eliminar la base "' + nombre + '"? Los presupuestos que ya la usaron no cambian.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) enviar();
        });
    } else if (confirm('¿Estás seguro de eliminar la base "' + nombre + '"?')) {
        enviar();
    }
}
</script>
