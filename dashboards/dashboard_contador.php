
<!-- Indicadores de Caja y Gastos -->
<div class="row g-2 mb-3">
    <h6 class="fw-bold mb-0">Detalle de Gastos</h6>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-top border-success border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">DINERO DISPONIBLE</small>
                <h3 id="saldoDisponible" class="fw-bold mb-0 fs-4 text-success">Cargando...</h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-top border-danger border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">GASTOS DE HOY</small>
                <h3 id="gastosHoy" class="fw-bold mb-0 fs-4 text-danger">Cargando...</h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-top border-warning border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">GASTOS DEL MES</small>
                <h3 id="gastosMes" class="fw-bold mb-0 fs-4 text-dark">Cargando...</h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-top border-primary border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">MAYOR CATEGORÍA DEL MES</small>
                <div id="categoriaTopNombre" class="fw-bold fs-6 text-truncate">Cargando...</div>
                <span id="categoriaTopTotal" class="fw-bold text-danger small">...</span>
            </div>
        </div>
    </div>
</div>

<!-- Cajas -->
<div class="mb-4">
    <h6 class="fw-bold mb-2">Estado de las Cajas</h6>
    <div class="row g-2" id="cardsCajas"></div>
</div>

<!-- Centros de Costo -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-diagram-3 me-2"></i>Gastos por Centro de Costo (Mes Actual)</h6>
        <div id="centrosCostosDashboard">
            <div class="text-muted small">Cargando centros de costo...</div>
        </div>
    </div>
</div>

<!-- Tablas de Gestión Contador -->
<div class="row g-3 mb-4">
    <!-- Cheques a Vencer -->
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                <span class="small fw-bold"><i class="bi bi-card-heading me-1"></i>Cheques a Vencer</span>
                <a href="/contable/modules/cheques/" class="btn btn-outline-light btn-xs py-0 px-1" style="font-size: 10px;">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" id="tablaCheques" style="font-size: 12px;">
                    <thead class="table-light">
                        <tr><th>Venc.</th><th>Banco/N°</th><th class="text-end">Importe</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Agenda de Vencimientos -->
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                <span class="small fw-bold"><i class="bi bi-calendar-check me-1"></i>Agenda Vencimientos</span>
                <a href="/contable/modules/vencimientos/" class="btn btn-outline-light btn-xs py-0 px-1" style="font-size: 10px;">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" id="tablaVencimientos" style="font-size: 12px;">
                    <thead class="table-light">
                        <tr><th>Venc.</th><th>Concepto</th><th class="text-end">Importe</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Facturas Pendientes de Cobro -->
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                <span class="small fw-bold"><i class="bi bi-receipt-cutoff me-1"></i>Facturas x Cobrar</span>
                <a href="/contable/modules/ventas/" class="btn btn-outline-light btn-xs py-0 px-1" style="font-size: 10px;">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" id="tablaFacturasCobrar" style="font-size: 12px;">
                    <thead class="table-light">
                        <tr><th>Venc.</th><th>N° Fact.</th><th>Cliente</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>