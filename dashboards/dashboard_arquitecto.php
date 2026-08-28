<?php
// Consulta de las últimas 6 obras ACTIVAS (excluyendo las FINALIZADAS)
$sql_obras = "SELECT o.*, c.nombre AS cliente_nombre,
                (SELECT SUM(total) FROM gastos WHERE obra_id = o.id) AS total_gastado 
              FROM obras o
              LEFT JOIN clientes c ON c.id = o.cliente_id
              WHERE o.estado != 'FINALIZADA' OR o.estado IS NULL
              ORDER BY o.id DESC 
              LIMIT 6";
$res_obras = mysqli_query($conn, $sql_obras);
?>

<!-- 1. RESUMEN DE CAJA -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-wallet2 me-2"></i>Saldos de Cajas
        </h6>
    </div>
    
    <!-- Aquí es donde dashboard.js inyecta las tarjetas -->
    <div class="row g-3" id="cardsCajas">
        <div class="col-12 text-muted small py-2">Cargando saldos...</div>
    </div>
</div>

<!-- 2. ÚLTIMAS OBRAS EN EJECUCIÓN (TARJETAS COMPACTAS) -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-building me-2"></i>Obras Recientes</h5>
    <a href="/contable/modules/config/obras/" class="btn btn-outline-dark btn-sm">Ver Tablero Completo</a>
</div>

