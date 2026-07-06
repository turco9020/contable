<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="content">
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
                <div class="card-header">Últimos Gastos</div>
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
                <div class="card-header">Últimos Movimientos</div>
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
</div>

<?php include 'includes/footer.php'; ?>
<script src="/contable/assets/js/dashboard.js"></script>