/* ==========================================================================
   LECTOR DEL COMPROBANTE PDF DE AFIP / ARCA
   --------------------------------------------------------------------------
   Lo usan el asistente de carga de Facturación (ventas: el cliente es el
   receptor) y el de Gastos (compras: el proveedor es el emisor).
   En el PDF oficial cada valor está en la misma línea que su rótulo
   (ej: "Fecha de Emisión:" y "27/08/2026" a la misma altura), así que se lee
   por posición en lugar de por orden de aparición.

   Uso:
     PdfAfip.leerArchivo(file).then(datos => ...)   // carga pdf.js si hace falta
     analizarPdfAfip(items)                          // items: [{str, x, y}]
   ========================================================================== */

const AFIP_CODIGOS_COMPROBANTE = {
    '1': ['FACTURA', 'A'], '2': ['NOTA DE DEBITO', 'A'], '3': ['NOTA DE CREDITO', 'A'],
    '6': ['FACTURA', 'B'], '7': ['NOTA DE DEBITO', 'B'], '8': ['NOTA DE CREDITO', 'B'],
    '11': ['FACTURA', 'C'], '12': ['NOTA DE DEBITO', 'C'], '13': ['NOTA DE CREDITO', 'C'],
    '19': ['FACTURA', 'E'], '51': ['FACTURA', 'M']
};
const RE_IMPORTE_AFIP = /^-?[\d.]*\d,\d{2}$/;   // 1819098,28 o 1.819.098,28
const RE_FECHA_AFIP = /(\d{2})\/(\d{2})\/(\d{4})/;

function normalizarTxt(s) {
    return String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/\s+/g, ' ').trim();
}

// "1.819.098,28" o "1819098,28" -> 1819098.28
function importeAfip(s) {
    const n = parseFloat(String(s).replace(/\./g, '').replace(',', '.'));
    return isNaN(n) ? null : n;
}

