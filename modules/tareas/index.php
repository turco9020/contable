<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$conexion = $conn; 

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

$usuario_id  = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? 0;
$rol_usuario = strtoupper($_SESSION['rol'] ?? '');
$rol_id      = $_SESSION['rol_id'] ?? 0;
$es_admin    = ($rol_usuario === 'ADMIN' || $rol_usuario === 'ADMINISTRADOR' || $rol_id == 1);

$usuarios_lista = [];
$res_users = mysqli_query($conexion, "SELECT id, usuario FROM usuarios ORDER BY usuario ASC");
while ($u = mysqli_fetch_assoc($res_users)) {
    $usuarios_lista[] = $u;
}

// CONSULTA CON FECHA DE ÚLTIMO COMENTARIO PARA DETECTAR Novedades / Tareas Nuevas
$sql = "SELECT t.*, 
               u_creador.usuario AS creador_nombre, 
               u_asig.usuario AS asignado_nombre,
               (SELECT COUNT(*) FROM tarea_comentarios WHERE tarea_id = t.id) AS total_comentarios,
               (SELECT COUNT(*) FROM tarea_adjuntos WHERE tarea_id = t.id) AS total_adjuntos,
               (SELECT MAX(fecha_creacion) FROM tarea_comentarios WHERE tarea_id = t.id) AS ultimo_comentario_fecha
        FROM tareas t
        LEFT JOIN usuarios u_creador ON t.creador_id = u_creador.id
        LEFT JOIN usuarios u_asig ON t.asignado_id = u_asig.id";

if (!$es_admin) {
    $sql .= " WHERE t.creador_id = $usuario_id OR t.asignado_id = $usuario_id";
}

$sql .= " ORDER BY t.prioridad DESC, t.fecha_limite ASC";
$res = mysqli_query($conexion, $sql);

$tareas = [
    'PENDIENTE'  => [],
    'EN_PROCESO' => [],
    'REVISION'   => [],
    'COMPLETADO' => []
];

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        if (isset($tareas[$row['estado']])) {
            $tareas[$row['estado']][] = $row;
        }
    }
}
?>

<style>
/* Estética Minimalista Tablero Compacto */
.kanban-column-card {
    border: none !important;
    background-color: #f4f6f8 !important;
    border-radius: 8px !important;
}
.kanban-col {
    min-height: 500px;
    padding: 8px;
}
.task-card {
    border: 1px solid #e3e8ee !important;
    border-radius: 5px !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    background: #ffffff;
}
.task-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05) !important;
}

