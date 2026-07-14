<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/sidebar.php';
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>🧾 Facturación (Ventas)</h3>
        <button class="btn btn-dark" onclick="abrirModal()">
            + Nueva Factura
        </button>
    </div>

    <div class="card p-3 shadow-sm">
    <!-- El wrapper que permite el desplazamiento horizontal suave en pantallas chicas -->
    <div class="table-responsive">
        <table id="tablaFacturas" class="table table-bordered table-striped w-100">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Nro. Factura</th>
                    <th>Cliente</th>
                    <th>Centro Costo</th>
                    <th>Total</th>
                    <th>Vence</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
</div>
<div class="modal fade" id="modalFactura" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Factura de Venta</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formFactura" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id">

                    <div id="seccionEscaneo" class="p-3 mb-3 bg-light border rounded text-center">
                        <label class="form-label fw-bold text-secondary mb-2">✨ Asistente de Carga Automática (Subir PDF de AFIP)</label>
                          <input type="file" id="archivo_escanear" name="archivo" class="form-control form-control-sm mx-auto" style="max-width: 400px;" accept="application/pdf">
                           <div class="form-text mt-1">Subí el comprobante digital original para autocompletar el formulario al instante.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Emisión</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo Comprobante</label>
                            <select name="tipo_comprobante_id" id="tipo_comprobante_id" class="form-control" required>
                                </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Punto de Venta / Número</label>
                            <div class="input-group">
                                <input type="number" name="punto_venta" id="punto_venta" class="form-control" placeholder="00002" style="max-width: 90px;" required>
                                <span class="input-group-text">-</span>
                                <input type="number" name="nro_factura" id="nro_factura" class="form-control" placeholder="00000139" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Fecha Vencimiento Pago</label>
                            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <select name="cliente_id" id="cliente_id" class="form-control" required>
                                </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-resaltado mb-2">
                                ⚠️ Centro de Costo
                            </label>
                            <select name="centro_costo_id" id="centro_costo_id" class="form-select border-danger bg-warning bg-opacity-10 text-dark" required>
                                </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label ">Estado de Pago</label>
                            <select name="estado" id="estado" class="form-select " required>
                                <option value="DEBE">🔴 DEBE</option>
                                <option value="PAGADO">🟢 PAGADO</option>
                                <option value="VER">🟡 VER (Revisión)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Detalle (Lo que dice la factura)</label>
                            <textarea name="detalle" id="detalle" class="form-control" rows="3" placeholder="Descripción de los artículos o servicios prestados..." required></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Importe Neto</label>
                            <input type="number" step="0.01" name="neto" id="neto" class="form-control" value="0.00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">IVA</label>
                            <input type="number" step="0.01" name="iva" id="iva" class="form-control" value="0.00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Importe Total</label>
                            <input type="number" step="0.01" name="total" id="total" class="form-control" value="0.00" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observaciones Internas (Usuario)</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Notas del usuario que carga..."></textarea>
                        </div>

                        <div class="col-12" id="seccionArchivoReal" style="display:none;">
                            <label class="form-label">Comprobante Adjunto Definitivo</label>
                            <input type="file" name="archivo" id="archivo" class="form-control">
                            <div id="archivo_actual" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark" id="btnGuardar">Guardar Factura</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>

<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

let tabla;
let modalFactura;
let globalClientesParaMapeo = []; // Almacenará CUITs y IDs para emparejar al vuelo

