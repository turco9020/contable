<?php
include '../../includes/header.php';
include '../../includes/sidebar.php';
$cliente_inicial = (int)($_GET['id'] ?? 0);
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-journal-text text-secondary me-2"></i> Cuenta Corriente de Clientes
        </h4>
        <a href="/contable/modules/clientes/index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Clientes</a>
    </div>

    <!-- FILTROS -->
    <div class="card p-3 shadow-sm border-0 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">Cliente</label>
                <select id="cc_cliente"></select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Desde</label>
                <input type="date" id="cc_desde" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Hasta</label>
                <input type="date" id="cc_hasta" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-dark btn-sm flex-grow-1" onclick="cargarCuentaCorriente()"><i class="bi bi-search me-1"></i> Ver</button>
                <button class="btn btn-outline-danger btn-sm" onclick="pdfCuentaCorriente()" title="PDF para enviar al cliente"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
            </div>
        </div>
    </div>

    <div id="cc_vacio" class="alert alert-info">Elegí un cliente para ver su cuenta corriente.</div>

    <div id="cc_contenido" class="d-none">
        <!-- RESUMEN -->
        <div class="row g-2 mb-3 text-center">
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm p-2"><div class="small text-muted">Saldo anterior</div><div class="fw-bold" id="cc_saldo_inicial">$ 0,00</div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm p-2"><div class="small text-muted">Facturado (Debe)</div><div class="fw-bold" id="cc_debe">$ 0,00</div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm p-2"><div class="small text-muted">Cobrado y retenido (Haber)</div><div class="fw-bold text-success" id="cc_haber">$ 0,00</div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm p-2 bg-light"><div class="small text-muted">Saldo a cobrar</div><div class="fw-bold fs-5" id="cc_saldo">$ 0,00</div></div></div>
        </div>

        <!-- MOVIMIENTOS -->
        <div class="card p-3 shadow-sm border-0 mb-3">
            <h6 class="fw-bold text-secondary mb-2">Movimientos</h6>
            <div class="table-responsive">
                <table id="tablaCC" class="table table-sm table-bordered table-striped w-100 align-middle" style="font-size: 0.85rem;">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Comprobante</th>
                            <th>Detalle</th>
                            <th class="text-end">Debe</th>
                            <th class="text-end">Haber</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- FACTURAS PENDIENTES -->
        <div class="card p-3 shadow-sm border-0">
            <h6 class="fw-bold text-secondary mb-2">Facturas con saldo pendiente <span class="text-muted fw-normal small">(a hoy)</span></h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Vencimiento</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody id="cc_pendientes"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>

<script>
const CC_CLIENTE_INICIAL = <?= $cliente_inicial ?>;
let tsCliente, tablaCC;