function analizarPdfAfip(items) {
    const conRotulo = re => items.filter(i => re.test(normalizarTxt(i.str)));
    const mismaLinea = (a, b) => Math.abs(a.y - b.y) <= 3;
    const aLaDerecha = (r, reValor) => items
        .filter(i => i !== r && mismaLinea(i, r) && i.x > r.x && reValor.test(i.str))
        .sort((a, b) => a.x - b.x)[0];

    // Valor más cercano a la derecha del rótulo, en la misma línea
    const valorDe = (reRotulo, reValor) => {
        for (const r of conRotulo(reRotulo)) {
            const v = aLaDerecha(r, reValor);
            if (v) return v.str;
        }
        return '';
    };
    const fechaISO = s => { const m = String(s).match(RE_FECHA_AFIP); return m ? `${m[3]}-${m[2]}-${m[1]}` : ''; };

    const d = {};
    d.fecha = fechaISO(valorDe(/^fecha de emision/, RE_FECHA_AFIP));
    d.vencimiento = fechaISO(valorDe(/^fecha de vto\. para el pago/, RE_FECHA_AFIP));
    d.punto_venta = parseInt(valorDe(/^punto de venta/, /^\d{1,5}$/), 10) || '';
    d.numero = parseInt(valorDe(/^comp\. nro/, /^\d{1,8}$/), 10) || '';
    d.cae = valorDe(/^cae n/, /^\d{14}$/);
    d.vto_cae = valorDe(/^fecha de vto\. de cae/, RE_FECHA_AFIP);

    // Tipo: por el código ("COD. 01") o, si no está, por el texto y la letra
    const cod = items.map(i => i.str.match(/^COD\.?\s*0*(\d{1,3})$/i)).find(Boolean);
    if (cod && AFIP_CODIGOS_COMPROBANTE[cod[1]]) {
        [d.tipo, d.letra] = AFIP_CODIGOS_COMPROBANTE[cod[1]];
    } else {
        const tipoTxt = items.find(i => /^(factura|nota de credito|nota de debito)$/.test(normalizarTxt(i.str)));
        const letra = items.find(i => /^[ABCEM]$/.test(i.str));
        if (tipoTxt) d.tipo = normalizarTxt(tipoTxt.str).toUpperCase();
        if (letra) d.letra = letra.str;
    }

    // Receptor (cliente en ventas): CUIT y nombre en la línea de "Apellido y Nombre / Razón Social"
    const rotReceptor = conRotulo(/^apellido y nombre \/ razon social/)[0];
    if (rotReceptor) {
        const enLinea = items.filter(i => mismaLinea(i, rotReceptor));
        const cuit = enLinea.find(i => /^\d{11}$/.test(i.str));
        const nombre = enLinea.filter(i => i.x > rotReceptor.x).sort((a, b) => a.x - b.x)[0];
        d.cuit_receptor = cuit ? cuit.str : '';
        d.nombre_receptor = nombre ? nombre.str : '';
    }
    // Compatibilidad con el asistente de Facturación
    d.cuit_cliente = d.cuit_receptor || '';
    d.nombre_cliente = d.nombre_receptor || '';

    // Emisor (proveedor en compras): el "CUIT:" que no está en la línea del receptor
    const rotCuitEmisor = conRotulo(/^cuit:?$/).find(r => !(rotReceptor && mismaLinea(r, rotReceptor)));
    const cuitEmisor = rotCuitEmisor ? aLaDerecha(rotCuitEmisor, /^\d{11}$/) : null;
    d.cuit_emisor = cuitEmisor ? cuitEmisor.str : '';
    d.nombre_emisor = valorDe(/^razon social:?$/, /\S/);

    // Detalle: solo la columna "Producto / Servicio", entre los encabezados y los totales
    const hProd = conRotulo(/^producto \/ servicio$/)[0];
    const hCant = conRotulo(/^cantidad$/)[0];
    if (hProd) {
        const xMax = hCant ? hCant.x - 5 : hProd.x + 180;
        const yMin = Math.max(0, ...conRotulo(/^(importe otros tributos|importe neto gravado|importe total|subtotal:)/).filter(i => i.y < hProd.y).map(i => i.y));
        d.detalle = items
            .filter(i => i.y < hProd.y - 3 && i.y > yMin && i.x >= hProd.x - 5 && i.x < xMax)
            .sort((a, b) => (b.y - a.y) || (a.x - b.x))
            .map(i => i.str).join(' ').replace(/\s+/g, ' ').trim();
    }

    // Importes: neto, IVA (sumando todas las alícuotas), otros tributos y total
    const num = s => s ? importeAfip(s) : null;
    d.total = num(valorDe(/^importe total/, RE_IMPORTE_AFIP));
    d.neto = num(valorDe(/^importe neto gravado/, RE_IMPORTE_AFIP));
    let iva = 0, hayIva = false;
    conRotulo(/^iva \d+(\.\d+)?%/).forEach(r => {
        const v = aLaDerecha(r, RE_IMPORTE_AFIP);
        if (v) { iva += importeAfip(v.str); hayIva = true; }
    });
    const ivaContenido = num(valorDe(/^iva contenido/, RE_IMPORTE_AFIP)); // facturas B (transparencia fiscal)
    if (hayIva) d.iva = Math.round(iva * 100) / 100;
    else if (ivaContenido !== null) d.iva = ivaContenido;
    if (d.neto === null) {
        // Facturas B / C: sin neto gravado discriminado
        const subtotal = num(valorDe(/^subtotal:/, RE_IMPORTE_AFIP));
        const base = subtotal !== null ? subtotal : d.total;
        if (base !== null) d.neto = Math.round((base - (d.iva || 0)) * 100) / 100;
        if (d.iva === undefined) d.iva = 0;
    }
    // "Importe Otros Tributos" aparece dos veces: se usa el del bloque de totales (el más abajo)
    const rotOtros = conRotulo(/^importe otros tributos/).sort((a, b) => a.y - b.y)[0];
    const vOtros = rotOtros ? aLaDerecha(rotOtros, RE_IMPORTE_AFIP) : null;
    d.otros_tributos = vOtros ? importeAfip(vOtros.str) : 0;

    return d;
}

window.PdfAfip = (function () {
    const PDFJS = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js';
    const WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    let cargando = null;

    // pdf.js se carga recién cuando se usa (en Gastos, Caja y Vencimientos no pesa de entrada)
    function cargarPdfJs() {
        if (window.pdfjsLib) return Promise.resolve(window.pdfjsLib);
        if (!cargando) {
            cargando = new Promise((ok, error) => {
                const s = document.createElement('script');
                s.src = PDFJS;
                s.onload = () => { window.pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER; ok(window.pdfjsLib); };
                s.onerror = () => { cargando = null; error(new Error('No se pudo cargar el lector de PDF.')); };
                document.head.appendChild(s);
            });
        }
        return cargando;
    }

    // Lee la primera página del PDF y devuelve los datos del comprobante
    function leerArchivo(file) {
        return cargarPdfJs()
            .then(lib => file.arrayBuffer().then(buf => lib.getDocument(new Uint8Array(buf)).promise))
            .then(pdf => pdf.getPage(1))
            .then(page => page.getTextContent())
            .then(tc => analizarPdfAfip(tc.items
                .filter(it => it.str && it.str.trim() !== '')
                .map(it => ({ str: it.str.trim(), x: it.transform[4], y: it.transform[5] }))));
    }

    return { leerArchivo: leerArchivo, cargarPdfJs: cargarPdfJs };
})();
