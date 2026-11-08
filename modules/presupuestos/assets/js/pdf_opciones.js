// =====================================================================
// PDF del presupuesto y de APU: antes de abrirlo muestra una ventana con las
// opciones de impresión (Imprimir K / IVA / logo / versión; en el resumen de
// recursos, Imprimir costos en lugar de K e IVA).
// Uso en un link: onclick="return pdfElegirOpciones(event, this.href)"
// El href queda como respaldo (clic con la rueda / abrir en otra pestaña),
// con el formato por defecto de cada PDF.
// Las últimas opciones elegidas se recuerdan en este navegador, por tipo de PDF.
// =====================================================================
(function() {
    // Valores por defecto = cómo sale cada PDF sin parámetros
    const DEFECTO = {
        presupuesto: { k: false, iva: true, logo: true, ver: false },
        apu:         { k: true,              logo: true, ver: false },
        resumen:     { costos: false,        logo: true, ver: false }
    };
    const CLAVE_GUARDADO = 'presupuestos.pdfOpciones.';

    let modal = null;
    let urlPendiente = '';
    let tipoPendiente = '';

    function crearModal() {
        const html = `
<div class="modal fade" id="modalOpcionesPdf" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-file-earmark-pdf me-2"></i> <span id="opcPdfTitulo">Imprimir PDF</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold small">Opciones de impresión:</label>
                <div class="form-check" id="opcPdfCostosFila">
                    <input class="form-check-input" type="checkbox" id="opcPdfCostos">
                    <label class="form-check-label small" for="opcPdfCostos">
                        <strong>Imprimir costos</strong> <span class="text-muted">(costo neto por insumo y totales, para el arquitecto; sin costos solo salen las cantidades, para el capataz)</span>
                    </label>
                </div>
                <div class="form-check" id="opcPdfKFila">
                    <input class="form-check-input" type="checkbox" id="opcPdfK">
                    <label class="form-check-label small" for="opcPdfK">
                        <strong>Imprimir K</strong> <span class="text-muted" id="opcPdfKAyuda"></span>
                    </label>
                </div>
                <div class="form-check mt-1" id="opcPdfIvaFila">
                    <input class="form-check-input" type="checkbox" id="opcPdfIva">
                    <label class="form-check-label small" for="opcPdfIva">
                        <strong>Imprimir IVA</strong> <span class="text-muted">(suma el IVA 21% y muestra el Total final; sin IVA termina en el Total neto)</span>
                    </label>
                </div>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" id="opcPdfLogo">
                    <label class="form-check-label small" for="opcPdfLogo">
                        <strong>Imprimir logo</strong> <span class="text-muted">(logo de la empresa en el encabezado)</span>
                    </label>
                </div>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" id="opcPdfVer">
                    <label class="form-check-label small" for="opcPdfVer">
                        <strong>Imprimir versión del presupuesto</strong> <span class="text-muted">(N° de versión junto a la fecha)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-danger px-3" id="opcPdfGenerar">
                    <i class="bi bi-file-pdf me-1"></i> Generar PDF
                </button>
            </div>
        </div>
    </div>
</div>`;
        document.body.insertAdjacentHTML('beforeend', html);
        const el = document.getElementById('modalOpcionesPdf');

        // Puede abrirse encima de otra ventana (historial, detalle de versión):
        // su fondo oscuro va por encima de esa ventana y por debajo de esta.
        el.addEventListener('shown.bs.modal', function() {
            const fondos = document.querySelectorAll('.modal-backdrop');
            if (fondos.length > 1) fondos[fondos.length - 1].style.zIndex = 1065;
        });
        // Al cerrarla, si quedó otra ventana abierta debajo, la página sigue sin desplazarse
        el.addEventListener('hidden.bs.modal', function() {
            if (document.querySelector('.modal.show')) document.body.classList.add('modal-open');
        });
        document.getElementById('opcPdfGenerar').addEventListener('click', generar);

        return new bootstrap.Modal(el);
    }

    function leerGuardadas(tipo) {
        try {
            const v = JSON.parse(localStorage.getItem(CLAVE_GUARDADO + tipo) || 'null');
            return Object.assign({}, DEFECTO[tipo], v || {});
        } catch (e) {
            return Object.assign({}, DEFECTO[tipo]);
        }
    }

    function guardar(tipo, opciones) {
        try { localStorage.setItem(CLAVE_GUARDADO + tipo, JSON.stringify(opciones)); } catch (e) { /* sin almacenamiento: no se recuerda */ }
    }

    function generar() {
        const opciones = {
            logo: document.getElementById('opcPdfLogo').checked,
            ver: document.getElementById('opcPdfVer').checked
        };
        if (tipoPendiente === 'resumen') {
            opciones.costos = document.getElementById('opcPdfCostos').checked;
        } else {
            opciones.k = document.getElementById('opcPdfK').checked;
        }
        if (tipoPendiente === 'presupuesto') {
            opciones.iva = document.getElementById('opcPdfIva').checked;
        }
        guardar(tipoPendiente, opciones);

        const destino = new URL(urlPendiente, window.location.href);
        Object.keys(opciones).forEach(function(clave) {
            destino.searchParams.set(clave, opciones[clave] ? '1' : '0');
        });
        window.open(destino.toString(), '_blank', 'noopener');
        modal.hide();
    }

    window.pdfElegirOpciones = function(e, url) {
        if (typeof bootstrap === 'undefined') return true; // sin Bootstrap, sigue el link normal
        e.preventDefault();

        if (!modal) modal = crearModal();
        urlPendiente = url;
        tipoPendiente = /apu_pdf\.php/.test(url) ? 'apu' : (/resumen_pdf\.php/.test(url) ? 'resumen' : 'presupuesto');
        const opciones = leerGuardadas(tipoPendiente);
        const esApu = tipoPendiente === 'apu';
        const esResumen = tipoPendiente === 'resumen';

        document.getElementById('opcPdfTitulo').textContent = esResumen ? 'Imprimir Resumen de recursos'
            : (esApu ? 'Imprimir APU (una hoja por APU)' : 'Imprimir Presupuesto');
        // El resumen no lleva K ni IVA: solo la opción de costos
        document.getElementById('opcPdfCostosFila').classList.toggle('d-none', !esResumen);
        document.getElementById('opcPdfKFila').classList.toggle('d-none', esResumen);
        document.getElementById('opcPdfCostos').checked = !!opciones.costos;
        document.getElementById('opcPdfKAyuda').textContent = esApu
            ? '(insumos con costo directo y abajo Costo unitario × K = Precio unitario)'
            : '(precios de costo directo y abajo Subtotal × K = Total neto)';
        document.getElementById('opcPdfK').checked = !!opciones.k;
        document.getElementById('opcPdfLogo').checked = !!opciones.logo;
        document.getElementById('opcPdfVer').checked = !!opciones.ver;
        // Las hojas de APU y el resumen no llevan IVA
        document.getElementById('opcPdfIvaFila').classList.toggle('d-none', esApu || esResumen);
        document.getElementById('opcPdfIva').checked = !!opciones.iva;

        modal.show();
        return false;
    };
})();
