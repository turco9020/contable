<!-- FILA 1: KPIs FINANCIEROS (5 TARJETAS COMPACTAS) -->
<div class="row g-2 mb-3">
    <!-- Gastos (Actual vs Anterior) -->
    <div class="col-12 col-md-4 col-xl-2.4" style="width: 20%;">
        <div class="card shadow-sm border-0 border-start border-danger border-3 p-2 bg-white h-100">
            <span class="text-muted fw-semibold text-uppercase" style="font-size: 9.5px;">Gastos</span>
            <div id="gastosMes" class="fw-bold text-danger fs-5 mt-1">$ 0,00</div>
            <div class="text-muted border-top pt-1 mt-1 d-flex justify-content-between align-items-center" style="font-size: 10px;">
                <span>Mes Ant:</span>
                <span id="gastosMesAnterior" class="fw-semibold text-secondary">$ 0,00</span>
            </div>
        </div>
    </div>

    <!-- Ventas (Actual vs Anterior) -->
    <div class="col-12 col-md-4 col-xl-2.4" style="width: 20%;">
        <div class="card shadow-sm border-0 border-start border-info border-3 p-2 bg-white h-100">
            <span class="text-muted fw-semibold text-uppercase" style="font-size: 9.5px;">Ventas</span>
            <div id="ventasMes" class="fw-bold text-info fs-5 mt-1">$ 0,00</div>
            <div class="text-muted border-top pt-1 mt-1 d-flex justify-content-between align-items-center" style="font-size: 10px;">
                <span>Mes Ant:</span>
                <span id="ventasMesAnterior" class="fw-semibold text-secondary">$ 0,00</span>
            </div>
        </div>
    </div>

    <!-- Rentabilidad (Actual vs Anterior) -->
    <div class="col-12 col-md-4 col-xl-2.4" style="width: 20%;">
        <div class="card shadow-sm border-0 border-start border-success border-3 p-2 bg-white h-100">
            <span class="text-muted fw-semibold text-uppercase" style="font-size: 9.5px;">Rentabilidad Neta</span>
            <div id="rentabilidadMes" class="fw-bold text-success fs-5 mt-1">$ 0,00</div>
            <div class="text-muted border-top pt-1 mt-1 d-flex justify-content-between align-items-center" style="font-size: 10px;">
                <span>Mes Ant:</span>
                <span id="rentabilidadMesAnterior" class="fw-semibold text-secondary">$ 0,00</span>
            </div>
        </div>
    </div>

    <!-- DIFERENCIA DE IVA (VENTAS - COMPRAS) -->
    <div class="col-12 col-md-4 col-xl-2.4" style="width: 20%;">
        <div class="card shadow-sm border-0 border-start border-warning border-3 p-2 bg-white h-100">
            <span class="text-muted fw-semibold text-uppercase" style="font-size: 9.5px;">Dif. IVA (Ventas - Compras)</span>
            <div id="diferenciaIva" class="fw-bold text-dark fs-5 mt-1">$ 0,00</div>
            <div class="text-muted border-top pt-1 mt-1 d-flex justify-content-between align-items-center" style="font-size: 10px;">
                <span>Ret. Sufridas:</span>
                <span id="retencionesMes" class="fw-semibold text-secondary">$ 0,00</span>
            </div>
        </div>
    </div>

    <!-- CHEQUES EMITIDOS A CUBRIR -->
    <div class="col-12 col-md-4 col-xl-2.4" style="width: 20%;">
        <div class="card shadow-sm border-0 border-start border-dark border-3 p-2 bg-white h-100">
            <span class="text-muted fw-semibold text-uppercase" style="font-size: 9.5px;"><i class="bi bi-card-checklist me-1"></i>Cheques Emitidos a Cubrir</span>
            <div id="chequesEmitidosMes" class="fw-bold text-dark fs-6 mt-1">$ 0,00 <small class="text-muted fw-normal" style="font-size: 9px;">(Este mes)</small></div>
            <div class="text-muted border-top pt-1 mt-1 d-flex justify-content-between align-items-center" style="font-size: 10px;">
                <span>Total Absoluto:</span>
                <span id="chequesEmitidosTotal" class="fw-bold text-danger">$ 0,00</span>
            </div>
        </div>
    </div>