function cc_moneda(n) {
    return '$ ' + Number(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function cc_fecha(f) {
    return f ? String(f).split('-').reverse().join('/') : '-';
}
function cc_esc(s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}
const CC_COLOR_TIPO = { 'FACTURA': 'bg-dark', 'COBRO': 'bg-success', 'RETENCIÓN': 'bg-secondary', 'NOTA DE CRÉDITO': 'bg-info text-dark', 'CANCELADA': 'bg-light text-dark border' };

document.addEventListener('DOMContentLoaded', function() {
    tsCliente = new TomSelect('#cc_cliente', { placeholder: '-- Elegí un cliente --', onChange: () => cargarCuentaCorriente() });

    $.get('/contable/ajax/clientes.php?accion=listar', function(r) {
        (r.data || []).sort((a, b) => String(a.nombre).localeCompare(b.nombre)).forEach(c => tsCliente.addOption({ value: c.id, text: c.nombre + (c.cuit ? ' (' + c.cuit + ')' : '') }));
        if (CC_CLIENTE_INICIAL > 0) tsCliente.setValue(String(CC_CLIENTE_INICIAL));
    }, 'json');

    tablaCC = $('#tablaCC').DataTable({
        data: [],
        ordering: false,          // orden cronológico, tal como viene del servidor
        paging: false,
        searching: true,
        info: false,
        autoWidth: false,
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rt',
        buttons: [
            {
                extend: 'excelHtml5', text: ' Excel', className: 'btn btn-success btn-sm',
                title: () => 'Cuenta corriente - ' + (tsCliente.getItem(tsCliente.getValue())?.innerText || ''),
                exportOptions: { orthogonal: 'export' }
            }
        ],
        columns: [
            { data: 'fecha', render: (d, t) => t === 'export' ? d : cc_fecha(d) },
            { data: 'tipo', render: (d, t) => t === 'export' ? d : `<span class="badge ${CC_COLOR_TIPO[d] || 'bg-secondary'}">${cc_esc(d)}</span>` },
            { data: 'comprobante', render: d => cc_esc(d || '') },
            { data: 'detalle', render: d => cc_esc(d || '') },
            { data: 'debe', className: 'text-end', render: (d, t) => t === 'export' ? Number(d) : (Number(d) ? cc_moneda(d) : '') },
            { data: 'haber', className: 'text-end', render: (d, t) => t === 'export' ? Number(d) : (Number(d) ? cc_moneda(d) : '') },
            { data: 'saldo', className: 'text-end fw-semibold', render: (d, t) => t === 'export' ? Number(d) : cc_moneda(d) }
        ],
        language: { emptyTable: 'Sin movimientos en el período.', search: 'Buscar:' }
    });
});

function cargarCuentaCorriente() {
    const id = tsCliente.getValue();
    if (!id) { $('#cc_contenido').addClass('d-none'); $('#cc_vacio').removeClass('d-none'); return; }

    $.getJSON('/contable/ajax/clientes.php', { accion: 'cuenta_corriente', id: id, desde: $('#cc_desde').val(), hasta: $('#cc_hasta').val() }, function(r) {
        if (!r.ok) { Swal.fire('Error', r.error || 'No se pudo cargar la cuenta corriente.', 'error'); return; }

        $('#cc_vacio').addClass('d-none');
        $('#cc_contenido').removeClass('d-none');

        $('#cc_saldo_inicial').text(cc_moneda(r.saldo_inicial));
        $('#cc_debe').text(cc_moneda(r.totales.debe));
        $('#cc_haber').text(cc_moneda(r.totales.haber));
        $('#cc_saldo').text(cc_moneda(r.totales.saldo)).toggleClass('text-danger', r.totales.saldo > 0.01).toggleClass('text-success', r.totales.saldo <= 0.01);

        tablaCC.clear().rows.add(r.movimientos).draw();

        const tb = $('#cc_pendientes').empty();
        if (!r.pendientes.length) tb.append('<tr><td colspan="5" class="text-center text-muted py-2">No tiene facturas con saldo pendiente.</td></tr>');
        r.pendientes.forEach(p => {
            const vencida = p.vencimiento && p.vencimiento < new Date().toISOString().split('T')[0];
            tb.append(`<tr>
                <td>${cc_esc(p.comprobante)}</td>
                <td>${cc_fecha(p.fecha)}</td>
                <td class="${vencida ? 'text-danger fw-semibold' : ''}">${cc_fecha(p.vencimiento)}${vencida ? ' (vencida)' : ''}</td>
                <td class="text-end">${cc_moneda(p.total)}</td>
                <td class="text-end fw-bold">${cc_moneda(p.saldo)}</td>
            </tr>`);
        });
    });
}

function pdfCuentaCorriente() {
    const id = tsCliente.getValue();
    if (!id) { Swal.fire('Elegí un cliente', 'Primero elegí el cliente para armar el PDF.', 'info'); return; }
    const q = new URLSearchParams({ id: id, desde: $('#cc_desde').val(), hasta: $('#cc_hasta').val() });
    window.open('/contable/modules/clientes/cuenta_corriente_pdf.php?' + q.toString(), '_blank');
}

$('#cc_desde, #cc_hasta').on('change', () => { if (tsCliente.getValue()) cargarCuentaCorriente(); });
</script>
