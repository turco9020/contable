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
    <div class="d-flex gap-2">
        <a href="/contable/modules/facturacion/index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Facturacion</a>
        <a href="/contable/modules/clientes/index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Clientes</a>
        </div>
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
                            <th class="text-center" style="width: 40px;">Ver</th>
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

<!-- MODAL: VER FACTURA COMPLETA -->
<div class="modal fade" id="modalVerFactura" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i><span id="vf_titulo">Factura</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="vf_cuerpo"></div>
            <div class="modal-footer">
                <a id="vf_pdf" href="#" target="_blank" class="btn btn-outline-danger d-none"><i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF de la factura</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                exportOptions: { orthogonal: 'export', columns: [0, 1, 2, 3, 4, 5, 6] } // sin la columna del botón
            }
        ],
        columns: [
            { data: 'fecha', render: (d, t) => t === 'export' ? d : cc_fecha(d) },
            { data: 'tipo', render: d => cc_esc(d) }, // tal como figura en Facturación (ej: FACTURA A)
            { data: 'comprobante', render: d => cc_esc(d || '') },
            { data: 'detalle', render: d => cc_esc(d || '') },
            { data: 'debe', className: 'text-end', render: (d, t) => t === 'export' ? Number(d) : (Number(d) ? cc_moneda(d) : '') },
            { data: 'haber', className: 'text-end', render: (d, t) => t === 'export' ? Number(d) : (Number(d) ? cc_moneda(d) : '') },
            { data: 'saldo', className: 'text-end fw-semibold', render: (d, t) => t === 'export' ? Number(d) : cc_moneda(d) },
            {
                // Abre la factura (en cobros y retenciones, la factura a la que corresponden)
                data: 'factura_id', className: 'text-center', orderable: false,
                render: d => d ? `<button class="btn btn-sm btn-outline-dark py-0 px-1" title="Ver factura" onclick="verFacturaCC(${parseInt(d, 10)})"><i class="bi bi-eye"></i></button>` : ''
            }
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

// Modal con la factura completa: datos, importes, cobros, retenciones y saldo
function verFacturaCC(facturaId) {
    $.getJSON('/contable/ajax/clientes.php', { accion: 'cc_factura', id: facturaId }, function(r) {
        if (!r.ok) { Swal.fire('Error', r.error || 'No se pudo abrir la factura.', 'error'); return; }
        const f = r.factura;
        const fila = (lbl, val) => `<tr><th class="text-muted fw-semibold" style="width: 35%;">${lbl}</th><td>${val}</td></tr>`;

        let html = '<table class="table table-sm mb-3">' +
            fila('Cliente', cc_esc(f.cliente)) +
            fila('Fecha', cc_fecha(f.fecha)) +
            fila('Vencimiento', cc_fecha(f.fecha_vencimiento)) +
            fila('Estado', cc_esc(f.estado)) +
            (f.centro_costo ? fila('Centro de costo', cc_esc(f.centro_costo)) : '') +
            (f.obra ? fila('Obra', cc_esc(f.obra)) : '') +
            fila('Detalle', cc_esc(f.detalle || '-').replace(/\n/g, '<br>')) +
            (f.observaciones ? fila('Observaciones', cc_esc(f.observaciones).replace(/\n/g, '<br>')) : '') +
            '</table>';

        html += '<div class="row g-2 text-center small mb-3">' +
            [['Neto', f.neto], ['IVA', f.iva], ['Total', f.total], ['Saldo pendiente', f.saldo]].map(([l, v], i) =>
                `<div class="col-6 col-md-3"><div class="border rounded p-2 ${i === 3 ? 'bg-light' : ''}"><div class="text-muted">${l}</div><div class="fw-bold ${i === 3 ? (v > 0.01 ? 'text-danger' : 'text-success') : ''}">${cc_moneda(v)}</div></div></div>`
            ).join('') + '</div>';

        const filasCobros = f.cobros.map(c => `<tr><td>${cc_fecha(c.fecha)}</td><td>COBRO</td><td>${cc_esc([c.comprobante ? 'Recibo ' + c.comprobante : '', c.medio, c.caja ? 'Caja ' + c.caja : ''].filter(Boolean).join(' · '))}</td><td class="text-end">${cc_moneda(c.importe)}</td></tr>`);
        const filasRet = f.retenciones.map(x => `<tr><td>${cc_fecha(x.fecha)}</td><td>RETENCIÓN</td><td>${cc_esc([x.tipo, x.nro_certificado ? 'Cert. ' + x.nro_certificado : ''].filter(Boolean).join(' · '))}</td><td class="text-end">${cc_moneda(x.importe)}</td></tr>`);
        html += '<h6 class="fw-bold text-secondary">Cobros y retenciones</h6>' +
            '<table class="table table-sm table-bordered mb-0 small"><thead class="table-light"><tr><th>Fecha</th><th>Tipo</th><th>Detalle</th><th class="text-end">Importe</th></tr></thead><tbody>' +
            (filasCobros.concat(filasRet).join('') || '<tr><td colspan="4" class="text-center text-muted">Sin cobros ni retenciones cargados.</td></tr>') +
            '</tbody></table>';

        $('#vf_titulo').text(f.tipo + ' ' + f.numero);
        $('#vf_cuerpo').html(html);
        $('#vf_pdf').toggleClass('d-none', !f.archivo_url).attr('href', f.archivo_url || '#');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerFactura')).show();
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
