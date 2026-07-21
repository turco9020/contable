<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="content">

    <!-- Mensaje de Alerta General para Accesos Denegados -->
    <?php if (isset($_GET['status']) && $_GET['status'] === 'no_autorizado'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> 
            <strong>Acceso denegado:</strong> No tienes los permisos necesarios para ingresar a esa sección.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (tieneRol('gerente')): ?>
        <!-- =======================================================================
             VISTA GERENCIAL (Estructura lista para expandir en el futuro)
             ======================================================================= -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark mb-0">📊 Tablero de Control Gerencial</h4>
            <span class="badge bg-primary px-3 py-2">Perfil: Gerente</span>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-top border-primary border-4">
                    <div class="card-body d-flex flex-column justify-content-center py-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-graph-up-arrow fs-4 text-primary me-2"></i>
                            <small class="text-muted fw-semibold">RENTABILIDAD NETA</small>
                        </div>
                        <h3 class="fw-bold mb-0 text-muted">Próximamente...</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-top border-info border-4">
                    <div class="card-body d-flex flex-column justify-content-center py-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-pie-chart-fill fs-4 text-info me-2"></i>
                            <small class="text-muted fw-semibold">PUNTOS DE EQUILIBRIO</small>
                        </div>
                        <h3 class="fw-bold mb-0 text-muted">Próximamente...</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4 border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-sliders2-vertical fs-1 text-secondary mb-3"></i>
                <h5 class="fw-bold text-dark">Métricas Estratégicas en Desarrollo</h5>
                <p class="text-muted max-w-md mx-auto">Este espacio está reservado para los reportes consolidados de auditoría, gráficos anuales y análisis de flujo de caja gerencial.</p>
            </div>
        </div>

    <?php else: ?>
        <!-- =======================================================================
             VISTA OPERATIVA / CONTADOR (Tu código original intacto)
             ======================================================================= -->
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-top border-success border-4">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-cash-stack fs-4 text-success me-2"></i>
                            <small class="text-muted fw-semibold">DINERO DISPONIBLE</small>
                        </div>
                        <h3 id="saldoDisponible" class="fw-bold mb-0">Cargando...</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-top border-danger border-4">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar-day fs-4 text-danger me-2"></i>
                            <small class="text-muted fw-semibold">GASTOS HOY</small>
                        </div>
                        <h3 id="gastosHoy" class="fw-bold mb-0">Cargando...</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-top border-warning border-4">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar-month fs-4 text-warning me-2"></i>
                            <small class="text-muted fw-semibold">GASTOS DEL MES</small>
                        </div>
                        <h3 id="gastosMes" class="fw-bold mb-0">Cargando...</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-top border-primary border-4">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-tags fs-4 text-primary me-2"></i>
                            <small class="text-muted fw-semibold">CATEGORÍA PRINCIPAL</small>
                        </div>
                        <div id="categoriaTopNombre" class="fw-bold fs-5">Cargando...</div>
                        <div class="mt-1">
                            <span id="categoriaTopTotal" class="fw-bold text-danger fs-5">Cargando...</span>
                            <br>
                            <small id="categoriaTopPorcentaje" class="text-muted">...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <h5 class="mb-3">Estado de las cajas</h5>
        <div class="row" id="cardsCajas"></div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <strong>💸 Gastos por Centro de Costo (Mes Actual)</strong>
                    </div>
                    <div class="card-body">
                        <div id="centrosCostosDashboard">
                            <div class="text-center text-muted">Cargando...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>    

        <hr>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">Cheques a Vencer</div>
                    <div class="card-body">
                        <table class="table table-sm" id="tablaUltimosGastos">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">Agenda Vencimientos</div>
                    <div class="card-body">
                        <table class="table table-sm" id="tablaUltimosMovimientos">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Importe</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

<!-- Carga condicional del script según el rol -->
<?php if (tieneRol('gerente')): ?>
    <script src="/contable/assets/js/dashboard_gerente.js"></script>
<?php else: ?>
    <script src="/contable/assets/js/dashboard.js"></script>
<?php endif; ?>