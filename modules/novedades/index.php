<?php
require_once '../../config/database.php';

// Limpiamos la memoria de menús abiertos antes de renderizar el sidebar
echo "<script>localStorage.setItem('menuOperaciones', 'closed'); localStorage.setItem('menuConfig', 'closed');</script>";

$jsonPath = '../../config/changelog.json';
$novedades = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content p-4 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0"><i class="bi bi-stars text-warning me-2"></i>Novedades del Sistema</h4>
    </div>

    <?php if (empty($novedades)): ?>
        <div class="alert alert-light border">No hay novedades registradas por el momento.</div>
    <?php else: ?>
        <div class="row g-3">
           <?php foreach ($novedades as $item): ?>
            <?php if (isset($item['_instrucciones'])) continue; // Saltea las instrucciones ?>
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                            <span class="fw-bold"><i class="bi bi-patch-check me-2 text-info"></i><?= htmlspecialchars($item['version']) ?> - <?= htmlspecialchars($item['titulo']) ?></span>
                            <small class="text-white-50"><?= htmlspecialchars($item['fecha']) ?></small>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($item['cambios'] as $cambio): ?>
                                    <li class="mb-1 text-secondary"><?= htmlspecialchars($cambio) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>