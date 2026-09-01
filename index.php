<?php
include 'includes/header.php';
include 'includes/sidebar.php';

// Consulta de Tareas Pendientes (incluye creador, asignado y la fecha del último comentario)
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? 0;
$sql_tareas = "SELECT t.*, 
                      u_creador.usuario AS creador_nombre,
                      u_asignado.usuario AS asignado_nombre,
                      (SELECT MAX(fecha_creacion) FROM tarea_comentarios WHERE tarea_id = t.id) AS ultimo_comentario_fecha
               FROM tareas t 
               LEFT JOIN usuarios u_creador ON t.creador_id = u_creador.id 
               LEFT JOIN usuarios u_asignado ON t.asignado_id = u_asignado.id 
               WHERE (t.asignado_id = $usuario_id OR t.creador_id = $usuario_id) 
                 AND t.estado != 'COMPLETADO' 
               ORDER BY t.prioridad DESC, t.fecha_limite ASC 
               LIMIT 12"; 
$res_tareas = mysqli_query($conn, $sql_tareas);

// Determinar etiqueta del perfil para el encabezado superior
$rol_actual = $_SESSION['rol'] ?? 'Usuario';
?>

<div class="content">

    <!-- Mensaje de Alerta General -->
    <?php if (isset($_GET['status']) && $_GET['status'] === 'no_autorizado'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> 
            <strong>Acceso denegado:</strong> No tienes los permisos necesarios para ingresar a esa sección.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

<!-- SECCIÓN TRANSVERSAL: MIS TAREAS PENDIENTES -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-kanban me-2"></i>Mis Tareas Pendientes</h5>
    <a href="/contable/modules/tareas/" class="btn btn-outline-dark btn-sm">Ver Tablero Completo</a>
</div>

<div class="row g-2 mb-4">
    <?php if ($res_tareas && mysqli_num_rows($res_tareas) > 0): ?>
        <?php while ($t = mysqli_fetch_assoc($res_tareas)): 
                $es_nueva = false;

                $ultima_vista = !empty($t['ultima_vista_en']) ? strtotime($t['ultima_vista_en']) : 0;
                $ultimo_comentario = !empty($t['ultimo_comentario_fecha']) ? strtotime($t['ultimo_comentario_fecha']) : 0;

                // 1. Si nunca la abrió, es NUEVA
                if (empty($t['ultima_vista_en'])) {
                    $es_nueva = true;
                } 
                // 2. Si hay un comentario posterior a su última lectura, es NUEVO
                elseif ($ultimo_comentario > $ultima_vista) {
                    $es_nueva = true;
                }
                
                $clase_borde = $es_nueva ? 'border-start border-4 border-primary shadow' : 'border-0 shadow-sm';
            ?>
            <div class="col-12 col-md-4 col-xl-2" id="tarea-card-<?= $t['id'] ?>">
                <div class="card h-100 <?= $clase_borde ?> p-2 cursor-pointer" 
                     onclick="verTareaDashboard(<?= $t['id'] ?>)"
                     style="background-color: #ffffff; cursor: pointer; transition: transform 0.15s;"
                     onmouseover="this.style.transform='scale(1.02)'" 
                     onmouseout="this.style.transform='scale(1)'">
                    
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <?php if ($es_nueva): ?>
                            <span class="badge bg-primary text-white badge-nueva" style="font-size: 8px;">
                                <i class="bi bi-bell-fill me-1"></i>NUEVA
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border" style="font-size: 9px;"><?= htmlspecialchars($t['estado']) ?></span>
                        <?php endif; ?>
                        
                        <span class="badge rounded-pill bg-<?= $t['prioridad'] === 'ALTA' ? 'danger' : ($t['prioridad'] === 'MEDIA' ? 'warning' : 'success') ?> ms-auto" style="font-size: 8px;">
                            <?= htmlspecialchars($t['prioridad']) ?>
                        </span>
                    </div>

                    <div class="fw-bold text-dark text-truncate small mb-1" title="<?= htmlspecialchars($t['titulo']) ?>">
                        <?= htmlspecialchars($t['titulo']) ?>
                    </div>

                    <!-- FECHA DE CREACIÓN Y LÍMITE -->
                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 9.5px;">
                        <span><i class="bi bi-clock me-1"></i><?= (!empty($t['creado_en']) && $t['creado_en'] !== '0000-00-00 00:00:00') ? date('d/m/y', strtotime($t['creado_en'])) : '-' ?></span>
                        <?php if (!empty($t['fecha_limite'])): ?>
                            <span class="<?= (strtotime($t['fecha_limite']) < strtotime('today')) ? 'text-danger fw-bold' : '' ?>">
                                <i class="bi bi-flag-fill me-1"></i><?= date('d/m/y', strtotime($t['fecha_limite'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- CREADOR Y ASIGNADO -->
                    <div class="text-truncate text-muted border-top pt-1" style="font-size: 9.5px;">
                        <?php 
                        $creador = htmlspecialchars($t['creador_nombre'] ?? 'Sistema');
                        $asignado = htmlspecialchars($t['asignado_nombre'] ?? 'Sin asignar');
                        
                        // Si el creador y el asignado son la misma persona
                        if (!empty($t['creador_id']) && $t['creador_id'] == $t['asignado_id']): 
                        ?>
                            <span class="fw-semibold text-secondary" title="Asignada a sí mismo (<?= $creador ?>)">
                                <i class="bi bi-person-fill me-1"></i><?= $creador ?>
                            </span>
                        <?php else: ?>
                            <span title="De <?= $creador ?> para <?= $asignado ?>">
                                <span class="fw-semibold text-secondary"><?= $creador ?></span>
                                <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                <span class="fw-semibold text-secondary"><?= $asignado ?></span>
                            </span>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-light text-muted border text-center mb-0 py-2 small">
                <i class="bi bi-check2-circle me-1"></i> No tienes tareas pendientes asignadas.
            </div>
        </div>
    <?php endif; ?>
</div>

    <!-- CARGA DINÁMICA DE DASHBOARD SEGÚN ROL -->
    <?php
    if (tieneRol('admin')) {
        include 'dashboards/dashboard_admin.php';
    } elseif (tieneRol('contador')) {
        include 'dashboards/dashboard_contador.php';
    } elseif (tieneRol('arquitecto')) {
        include 'dashboards/dashboard_arquitecto.php';
    } elseif (tieneRol('auditor')) {
        include 'dashboards/dashboard_auditor.php';
    } else {
        // Por defecto: Operador u otros roles
        include 'dashboards/dashboard_operador.php';
    }
    ?>

</div>

<?php include 'includes/footer.php'; ?>



<!-- Carga de JS condicional del Dashboard (SOLO AQUÍ) -->
<?php if (tieneRol('admin') || tieneRol('contador') || tieneRol('arquitecto') || tieneRol('auditor')): ?>
    <script src="/contable/assets/js/dashboard.js?v=<?= time() ?>"></script>
<?php endif; ?>