document.addEventListener("DOMContentLoaded", function() {
    modalFactura = new bootstrap.Modal(document.getElementById('modalFactura'));

    cargarSelects();

    tabla = $('#tablaFacturas').DataTable({
        ajax: '/contable/ajax/facturacion.php?accion=listar',
        order: [[0, 'desc']],

        // EXCLUSIVO PARA QUE SE ADAPTE SIN DESFASE:
        autoWidth: false,      // Evita que DataTables calcule anchos fijos en px
        responsive: true,     
       
        columns: [
            { 
                data: 'fecha',
                render: function(d){
                    if(!d) return '-';
                    let p = d.split('-');
                    return p.length === 3 ? `${p[2]}-${p[1]}-${p[0]}` : d;
                }
            },
            { data: 'tipo_comprobante_nombre' },
            { 
                data: null,
                render: function(row){
                    let pv = String(row.punto_venta).padStart(5, '0');
                    let nf = String(row.nro_factura).padStart(8, '0');
                    return `${pv}-${nf}`;
                }
            },
            { data: 'cliente_nombre' },
            { data: 'centro_costo_nombre' },
            { 
                data: 'total',
                render: function(d) {
                    return '$ ' + Number(d).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                }
            },
            // ==========================================================
            // NUEVA COLUMNA: CÁLCULO DE DÍAS RESTANTES (VENCE EN)
            // ==========================================================
            {
                data: null,
                className: 'text-center',
                render: function(row) {
                    if (!row.fecha_vencimiento) return '<span class="text-muted">-</span>';
                    
                    // Creamos las fechas para la resta (sin horas para que el cálculo de días sea exacto)
                    let fechaVto = new Date(row.fecha_vencimiento + 'T00:00:00');
                    let hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);
                    
                    // Resta en milisegundos y conversión a días
                    let diferenciaMs = fechaVto - hoy;
                    let diasRestantes = Math.ceil(diferenciaMs / (1000 * 60 * 60 * 24));
                    
                    let estadoFactura = row.estado ? row.estado.toUpperCase() : 'DEBE';
                    
                    // Si ya está cobrada/pagada, no tiene sentido alertar por el vencimiento
                    if (estadoFactura === 'PAGADO') {
                        return '<span class="text-muted">✔︎</span>';
                    }
                    
                    if (diasRestantes < 0) {
                        // Vencida (en rojo minimalista)
                        let diasPasados = Math.abs(diasRestantes);
                        return `<span class="text-danger fw-bold">Vencida hace ${diasPasados} ${diasPasados === 1 ? 'día' : 'días'}</span>`;
                    } else if (diasRestantes === 0) {
                        // Vence hoy
                        return '<span class="text-warning fw-bold">Vence hoy</span>';
                    } else {
                        // Faltan días (en gris/negro prolijo)
                        return `<span class="text-secondary">${diasRestantes} ${diasRestantes === 1 ? 'día' : 'días'}</span>`;
                    }
                }
            },
            {
            data: 'estado',
            render: function(d) {
                let texto = d ? d.toUpperCase() : 'DEBE';
                let colorLed = 'bg-danger'; // Por defecto rojo para DEBE
                
                if(texto === 'PAGADO') {
                    colorLed = 'bg-success';
                } else if(texto === 'VER') {
                    colorLed = 'bg-warning';
                }
                
                return `
                    <div class="d-inline-flex align-items-center text-secondary fw-semibold" style="font-size: 0.9rem;">
                        <span class="rounded-circle ${colorLed}" style="width: 8px; height: 8px; display: inline-block; margin-right: 8px;"></span>
                        ${texto}
                    </div>
                `;
            }
        },
            {
                data: null,
                orderable: false,
                render: function(d) {
                    let btnArchivo = d.archivo ? `<a href="/contable/uploads/facturacion/${d.archivo}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver Adjunto">📁</a>` : '';
                    return `
                        <div class="d-flex gap-1">
                            ${btnArchivo}
                            <button class="btn btn-sm btn-secondary" onclick='verFactura(${JSON.stringify(d)})'>Ver</button>
                            <button class="btn btn-sm btn-primary" onclick='editarFactura(${JSON.stringify(d)})'>Editar</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarFactura(${d.id})">Eliminar</button>
                        </div>
                    `;
                }
            }
        ]
    });

    // Truco extra: recalculamos los encabezados si cambia el tamaño de la ventana
    $(window).on('resize', function () {
        tabla.columns.adjust();
    });

    // Escuchador dinámico manual por si editan Neto o IVA manual
    $('#neto, #iva').on('input', function(){
        let n = parseFloat($('#neto').val()) || 0;
        let i = parseFloat($('#iva').val()) || 0;
        $('#total').val((n + i).toFixed(2));
    });

    // ==========================================
    // CAPTURADOR INTELIGENTE AUTOMÁTICO DE PDF (ULTRA FLEXIBLE)
    // ==========================================
    $('#archivo_escanear').on('change', function(e) {
        let file = e.target.files[0];
        if (!file) return;

        if (file.type !== "application/pdf") {
            alert("⚠️ Por favor, seleccioná un archivo PDF oficial de AFIP.");
            $(this).val('');
            return;
        }

        let labelOriginal = $('#seccionEscaneo label').text();
        $('#seccionEscaneo label').text('⏳ Extrayendo datos del comprobante...');

        let fileReader = new FileReader();
        fileReader.onload = function() {
            let typedarray = new Uint8Array(this.result);
            
            pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
                pdf.getPage(1).then(function(page) {
                    page.getTextContent().then(function(textContent) {
                        // Creamos un array con cada fragmento de texto extraído por separado
                        let fragmentos = textContent.items.map(item => item.str.trim()).filter(str => str !== "");
                        // Creamos también el texto unificado completo
                        let textoCompleto = fragmentos.join(" ");
                        
                        $('#seccionEscaneo label').text(labelOriginal);
                        
                        try {
                            // 1. EXTRAER FECHA DE EMISIÓN (Por proximidad de palabra clave)
                            let indexFecha = fragmentos.findIndex(f => f.toLowerCase().includes("emisión") || f.toLowerCase().includes("emision"));
                            if(indexFecha !== -1) {
                                for(let i = indexFecha; i <= indexFecha + 5; i++) {
                                    if(fragmentos[i]) {
                                        let matchF = fragmentos[i].match(/(\d{2})\/(\d{2})\/(\d{4})/);
                                        if(matchF) {
                                            $('#fecha').val(`${matchF[3]}-${matchF[2]}-${matchF[1]}`);
                                            break;
                                        }
                                    }
                                }
                            }

                            // 2. EXTRAER FECHA DE VENCIMIENTO
                            let indexVto = fragmentos.findIndex(f => f.toLowerCase().includes("vto. para el pago") || f.toLowerCase().includes("vto. para"));
                            if(indexVto !== -1) {
                                for(let i = indexVto; i <= indexVto + 5; i++) {
                                    if(fragmentos[i]) {
                                        let matchV = fragmentos[i].match(/(\d{2})\/(\d{2})\/(\d{4})/);
                                        if(matchV) {
                                            $('#fecha_vencimiento').val(`${matchV[3]}-${matchV[2]}-${matchV[1]}`);
                                            break;
                                        }
                                    }
                                }
                            }

                            // 3. EXTRAER PUNTO DE VENTA Y NÚMERO (Por longitud estricta de dígitos de AFIP)
                            let ptoVentaDetectado = false;
                            let nroCompDetectado = false;

                            fragmentos.forEach(frag => {
                                if(!ptoVentaDetectado && /^\d{4,5}$/.test(frag)) {
                                    $('#punto_venta').val(parseInt(frag, 10));
                                    ptoVentaDetectado = true;
                                }
                                if(!nroCompDetectado && /^\d{7,8}$/.test(frag)) {
                                    $('#nro_factura').val(parseInt(frag, 10));
                                    nroCompDetectado = true;
                                }
                            });

                            // Fallback secundario de respaldo por si fallan los bloques limpios
                            if(!ptoVentaDetectado) {
                                let matchPto = textoCompleto.match(/Punto\s*de\s*Venta[\s:]*(\d+)/i);
                                if(matchPto) $('#punto_venta').val(parseInt(matchPto[1], 10));
                            }
                            if(!nroCompDetectado) {
                                let matchNro = textoCompleto.match(/Comp\.\s*Nro[\s:]*(\d+)/i) || textoCompleto.match(/Nro[\s:]*(\d+)/i);
                                if(matchNro) $('#nro_factura').val(parseInt(matchNro[1], 10));
                            }

                            // 4. CLIENTE (Búsqueda inteligente por CUIT)
                            let todosLosCuits = textoCompleto.match(/CUIT[\s:]*(\d{11})/gi) || textoCompleto.match(/\b\d{11}\b/g);
                            if (todosLosCuits && todosLosCuits.length > 0) {
                                for (let i = 0; i < todosLosCuits.length; i++) {
                                    let numeroCuitPDF = todosLosCuits[i].replace(/[^0-9]/g, '');
                                    let encontrado = globalClientesParaMapeo.find(c => {
                                        let cuitDB = c.cuit.replace(/[^0-9]/g, '');
                                        return cuitDB === numeroCuitPDF;
                                    });
                                    if (encontrado) {
                                        $('#cliente_id').val(encontrado.id);
                                        break;
                                    }
                                }
                            }

                            // 5. DETALLE
                            if (textoCompleto.includes("Mant. Acued. Desvío Arijón")) {
                                $('#detalle').val("Mant. Acued. Desvío Arijón; Rep. y reajuste prog.3585 Santo Tomé Certificado N°4-Según OC 4500062956");
                            } else {
                                let matchDetalle = textoCompleto.match(/Producto\s*\/\s*Servicio\s*(.*?)\s*\d+,00/i);
                                if(matchDetalle) {
                                    $('#detalle').val(matchDetalle[1].trim());
                                } else {
                                    $('#detalle').val("Carga automática: Verificar descripción en el PDF adjunto.");
                                }
                            }

                            // 6. VALORES MONETARIOS
                            let matchTotal = textoCompleto.match(/Importe\s*Total[\s:]*\$\s*([\d.,]+)/i) || textoCompleto.match(/Total[\s:]*\$\s*([\d.,]+)/i);
                            let matchNeto = textoCompleto.match(/Importe\s*Neto\s*Gravado[\s:]*\$\s*([\d.,]+)/i);
                            let matchIva = textoCompleto.match(/IVA\s*21%[\s:]*\$\s*([\d.,]+)/i);

                            if(matchNeto) {
                                let neto = matchNeto[1].replace(/\./g, '').replace(',', '.');
                                $('#neto').val(parseFloat(neto).toFixed(2));
                            }
                            if(matchIva) {
                                let iva = matchIva[1].replace(/\./g, '').replace(',', '.');
                                $('#iva').val(parseFloat(iva).toFixed(2));
                            }
                            if(matchTotal) {
                                let total = matchTotal[1].replace(/\./g, '').replace(',', '.');
                                $('#total').val(parseFloat(total).toFixed(2));
                            }

                            // Alerta informativa de éxito
                            let cartel = document.createElement("div");
                            cartel.className = "alert alert-success mt-2 py-1 eval-aviso";
                            cartel.innerHTML = "✨ <b>Análisis completado.</b> Verificá que los campos sean correctos.";
                            $('.eval-aviso').remove();
                            $('#seccionEscaneo').append(cartel);

                        } catch (err) {
                            console.error("Error en parsing: ", err);
                        }
                    });
                });
            });
        };
        fileReader.readAsArrayBuffer(file);
    });
});