/* Reducción de fuentes y alturas compactas */
.badge-prio-ALTA { background-color: #ffeef0; color: #d92550; border: 1px solid #fecaca; }
.badge-prio-MEDIA { background-color: #fffbeb; color: #b45309; border: 1px solid #fef08a; }
.badge-prio-BAJA { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

.task-date-info {
    font-size: 11px;
    color: #6c757d;
}

/* Estilo para tarjetas con novedades/nuevas */
.task-card-nueva {
    background-color: #f0f7ff !important; /* Azul clarito pastel */
    border-left: 4px solid #0d6efd !important; /* Borde izquierdo azul destacado */
}

/* Custom Nav Tabs Minimal */
.nav-tabs-minimal .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}
.nav-tabs-minimal .nav-link.active {
    color: #212529;
    border-bottom: 2px solid #212529;
    background: transparent;
}
</style>

<div class="content p-4">
    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-kanban me-2"></i>Gestión de Tareas</h4>
            <small class="text-muted">Organización y seguimiento de pendientes</small>
        </div>
        <button class="btn btn-dark px-4 shadow-sm" onclick="abrirModalCrear()">
            <i class="bi bi-plus-lg me-1"></i> Nueva Tarea
        </button>
    </div>

    <!-- FILTROS -->
    <div class="card mb-4 border-0 shadow-sm rounded-3">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="filtroTexto" class="form-control form-control-sm border-start-0" placeholder="Buscar tarea..." onkeyup="filtrarTablero()">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="filtroPrioridad" class="form-select form-select-sm" onchange="filtrarTablero()">
                        <option value="">Todas las prioridades</option>
                        <option value="ALTA">Alta</option>
                        <option value="MEDIA">Media</option>
                        <option value="BAJA">Baja</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="filtroAsignado" class="form-select form-select-sm" onchange="filtrarTablero()">
                        <option value="">Todos los asignados</option>
                        <?php foreach ($usuarios_lista as $u): ?>
                            <option value="<?= htmlspecialchars($u['usuario']) ?>"><?= htmlspecialchars($u['usuario']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLERO -->
    <div class="row g-3">
        <?php 
        $columnas = [
            'PENDIENTE'  => ['titulo' => 'Pendiente', 'dot' => 'text-secondary'],
            'EN_PROCESO' => ['titulo' => 'En Proceso', 'dot' => 'text-primary'],
            'REVISION'   => ['titulo' => 'En Revisión', 'dot' => 'text-warning'],
            'COMPLETADO' => ['titulo' => 'Completado', 'dot' => 'text-success']
        ];
        foreach ($columnas as $estado => $col): 
        ?>
            <div class="col-md-3">
                <div class="card kanban-column-card">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3 pb-0">
                        <span class="fw-bold small text-uppercase text-secondary">
                            <i class="bi bi-circle-fill me-1 <?= $col['dot'] ?>" style="font-size: 8px;"></i>
                            <?= $col['titulo'] ?>
                        </span>
                        <span class="badge bg-white text-dark border rounded-circle shadow-sm" id="count-<?= $estado ?>"><?= count($tareas[$estado]) ?></span>
                    </div>
                    <div class="card-body kanban-col" id="<?= $estado ?>">
                        <?php foreach ($tareas[$estado] as $t): 
                            // Lógica de Tarea Nueva / Comentarios sin leer
                            $es_nueva = false;
                            $ultima_vista = !empty($t['ultima_vista_en']) ? strtotime($t['ultima_vista_en']) : 0;
                            $ultimo_comentario = !empty($t['ultimo_comentario_fecha']) ? strtotime($t['ultimo_comentario_fecha']) : 0;

                            if (empty($t['ultima_vista_en'])) {
                                $es_nueva = true;
                            } elseif ($ultimo_comentario > $ultima_vista) {
                                $es_nueva = true;
                            }

                            // Si es nueva le ponemos la clase del azul clarito, de lo contrario el estilo normal
                            $clase_tarjeta = $es_nueva ? 'task-card-nueva shadow-sm' : 'border-0 shadow-sm';
                        ?>
                            <div class="card mb-2 task-card <?= $clase_tarjeta ?> cursor-pointer" 
                                id="tarea-<?= $t['id'] ?>" 
                                data-id="<?= $t['id'] ?>"
                                data-titulo="<?= strtolower(htmlspecialchars($t['titulo'])) ?>"
                                data-descripcion="<?= strtolower(htmlspecialchars($t['descripcion'])) ?>"
                                data-prioridad="<?= $t['prioridad'] ?>"
                                data-asignado="<?= htmlspecialchars($t['asignado_nombre'] ?? '') ?>"
                                onclick="abrirModalDetalles(<?= $t['id'] ?>)">
                                
                                    <div class="card-body p-2 px-2.5">
                                        <!-- BADGE NUEVA + TÍTULO Y BADGE PRIO -->
                                        <?php if ($es_nueva): ?>
                                            <div class="mb-1">
                                                <span class="badge bg-primary text-white badge-nueva" style="font-size: 8px; padding: 2px 5px;">
                                                    <i class="bi bi-bell-fill me-1"></i>NUEVA
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-dark small text-truncate me-1" style="max-width: 160px; font-size: 12px;" title="<?= htmlspecialchars($t['titulo']) ?>">
                                                <?= htmlspecialchars($t['titulo']) ?>
                                            </span>
                                            <span class="badge rounded-pill badge-prio-<?= $t['prioridad'] ?>" style="font-size: 8.5px; padding: 2px 5px;">
                                                <?= $t['prioridad'] ?>
                                            </span>
                                        </div>

                                        <!-- FECHAS -->
                                        <div class="d-flex justify-content-between align-items-center task-date-info mb-1 pb-1 border-bottom border-light">
                                            <span title="Fecha de creación">
                                                <i class="bi bi-clock me-1 text-muted"></i><?= (!empty($t['creado_en']) && $t['creado_en'] !== '0000-00-00 00:00:00') ? date('d/m/y', strtotime($t['creado_en'])) : '-' ?>
                                            </span>
                                            <?php if (!empty($t['fecha_limite'])): ?>
                                                <span title="Fecha límite" class="fw-semibold <?= (strtotime($t['fecha_limite']) < strtotime('today')) ? 'text-danger' : 'text-dark' ?>">
                                                    <i class="bi bi-flag-fill me-1 <?= (strtotime($t['fecha_limite']) < strtotime('today')) ? 'text-danger' : 'text-primary' ?>"></i><?= date('d/m/y', strtotime($t['fecha_limite'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- ASIGNADO Y CONTEXTO -->
                                        <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 9.5px; line-height: 1;">
                                            <span class="text-truncate" style="max-width: 120px;">
                                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($t['asignado_nombre'] ?? 'Sin asignar') ?>
                                            </span>
                                            <div class="d-flex gap-2">
                                                <?php if ($t['total_adjuntos'] > 0): ?>
                                                    <span><i class="bi bi-paperclip me-1"></i><?= $t['total_adjuntos'] ?></span>
                                                <?php endif; ?>
                                                <?php if ($t['total_comentarios'] > 0): ?>
                                                    <span><i class="bi bi-chat-left me-1"></i><?= $t['total_comentarios'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL UNIFICADO -->
<div class="modal fade" id="modalTarea" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">

            <!-- HEADER OSCURO -->
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModal"><i class="bi bi-kanban me-2"></i>Gestión de Tarea</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <ul class="nav nav-tabs nav-tabs-minimal mb-4" id="tareaTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="tab-detalles-tab" data-bs-toggle="tab" data-bs-target="#tab-detalles" type="button">
                            <i class="bi bi-sliders me-1"></i> 1. Datos Principales
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-adjuntos-tab" data-bs-toggle="tab" data-bs-target="#tab-adjuntos" type="button">
                            <i class="bi bi-paperclip me-1"></i> 2. Archivos Adjuntos
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-comentarios-tab" data-bs-toggle="tab" data-bs-target="#tab-comentarios" type="button">
                            <i class="bi bi-chat-left-text me-1"></i> 3. Historial & Comentarios
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- DATOS PRINCIPALES -->
                    <div class="tab-pane fade show active" id="tab-detalles">
                        <form action="acciones.php?accion=guardar" method="POST" id="formTarea">
                            <input type="hidden" name="tarea_id" id="modal_tarea_id" value="0">

                            <div class="row g-3 mb-3">
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Título de la Tarea</label>
                                    <input type="text" name="titulo" id="modal_titulo" class="form-control" placeholder="Ej: Revisión de facturas pendientes" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Prioridad</label>
                                    <select name="prioridad" id="modal_prioridad" class="form-select">
                                        <option value="BAJA">Baja</option>
                                        <option value="MEDIA" selected>Media</option>
                                        <option value="ALTA">Alta</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Fecha Límite</label>
                                    <input type="date" name="fecha_limite" id="modal_fecha_limite" class="form-control">
                                </div>
                                
                                <!-- TODOS LOS USUARIOS PUEDEN ASIGNAR TAREAS ENTRE SÍ -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Asignar a</label>
                                    <select name="asignado_id" id="modal_asignado_id" class="form-select">
                                        <?php foreach ($usuarios_lista as $u): ?>
                                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['usuario']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold">Descripción / Detalle</label>
                                    <textarea name="descripcion" id="modal_descripcion" class="form-control" rows="4" placeholder="Detalles de la tarea..."></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-danger btn-sm px-3" id="btnEliminarTarea" style="display:none;" onclick="eliminarTareaActual()">
                                    <i class="bi bi-trash me-1"></i> Eliminar Tarea
                                </button>
                                <div class="ms-auto d-flex gap-2">
                                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-dark px-5">Guardar Registro</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- ARCHIVOS ADJUNTOS -->
                    <div class="tab-pane fade" id="tab-adjuntos">
                        <form action="acciones.php?accion=subir_adjunto" method="POST" enctype="multipart/form-data" class="mb-4">
                            <input type="hidden" name="tarea_id" class="modal_tarea_id_hidden">
                            <label class="form-label small fw-bold">Subir Nuevo Archivo</label>
                            <div class="input-group">
                                <input type="file" name="archivo" class="form-control" required>
                                <button class="btn btn-dark px-4" type="submit"><i class="bi bi-upload me-1"></i> Subir</button>
                            </div>
                        </form>
                        <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-3">Archivos Guardados</h6>
                        <div id="listaAdjuntos" class="list-group list-group-flush"></div>
                    </div>

                    <!-- HISTORIAL Y COMENTARIOS -->
                    <div class="tab-pane fade" id="tab-comentarios">
                        <div id="listaComentarios" class="mb-4 p-2 bg-light rounded-3 border" style="max-height: 280px; overflow-y: auto;"></div>
                        
                        <form id="formComentarioAjax">
                            <label class="form-label small fw-bold">Agregar Comentario</label>
                            <div class="input-group">
                                <input type="text" id="inputNuevoComentario" class="form-control" placeholder="Escribe una nota o avance..." required autocomplete="off">
                                <button class="btn btn-dark px-4" type="submit"><i class="bi bi-send me-1"></i> Comentar</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
const currentUserId = <?= json_encode($usuario_id) ?>;
const esAdminUser = <?= json_encode($es_admin) ?>;
let isDragging = false; 

$(document).ready(function() {
    
    // 1. SORTABLE DRAG AND DROP
    if ($.fn && $.fn.sortable) {
        $(".kanban-col").sortable({
            connectWith: ".kanban-col",
            items: ".task-card",
            cursor: "grab",
            placeholder: "card mb-2 bg-light border-dashed",
            forcePlaceholderSize: true,
            opacity: 0.8,
            start: function(event, ui) {
                isDragging = true;
                ui.placeholder.css({
                    'height': ui.item.outerHeight() + 'px',
                    'border': '2px dashed #cbd5e1',
                    'border-radius': '6px'
                });
            },
            stop: function(event, ui) {
                setTimeout(function() {
                    isDragging = false;
                }, 100);
            },
            receive: function(event, ui) {
                let tareaId = ui.item.data("id");
                let nuevoEstado = $(this).attr("id");

                $.ajax({
                    url: 'ajax_cambiar_estado.php',
                    type: 'POST',
                    data: { tarea_id: tareaId, nuevo_estado: nuevoEstado },
                    dataType: 'json',
                    success: function(res) {
                        if (!res.success) {
                            alert(res.msg || 'Error al actualizar el estado.');
                            location.reload();
                        } else {
                            actualizarContadoresColumnas();
                        }
                    },
                    error: function() {
                        alert('Error de conexión con el servidor al mover la tarea.');
                        location.reload();
                    }
                });
            }
        }).disableSelection();
    }

    // 2. PROCESAMIENTO DE COMENTARIOS VÍA AJAX
    $(document).on('submit', '#formComentarioAjax', function(e) {
        e.preventDefault();
        
        let tareaId = $('#modal_tarea_id').val() || $('.modal_tarea_id_hidden').val();
        let texto   = $('#inputNuevoComentario').val().trim();

        if (!texto || parseInt(tareaId) <= 0) return;

        $.ajax({
            url: 'acciones.php?accion=comentar_ajax',
            type: 'POST',
            data: { tarea_id: tareaId, comentario: texto },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#inputNuevoComentario').val('');
                    recargarComentariosYAdjuntos(tareaId);
                } else {
                    alert(res.msg || 'Error al guardar el comentario.');
                }
            }
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const openId = urlParams.get('open_id');
    if (openId) { abrirModalDetalles(openId); }
});

function actualizarContadoresColumnas() {
    $('.kanban-col').each(function() {
        let estado = $(this).attr('id');
        let count = $(this).find('.task-card:visible').length;
        $('#count-' + estado).text(count);
    });
}

function abrirModalDetalles(id) {
    if (isDragging) return;

    let $card = $(`#tarea-${id}`);
    $card.find('.badge-nueva').remove();
    $card.removeClass('task-card-nueva border-start border-4 border-info').addClass('border-0 shadow-sm');

    $.getJSON('acciones.php?accion=obtener&id=' + id, function(data) {
        if (!data.success) {
            alert(data.msg || data.message || 'Error al obtener la tarea.');
            return;
        }
        let t = data.tarea;
        
        $('#tituloModal').html('<i class="bi bi-kanban me-2"></i>Editar Tarea #' + t.id);
        $('#modal_tarea_id').val(t.id);
        $('.modal_tarea_id_hidden').val(t.id);
        $('#modal_titulo').val(t.titulo);
        $('#modal_descripcion').val(t.descripcion);
        $('#modal_prioridad').val(t.prioridad);
        $('#modal_fecha_limite').val(t.fecha_limite);
        if ($('#modal_asignado_id').length) {
            $('#modal_asignado_id').val(t.asignado_id);
        }
        $('#btnEliminarTarea').show();

        $('#tab-adjuntos-tab, #tab-comentarios-tab').removeClass('disabled');

        renderAdjuntos(data.adjuntos || []);
        renderComentarios(data.comentarios || []);

        let modalEl = document.getElementById('modalTarea');
        let modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInstance.show();
    });
}

function abrirModalCrear() {
    $('#tituloModal').html('<i class="bi bi-kanban me-2"></i>Nueva Tarea');
    $('#modal_tarea_id').val(0);
    $('.modal_tarea_id_hidden').val(0);
    $('#modal_titulo').val('');
    $('#modal_descripcion').val('');
    $('#modal_prioridad').val('MEDIA');
    $('#modal_fecha_limite').val('');
    if ($('#modal_asignado_id').length) {
        $('#modal_asignado_id').val(currentUserId);
    }
    $('#btnEliminarTarea').hide();
    
    $('#tab-adjuntos-tab, #tab-comentarios-tab').addClass('disabled');
    $('#tab-detalles-tab').tab('show');
    
    let modalEl = document.getElementById('modalTarea');
    let modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modalInstance.show();
}

function recargarComentariosYAdjuntos(tareaId) {
    $.getJSON('ajax_obtener_tarea.php?id=' + tareaId, function(data) {
        if (data.success) {
            renderAdjuntos(data.adjuntos || []);
            renderComentarios(data.comentarios || []);
        }
    });
}

function renderAdjuntos(adjuntos) {
    let html = '';
    if (adjuntos && adjuntos.length > 0) {
        adjuntos.forEach(function(a) {
            html += `<div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 border-bottom">
                <a href="/contable/${a.ruta_archivo}" target="_blank" class="text-dark text-decoration-none small fw-semibold">
                    <i class="bi bi-file-earmark-arrow-down me-2 text-primary"></i>${escapeHtml(a.nombre_archivo)}
                </a>
                <a href="acciones.php?accion=eliminar_adjunto&id=${a.id}" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('¿Eliminar archivo?')">
                    <i class="bi bi-trash"></i>
                </a>
            </div>`;
        });
    } else {
        html = '<div class="text-muted small">No hay archivos adjuntos.</div>';
    }
    $('#listaAdjuntos').html(html);
}

function renderComentarios(comentarios) {
    let html = '';
    if (comentarios && comentarios.length > 0) {
        comentarios.forEach(function(c) {
            let puedeEditar = esAdminUser || parseInt(c.usuario_id) === parseInt(currentUserId);
            let acciones = puedeEditar ? `
                <div>
                    <button type="button" class="btn btn-sm text-secondary p-0 me-2" onclick="editarComentario(${c.id}, '${escapeHtml(c.comentario)}')"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm text-danger p-0" onclick="eliminarComentario(${c.id}, ${c.tarea_id})"><i class="bi bi-trash"></i></button>
                </div>
            ` : '';

            html += `<div class="p-2 mb-2 bg-white rounded border shadow-sm" id="comentario-box-${c.id}">
                <div class="d-flex justify-content-between align-items-center small mb-1">
                    <span class="fw-bold text-dark">${escapeHtml(c.usuario_nombre || 'Usuario')}</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted" style="font-size: 10px;">${c.fecha_creacion}</span>
                        ${acciones}
                    </div>
                </div>
                <div class="small text-secondary" id="comentario-texto-${c.id}">${escapeHtml(c.comentario)}</div>
            </div>`;
        });
    } else {
        html = '<div class="text-muted small">Sin comentarios.</div>';
    }
    $('#listaComentarios').html(html);
}

function editarComentario(comentarioId, textoActual) {
    let nuevoTexto = prompt("Editar comentario:", textoActual);
    if (nuevoTexto !== null && nuevoTexto.trim() !== "") {
        let tareaId = $('#modal_tarea_id').val() || $('.modal_tarea_id_hidden').val();
        $.ajax({
            url: 'acciones.php?accion=editar_comentario_ajax',
            type: 'POST',
            data: { comentario_id: comentarioId, comentario: nuevoTexto.trim() },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    recargarComentariosYAdjuntos(tareaId);
                } else {
                    alert(res.msg || 'No se pudo editar.');
                }
            }
        });
    }
}

function eliminarComentario(comentarioId, tareaId) {
    if (confirm("¿Estás seguro de borrar este comentario?")) {
        $.ajax({
            url: 'acciones.php?accion=eliminar_comentario_ajax',
            type: 'POST',
            data: { comentario_id: comentarioId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    recargarComentariosYAdjuntos(tareaId);
                } else {
                    alert(res.msg || 'No se pudo eliminar.');
                }
            }
        });
    }
}

function eliminarTareaActual() {
    let id = $('#modal_tarea_id').val();
    if (id > 0 && confirm('¿Estás seguro de que deseas borrar esta tarea?')) {
        window.location.href = 'acciones.php?accion=eliminar&id=' + id;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function filtrarTablero() {
    let txt = $('#filtroTexto').val().toLowerCase();
    let prio = $('#filtroPrioridad').val();
    let asig = $('#filtroAsignado').val().toLowerCase();

    $('.task-card').each(function() {
        let cardTxtTitle = ($(this).data('titulo') || '').toString().toLowerCase();
        let cardTxtDesc  = ($(this).data('descripcion') || '').toString().toLowerCase();
        let cardPrio     = $(this).data('prioridad') || '';
        let cardAsig     = ($(this).data('asignado') || '').toString().toLowerCase();

        let matchTexto = !txt || cardTxtTitle.includes(txt) || cardTxtDesc.includes(txt);
        let matchPrio  = !prio || cardPrio === prio;
        let matchAsig  = !asig || cardAsig === asig;

        $(this).toggle(matchTexto && matchPrio && matchAsig);
    });
}
</script>