</div>

<!-- FILA 2: CONTROL DE RUBROS Y CATEGORÍAS (MÁXIMO ESPACIO PARA MÁS ITEMS) -->
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-header bg-transparent border-0 pt-2 px-3 pb-1 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0" style="font-size: 12px;">
                    <i class="bi bi-tags me-1"></i>Desglose y Control de Rubros / Categorías
                </h6>
                <div class="btn-group btn-group-sm" id="btnGroupRubros" role="group">
                    <button type="button" class="btn btn-outline-secondary active" onclick="cambiarVistaRubros('mes')">Mes Actual</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="cambiarVistaRubros('mes_anterior')">Mes Pasado</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="cambiarVistaRubros('anual')">Ranking Anual</button>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- Grilla de 2 columnas para mostrar hasta 8 rubros simultáneamente -->
                <div class="row g-3" id="contenedorBarrasRubros">
                    
                    <!-- Columna Izquierda (Rubros Top 1 al 4) -->
                    <div class="col-12 col-md-6 border-end pe-md-3">
                        <div class="mb-2.5">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                                <span class="fw-bold text-dark"><i class="bi bi-wrench-adjustable me-1 text-danger"></i>Mecánica y Mantenimiento Vehículos</span>
                                <span class="fw-bold text-dark">$ 850.000,00 <small class="text-muted fw-normal">(32%)</small></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 32%"></div>
                            </div>
                        </div>

                        <div class="mb-2.5">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                                <span class="fw-bold text-dark"><i class="bi bi-fuel-pump me-1 text-warning"></i>Combustibles y Peajes</span>
                                <span class="fw-bold text-dark">$ 520.000,00 <small class="text-muted fw-normal">(20%)</small></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 20%"></div>
                            </div>
                        </div>

                        <div class="mb-2.5">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                                <span class="fw-bold text-dark"><i class="bi bi-people me-1 text-info"></i>Sueldos y Cargas Sociales</span>
                                <span class="fw-bold text-dark">$ 410.000,00 <small class="text-muted fw-normal">(15%)</small></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 15%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha (Rubros Top 5 al 8) -->
                    <div class="col-12 col-md-6 ps-md-3">
                        <div class="mb-2.5">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                                <span class="fw-bold text-dark"><i class="bi bi-building me-1 text-secondary"></i>Alquileres y Servicios</span>
                                <span class="fw-bold text-dark">$ 300.000,00 <small class="text-muted fw-normal">(11%)</small></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-secondary" role="progressbar" style="width: 11%"></div>
                            </div>
                        </div>

                        <div class="mb-2.5">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                                <span class="fw-bold text-dark"><i class="bi bi-cart me-1 text-dark"></i>Insumos de Oficina</span>
                                <span class="fw-bold text-dark">$ 180.000,00 <small class="text-muted fw-normal">(7%)</small></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-dark" role="progressbar" style="width: 7%"></div>
                            </div>
                        </div>

                        <div class="mb-2.5">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                                <span class="fw-bold text-dark"><i class="bi bi-shield-check me-1 text-primary"></i>Seguros y Patentes</span>
                                <span class="fw-bold text-dark">$ 140.000,00 <small class="text-muted fw-normal">(5%)</small></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 5%"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- FILA 3: CAJAS (CON TOTAL AL FINAL DE LA TABLA) Y CENTROS DE COSTO -->