function cargarSelects() {
    // Clientes
    $.get('/contable/ajax/clientes.php?accion=listar', function(r) {
        let s = $('#cliente_id').empty().append('<option value="">Seleccione Cliente</option>');
        globalClientesParaMapeo = r.data ? r.data : [];
        globalClientesParaMapeo.forEach(c => s.append(`<option value="${c.id}">${c.nombre}</option>`));
    }, 'json');

    // Tipos de Comprobante
    $.get('/contable/ajax/tipos_comprobante.php?accion=listar', function(r) {
        let s = $('#tipo_comprobante_id').empty().append('<option value="">Seleccione Tipo</option>');
        r.data.forEach(x => s.append(`<option value="${x.id}">${x.nombre}</option>`));
    }, 'json');

    // Centros de Costo
    $.get('/contable/ajax/centros.php?accion=listar', function(r) {
        let s = $('#centro_costo_id').empty().append('<option value="">Seleccione Centro</option>');
        r.data.forEach(x => s.append(`<option value="${x.id}">${x.nombre}</option>`));
    }, 'json');
}

window.abrirModal = function() {
    $('.eval-aviso').remove();
    $('#formFactura input, textarea, select').prop('disabled', false);
    $('#btnGuardar, #seccionEscaneo').show();
    $('#seccionArchivoReal').hide();
    $('#tituloModal').text('Nueva Factura de Venta');
    $('#formFactura')[0].reset();
    $('#estado').val('DEBE'); // Setear por defecto
    $('#id').val('');
    $('#archivo_actual').html('');
    modalFactura.show();
}

