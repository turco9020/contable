<?php
require_once '../../config/database.php';

// Limpiamos la memoria de menús abiertos antes de renderizar el sidebar
echo "<script>localStorage.setItem('menuOperaciones', 'closed'); localStorage.setItem('menuConfig', 'closed');</script>";

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content p-4 flex-grow-1">
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
            <i class="bi bi-calculator text-primary display-4 mb-3"></i>
            <h4 class="fw-bold text-dark">Módulo de Presupuestos</h4>
            <p class="text-muted">Este módulo se encuentra actualmente en desarrollo y estará disponible próximamente.</p>
            <a href="/contable/index.php" class="btn btn-outline-dark mt-2">
                <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>