<div class="row g-3 mb-3">
    <!-- ESTADO DE CAJAS (CON FOOTER DE SALDO TOTAL) -->
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 bg-white h-100">
            <div class="card-header bg-transparent border-0 pt-2 px-3 pb-1 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0" style="font-size: 12px;"><i class="bi bi-wallet2 me-1"></i>Estado de Cajas</h6>
                <span class="badge bg-light text-dark border" style="font-size: 9px;">Saldos al día</span>
            </div>
            <div class="card-body p-0 d-flex flex-column justify-content-between">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" id="tablaCajasAdmin" style="font-size: 11.5px;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Caja</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-center" style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se puebla por JS -->
                        </tbody>
                        <!-- TOTAL DISPONIBLE EN EL FOOTER DE LA TABLA -->
                        <tfoot class="table-light border-top">
                            <tr class="fw-bold">
                                <td class="ps-3 text-dark" style="font-size: 11px;">TOTAL DISPONIBLE:</td>
                                <td class="text-end text-primary fs-6" id="saldoDisponible">$ 0,00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CENTROS DE COSTO -->
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 bg-white h-100">
            <div class="card-header bg-transparent border-0 pt-2 px-3 pb-1 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0" style="font-size: 12px;"><i class="bi bi-pie-chart me-1"></i>Análisis por Centro de Costo</h6>
                <div class="btn-group btn-group-sm" role="group" id="btnGroupPeriodoCentros">
                    <button type="button" class="btn btn-outline-secondary btn-xs px-2 active" onclick="cambiarPeriodoCentros('actual')">Mes Actual</button>
                    <button type="button" class="btn btn-outline-secondary btn-xs px-2" onclick="cambiarPeriodoCentros('pasado')">Mes Pasado</button>
                    <button type="button" class="btn btn-outline-secondary btn-xs px-2" onclick="cambiarPeriodoCentros('anual')">Anual (<?= date('Y') ?>)</button>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-12 col-md-4 text-center border-end">
                        <span class="fw-semibold text-muted d-block mb-2" style="font-size: 10.5px;">Gastos por Centro</span>
                        <div style="height: 140px; position: relative;"><canvas id="chartGastosCentro"></canvas></div>
                    </div>
                    <div class="col-12 col-md-4 text-center border-end">
                        <span class="fw-semibold text-muted d-block mb-2" style="font-size: 10.5px;">Ventas por Centro</span>
                        <div style="height: 140px; position: relative;"><canvas id="chartVentasCentro"></canvas></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <span class="fw-semibold text-muted d-block mb-2" style="font-size: 10.5px;">Rentabilidad por Centro</span>
                        <div class="table-responsive" style="max-height: 140px; overflow-y: auto;">
                            <table class="table table-sm table-borderless mb-0" id="tablaRentabilidadCentro" style="font-size: 11px;">
                                <thead>
                                    <tr class="border-bottom text-muted">
                                        <th>Centro</th>
                                        <th class="text-end">Rentab.</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FILA 4: LISTADOS OPERATIVOS (CHEQUES, FACTURAS X COBRAR, RETENCIONES) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 bg-white h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-1.5 px-3">
                <span class="small fw-bold" style="font-size: 11px;"><i class="bi bi-card-heading me-1"></i>Cheques a Vencer</span>
                <a href="/contable/modules/cheques/" class="btn btn-outline-light btn-xs py-0 px-1" style="font-size: 9.5px;">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" id="tablaCheques" style="font-size: 11.5px;">
                    <thead class="table-light">
                        <tr><th>Venc.</th><th>Banco/N°</th><th class="text-end">Importe</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 bg-white h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-1.5 px-3">
                <span class="small fw-bold" style="font-size: 11px;"><i class="bi bi-receipt-cutoff me-1"></i>Facturas x Cobrar</span>
                <a href="/contable/modules/ventas/" class="btn btn-outline-light btn-xs py-0 px-1" style="font-size: 9.5px;">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" id="tablaFacturasCobrar" style="font-size: 11.5px;">
                    <thead class="table-light">
                        <tr><th>Venc.</th><th>N° Fact.</th><th>Cliente</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 bg-white h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-1.5 px-3">
                <span class="small fw-bold" style="font-size: 11px;"><i class="bi bi-shield-check me-1"></i>Retenciones Sufridas (Mes)</span>
                <a href="/contable/modules/retenciones/" class="btn btn-outline-light btn-xs py-0 px-1" style="font-size: 9.5px;">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" id="tablaRetenciones" style="font-size: 11.5px;">
                    <thead class="table-light">
                        <tr><th>Fecha</th><th>Concepto</th><th class="text-end">Monto</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>