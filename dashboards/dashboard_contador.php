<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark mb-0">💼 Panel Contable & Financiero</h4>
    <span class="badge bg-success px-3 py-2">Perfil: Contador</span>
</div>

<!-- Indicadores de Caja y Gastos -->
<div class="row g-3 mb-4">
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

<!-- Agenda y Cheques -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white">Cheques a Vencer</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" id="tablaUltimosGastos">
                    <thead class="table-light">
                        <tr><th>Fecha</th><th>Proveedor</th><th>Total</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white">Agenda de Vencimientos</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" id="tablaUltimosMovimientos">
                    <thead class="table-light">
                        <tr><th>Fecha</th><th>Concepto</th><th>Importe</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>