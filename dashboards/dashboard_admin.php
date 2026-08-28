<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark mb-0">📊 Tablero Gerencial</h4>
    <span class="badge bg-dark px-3 py-2">Perfil: Administrador</span>
</div>

<!-- Tarjetas KPI -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-top border-primary border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">SALDO DISPONIBLE TOTAL</small>
                <h3 id="saldoDisponible" class="fw-bold mb-0 text-primary fs-4">Cargando...</h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-top border-danger border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">GASTOS DEL MES</small>
                <h3 id="gastosMes" class="fw-bold mb-0 text-danger fs-4">Cargando...</h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-top border-success border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">RENTABILIDAD NETA</small>
                <h3 class="fw-bold mb-0 text-muted fs-4">Próximamente</h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-top border-info border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">PUNTO DE EQUILIBRIO</small>
                <h3 class="fw-bold mb-0 text-muted fs-4">Próximamente</h3>
            </div>
        </div>
    </div>
</div>

<!-- Centros de Costo y Resumen de Cajas -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0">
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
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white">
                <strong>🏦 Estado General de Cajas</strong>
            </div>
            <div class="card-body p-2">
                <div class="row g-2" id="cardsCajas"></div>
            </div>
        </div>
    </div>
</div>