<div class="row g-2 mb-4">
    <?php if ($res_obras && mysqli_num_rows($res_obras) > 0): ?>
        <?php while ($obra = mysqli_fetch_assoc($res_obras)): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0 task-card p-2 px-3 position-relative cursor-pointer" 
                     onclick="verObra(<?= $obra['id'] ?>)"
                     style="background: #ffffff; border-radius: 6px; transition: transform 0.15s ease-in-out;">
                    
                    <!-- Encabezado con Badges -->
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-secondary text-white px-2 py-1" style="font-size: 10px;">
                            <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i><?= htmlspecialchars($obra['estado'] ?? 'ACTIVA') ?>
                        </span>
                        <span class="text-muted text-truncate ms-2 small" style="font-size: 11px;" title="<?= htmlspecialchars($obra['direccion'] ?? 'Sin Dirección') ?>">
                            <i class="bi bi-geo-alt me-1 text-secondary"></i><?= htmlspecialchars($obra['direccion'] ?? 'Sin Dirección') ?>
                        </span>
                    </div>

                    <!-- Título de la Obra -->
                    <div class="fw-bold text-dark text-truncate small mb-1" title="<?= htmlspecialchars($obra['nombre']) ?>">
                        <?= htmlspecialchars($obra['nombre']) ?>
                    </div>

                    <!-- Cliente Asignado -->
                    <div class="text-muted text-truncate mb-2" style="font-size: 12px;">
                        <i class="bi bi-person me-1 text-primary"></i><strong>Cliente:</strong> <?= htmlspecialchars($obra['cliente_nombre'] ?? 'Sin Cliente') ?>
                    </div>

                    <!-- Pie de la Tarjeta (Gastos Acumulados) -->
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-1 border-top border-light">
                        <span class="text-muted small fw-semibold" style="font-size: 11px;">Gasto Acumulado:</span>
                        <span class="fw-bold text-danger small">
                            $<?= number_format($obra['total_gastado'] ?? 0, 2, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-light text-muted border text-center mb-0 py-2 small">
                <i class="bi bi-info-circle me-1"></i> No hay obras registradas recientemente.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL COMPLETO DE DETALLE DE OBRA -->
<div class="modal fade" id="modalObra" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalObraLabel"><i class="bi bi-cone-striped me-2"></i>Detalles Completos de la Obra</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formObra" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="id">

                    <div class="row">
                        <!-- Fila 1 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nombre de la Obra</label>
                            <input name="nombre" id="nombre" class="form-control" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Dirección de la Obra</label>
                            <input name="direccion" id="direccion" class="form-control" disabled>
                        </div>

                        <!-- Fila 2 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Cliente Asignado</label>
                            <select name="cliente_id" id="cliente_id" class="form-select" disabled></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Responsable a Cargo</label>
                            <input name="responsable" id="responsable" class="form-control" disabled>
                        </div>

                        <!-- Fila 3 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tipo de Obra</label>
                            <select name="tipo_obra" id="tipo_obra" class="form-select" disabled>
                                <option value="PRIVADA">🔒 PRIVADA</option>
                                <option value="PUBLICA">🏛️ PUBLICA</option>
                                <option value="PARTICULAR">👤 PARTICULAR</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">N° de OC</label>
                            <input name="nro_oc" id="nro_oc" class="form-control" disabled>
                        </div>

                        <!-- Fila 4 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Fecha de Fin (Estimada/Real)</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" disabled>
                        </div>

                        <!-- Fila 5 -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Estado Operativo</label>
                            <select name="estado" id="estado" class="form-select" disabled>
                                <option value="ACTIVA">🟢 ACTIVA</option>
                                <option value="FINALIZADA">🔴 FINALIZADA</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Facturación</label>
                            <select name="facturacion" id="facturacion" class="form-select" disabled>
                                <option value="Por Cobrar">⏳ Por Cobrar</option>
                                <option value="Pagadas">💵 Pagadas</option>
                            </select>
                        </div>

                        <!-- Fila 6: Detalles -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Detalles / Memoria Descriptiva</label>
                            <textarea name="detalle" id="detalle" class="form-control" rows="2" disabled></textarea>
                        </div>

                        <!-- PESTAÑAS DE VINCULACIONES Y ARCHIVOS EN LA OBRA -->
                        <div class="col-12 mt-2" id="seccionListadosObra">
                            <ul class="nav nav-tabs" id="tabModulosObra" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active fw-bold text-dark py-1 px-3" id="tab-repo-btn" data-bs-toggle="tab" data-bs-target="#tab-repo" type="button" role="tab">
                                        <i class="bi bi-folder2-open me-1 text-primary"></i> Documentos (<span id="cantDocRepo">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-dark py-1 px-3" id="tab-ventas-btn" data-bs-toggle="tab" data-bs-target="#tab-ventas" type="button" role="tab">
                                        <i class="bi bi-receipt me-1 text-success"></i> Facturas Venta (<span id="cantFacVentas">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-dark py-1 px-3" id="tab-gastos-btn" data-bs-toggle="tab" data-bs-target="#tab-gastos" type="button" role="tab">
                                        <i class="bi bi-cart-dash me-1 text-danger"></i> Gastos / Compras (<span id="cantFacGastos">0</span>)
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content border border-top-0 p-2 rounded-bottom bg-white" id="tabModulosObraContent">
                                <div class="tab-pane fade show active" id="tab-repo" role="tabpanel">
                                    <ul class="list-group list-group-flush" id="listaArchivosObra"></ul>
                                </div>
                                <div class="tab-pane fade" id="tab-ventas" role="tabpanel">
                                    <ul class="list-group list-group-flush" id="listaFacturasObra"></ul>
                                </div>
                                <div class="tab-pane fade" id="tab-gastos" role="tabpanel">
                                    <ul class="list-group list-group-flush" id="listaGastosObra"></ul>
                                </div>
                            </div>
                        </div>

                        <!-- RESUMEN FINANCIERO -->
                        <div id="seccionResumenFinanciero" class="col-12 mt-3">
                            <div class="p-2 bg-light rounded border shadow-sm">
                                <div class="row text-center g-2 align-items-center">
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded border-start border-success border-3">
                                            <span class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.75rem;">Total Ventas</span>
                                            <span class="fw-bold text-success fs-6" id="lblTotalVentas">$ 0,00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded border-start border-danger border-3">
                                            <span class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.75rem;">Total Gastos</span>
                                            <span class="fw-bold text-danger fs-6" id="lblTotalGastos">$ 0,00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded border-start border-primary border-3" id="boxMargenNeto">
                                            <span class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.75rem;">Margen / Balance</span>
                                            <span class="fw-bold fs-6" id="lblMargenNeto">$ 0,00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a id="btnIrModuloObra" href="#" class="btn btn-sm btn-dark">Ir al Módulo de la Obra</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalObraBS;
let globalTotalVentas = 0;
let globalTotalGastos = 0;

document.addEventListener("DOMContentLoaded", function() {
    modalObraBS = new bootstrap.Modal(document.getElementById('modalObra'));
    cargarClientes();
});

function cargarClientes(callback = null) {
    $.get('/contable/ajax/clientes.php?accion=listar', function(r) {
        let s = $('#cliente_id');
        s.empty().append('<option value="" selected disabled>Seleccione un cliente...</option>');
        if (r.data) {
            r.data.forEach(x => { s.append(`<option value="${x.id}">${x.nombre}</option>`); });
        }
        if (callback) callback();
    }, 'json');
}

window.verObra = function(obraId) {
    globalTotalVentas = 0;
    globalTotalGastos = 0;

    $('#listaArchivosObra, #listaFacturasObra, #listaGastosObra').html('');
    $('#tab-repo-btn').tab('show');
    $('#btnIrModuloObra').attr('href', '/contable/modules/config/obras/index.php?id=' + obraId);

    $.get('/contable/ajax/obras.php?accion=listar', function(r) {
        if (!r.data) return;
        let d = r.data.find(x => x.id == obraId);
        if (!d) return;

        $('#id').val(d.id);
        $('#nombre').val(d.nombre);
        $('#cliente_id').val(d.cliente_id);
        $('#responsable').val(d.responsable);
        $('#direccion').val(d.direccion);
        $('#nro_oc').val(d.nro_oc);
        $('#fecha_inicio').val(d.fecha_inicio);
        $('#fecha_fin').val(d.fecha_fin);
        $('#tipo_obra').val(d.tipo_obra);
        $('#detalle').val(d.detalle);
        $('#estado').val(d.estado);
        $('#facturacion').val(d.facturacion);

        if (d.presupuesto_archivo) {
            $('#listaArchivosObra').prepend(`
                <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center py-1 fw-semibold border-secondary small mb-2">
                    <a href="/contable/uploads/obras/${d.presupuesto_archivo}" target="_blank" class="text-decoration-none text-dark">
                        <i class="bi bi-file-earmark-check-fill text-success me-2"></i> 📄 PRESUPUESTO ACEPTADO
                    </a>
                    <span class="badge bg-success rounded-pill">Principal</span>
                </li>
            `);
        }

        window.cargarRepositorio(d.id);
        window.cargarFacturasAsociadas(d.id);
        window.cargarGastosAsociados(d.id);

        modalObraBS.show();
    }, 'json');
}

function calcularBalanceGlobal() {
    let fmt = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
    
    $('#lblTotalVentas').text(fmt.format(globalTotalVentas));
    $('#lblTotalGastos').text(fmt.format(globalTotalGastos));

    let margen = globalTotalVentas - globalTotalGastos;
    let lblMargen = $('#lblMargenNeto');
    lblMargen.text(fmt.format(margen));

    if (margen >= 0) {
        lblMargen.removeClass('text-danger').addClass('text-success');
    } else {
        lblMargen.removeClass('text-success').addClass('text-danger');
    }
}

window.cargarRepositorio = function(obraId) {
    $.get('/contable/ajax/obras.php?accion=listar_archivos', { obra_id: obraId }, function(r) {
        let lista = $('#listaArchivosObra');
        if (r.success && r.archivos.length > 0) {
            $('#cantDocRepo').text(r.archivos.length);
            r.archivos.forEach(arc => {
                lista.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                        <a href="/contable/uploads/obras/${arc.archivo}" target="_blank" class="text-decoration-none text-dark">
                            <i class="bi bi-file-earmark text-primary me-2"></i> ${arc.nombre_original}
                        </a>
                    </li>
                `);
            });
        } else {
            $('#cantDocRepo').text('0');
            lista.append('<li class="list-group-item text-muted text-center py-2 small">No hay documentos cargados en el repositorio.</li>');
        }
    }, 'json');
}

window.cargarFacturasAsociadas = function(obraId) {
    $.get('/contable/ajax/obras.php?accion=listar_facturas', { obra_id: obraId }, function(r) {
        let lista = $('#listaFacturasObra');
        lista.empty();
        globalTotalVentas = 0;
        
        if (r.success && r.facturas.length > 0) {
            $('#cantFacVentas').text(r.facturas.length);
            r.facturas.forEach(fac => {
                let monto = parseFloat(fac.total) || 0;
                globalTotalVentas += monto;

                let totalFormateado = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto);
                let fechaFormateada = fac.fecha ? fac.fecha.split('-').reverse().join('/') : '-';

                let botonAdjunto = fac.archivo ? 
                    `<a href="/contable/uploads/facturacion/${fac.archivo}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2" title="Ver PDF Factura"><i class="bi bi-file-earmark-pdf"></i></a>` : 
                    `<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" disabled title="Sin adjunto"><i class="bi bi-eye-slash"></i></button>`;

                lista.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                        <div>
                            <i class="bi bi-file-earmark-text text-success me-2"></i> 
                            <span class="fw-semibold">Factura N° ${fac.nro_factura}</span> 
                            <small class="text-muted ms-2">(${fechaFormateada})</small>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-dark">${totalFormateado}</span>
                            ${botonAdjunto}
                        </div>
                    </li>
                `);
            });
        } else {
            $('#cantFacVentas').text('0');
            lista.append('<li class="list-group-item text-muted text-center py-2 small">No hay facturas de venta vinculadas a esta obra.</li>');
        }
        calcularBalanceGlobal();
    }, 'json');
}

window.cargarGastosAsociados = function(obraId) {
    $.get('/contable/ajax/obras.php?accion=listar_gastos', { obra_id: obraId }, function(r) {
        let lista = $('#listaGastosObra');
        lista.empty();
        globalTotalGastos = 0;

        if (r.success && r.gastos.length > 0) {
            $('#cantFacGastos').text(r.gastos.length);
            r.gastos.forEach(g => {
                let monto = parseFloat(g.total) || 0;
                globalTotalGastos += monto;

                let totalFormateado = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto);
                let fechaFormateada = g.fecha ? g.fecha.split('-').reverse().join('/') : '-';
                let prov = g.proveedor ? g.proveedor : 'Gasto General';
                let comp = g.numero_comprobante ? `Comp: ${g.numero_comprobante}` : 'Sin comprobante';

                let botonAdjunto = g.archivo ? 
                    `<a href="/contable/uploads/gastos/${g.archivo}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2" title="Ver Comprobante Gasto"><i class="bi bi-file-earmark-pdf"></i></a>` : 
                    `<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" disabled title="Sin adjunto"><i class="bi bi-eye-slash"></i></button>`;

                lista.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                        <div>
                            <i class="bi bi-cart-dash text-danger me-2"></i> 
                            <span class="fw-semibold">${prov}</span> 
                            <small class="text-muted ms-2">(${comp} - ${fechaFormateada})</small>
                            <span class="text-secondary d-block" style="font-size:0.75rem">${g.detalle || ''}</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-danger">${totalFormateado}</span>
                            ${botonAdjunto}
                        </div>
                    </li>
                `);
            });
        } else {
            $('#cantFacGastos').text('0');
            lista.append('<li class="list-group-item text-muted text-center py-2 small">No hay gastos o egresos registrados para esta obra.</li>');
        }
        calcularBalanceGlobal();
    }, 'json');
}
</script>