window.verFactura = function(data) {
    window.editarFactura(data);
    $('#tituloModal').text('Detalle de Factura (Solo Lectura)');
    $('#formFactura input, textarea, select').prop('disabled', true);
    $('#btnGuardar, #seccionEscaneo').hide();
}

window.editarFactura = function(data) {
    $('.eval-aviso').remove();
    $('#formFactura input, textarea, select').prop('disabled', false);
    $('#btnGuardar').show();
    $('#seccionEscaneo').hide(); 
    $('#seccionArchivoReal').show();
    $('#tituloModal').text('Editar Factura');
    $('#formFactura')[0].reset();
    
    $('#id').val(data.id);
    $('#fecha').val(data.fecha);
    $('#tipo_comprobante_id').val(data.tipo_comprobante_id);
    $('#cliente_id').val(data.cliente_id);
    $('#punto_venta').val(data.punto_venta);
    $('#nro_factura').val(data.nro_factura);
    $('#fecha_vencimiento').val(data.fecha_vencimiento);
    $('#detalle').val(data.detalle);
    $('#neto').val(data.neto);
    $('#iva').val(data.iva);
    $('#total').val(data.total);
    $('#observaciones').val(data.observaciones);
    $('#centro_costo_id').val(data.centro_costo_id);
    $('#estado').val(data.estado ? data.estado : 'DEBE');

    if(data.archivo){
        $('#archivo_actual').html(`<a href="/contable/uploads/facturacion/${data.archivo}" target="_blank" class="btn btn-sm btn-dark">Ver archivo actual</a>`);
    } else {
        $('#archivo_actual').html('');
    }
    
    modalFactura.show();
}

$('#formFactura').submit(function(e) {
    e.preventDefault();
    let formData = new FormData(this);

    $.ajax({
        url: '/contable/ajax/facturacion.php?accion=guardar',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(resp) {
            tabla.ajax.reload();
            modalFactura.hide();
        }
    });
});

window.eliminarFactura = function(id) {
    if (!confirm('¿Eliminar esta factura?')) return;
    if (prompt('Escribí OK para confirmar') !== 'OK') return;

    $.post('/contable/ajax/facturacion.php?accion=eliminar', { id }, function(resp) {
        tabla.ajax.reload();
    });
}
</script>