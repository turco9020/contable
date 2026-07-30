<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/sidebar.php';
?>

<div class="content">

    <!-- CABECERA DEL MÓDULO (Estilo unificado) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-receipt text-secondary me-2"></i> Facturación (Ventas)
        </h4>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal()">
            <i class="bi bi-plus-circle me-2"></i> Nueva Factura
        </button>
    </div>

    <!-- Pestañas de Navegación de Estados de Facturas -->
    <ul class="nav nav-tabs mb-3" id="tabFacturas" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold text-dark" id="cobrar-tab" data-bs-toggle="tab" data-bs-target="#cobrar" type="button" role="tab" onclick="filtrarPestaña('POR_COBRAR')">
                <i class="bi bi-wallet2 me-1 text-secondary"></i> Por Cobrar 
                <span class="badge bg-secondary ms-1" id="cant-cobrar">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-dark" id="pagadas-tab" data-bs-toggle="tab" data-bs-target="#pagadas" type="button" role="tab" onclick="filtrarPestaña('PAGADAS')">
                <i class="bi bi-check-circle me-1 text-secondary"></i> Pagadas 
                <span class="badge bg-secondary ms-1" id="cant-pagadas">0</span>
            </button>
        </li>
    </ul>

    <div class="card p-3 shadow-sm">
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
                        <th>Usuario</th>
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

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModal"><i class="bi bi-receipt me-2"></i>Factura Venta</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formFactura" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id">

                    <div id="seccionEscaneo" class="p-3 mb-3 bg-light border rounded text-center">
                        <label class="form-label fw-bold text-secondary mb-2">✨ Asistente de Carga Automática (Subir PDF de AFIP)</label>
                        <input type="file" id="archivo_escanear" name="archivo_escanear" class="form-control form-control-sm mx-auto" style="max-width: 400px;" accept="application/pdf">
                        <div class="form-text mt-1">Subí el comprobante digital original para autocompletar el formulario al instante.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Emisión</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo Comprobante</label>
                            <select name="tipo_comprobante_id" id="tipo_comprobante_id" class="form-control" required></select>
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
                            <select name="cliente_id" id="cliente_id" class="form-control" required></select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-2 fw-semibold text-dark">Centro de Costo</label>
                            <select name="centro_costo_id" id="centro_costo_id" class="form-select border-secondary text-dark" style="background-color: #fef9e7" required></select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Estado de Pago</label>
                            <select name="estado" id="estado" class="form-select" required>
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
                            <input type="file" name="archivo" id="archivo" class="form-control" accept="application/pdf,image/*">
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
let globalClientesParaMapeo = [];
let estadoPestañaActual = 'POR_COBRAR';

$.fn.dataTable.ext.search.push(
    function(settings, data, dataIndex, rowData) {
        let estado = rowData.estado ? rowData.estado.toUpperCase() : 'DEBE';

        if (estadoPestañaActual === 'POR_COBRAR') {
            return (estado === 'DEBE' || estado === 'VER');
        } else if (estadoPestañaActual === 'PAGADAS') {
            return (estado === 'PAGADO');
        }
        return true;
    }
);

document.addEventListener("DOMContentLoaded", function() {
    modalFactura = new bootstrap.Modal(document.getElementById('modalFactura'));

    cargarSelects();

    tabla = $('#tablaFacturas').DataTable({
        ajax: '/contable/ajax/facturacion.php?accion=listar',
        order: [[0, 'desc']],
        autoWidth: false,
        responsive: true,
        deferRender: true,
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: ' Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            {
                extend: 'print',
                text: ' Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            { 
                extend: 'colvis', 
                text: 'Columnas', 
                className: 'btn btn-sm btn-secondary' 
            }
        ],
        drawCallback: function(settings) {
            actualizarBadgesPestañas(settings.aoData);
        },
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
                    let pv = String(row.punto_venta || 0).padStart(5, '0');
                    let nf = String(row.nro_factura || 0).padStart(8, '0');
                    return `${pv}-${nf}`;
                }
            },
            { data: 'cliente_nombre' },
            { data: 'centro_costo_nombre' },
            { 
                data: 'total',
                render: function(d) {
                    return '$ ' + Number(d || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function(row) {
                    if (!row.fecha_vencimiento) return '<span class="text-muted">-</span>';
                    
                    let fechaVto = new Date(row.fecha_vencimiento + 'T00:00:00');
                    let hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);
                    
                    let diferenciaMs = fechaVto - hoy;
                    let diasRestantes = Math.ceil(diferenciaMs / (1000 * 60 * 60 * 24));
                    let estadoFactura = row.estado ? row.estado.toUpperCase() : 'DEBE';
                    
                    if (estadoFactura === 'PAGADO') {
                        return '<span class="text-muted">✔︎</span>';
                    }
                    
                    if (diasRestantes < 0) {
                        let diasPasados = Math.abs(diasRestantes);
                        return `<span class="text-danger fw-bold">Vencida ${diasPasados} ${diasPasados === 1 ? 'día' : 'días'}</span>`;
                    } else if (diasRestantes === 0) {
                        return '<span class="text-warning fw-bold">Vence hoy</span>';
                    } else {
                        return `<span class="text-secondary">${diasRestantes} ${diasRestantes === 1 ? 'día' : 'días'}</span>`;
                    }
                }
            },
            {
                data: 'estado',
                render: function(d) {
                    let texto = d ? d.toUpperCase() : 'DEBE';
                    let colorLed = 'bg-danger';
                    
                    if(texto === 'PAGADO') colorLed = 'bg-success';
                    else if(texto === 'VER') colorLed = 'bg-warning';
                    
                    return `
                        <div class="d-inline-flex align-items-center text-secondary fw-semibold" style="font-size: 0.9rem;">
                            <span class="rounded-circle ${colorLed}" style="width: 8px; height: 8px; display: inline-block; margin-right: 8px;"></span>
                            ${texto}
                        </div>
                    `;
                }
            },
            {
                data: 'usuario_nombre',
                visible: true,
                render: function(d) {
                    return d ? `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i> ${d}</span>` : '<span class="text-muted">Sistema</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(d) {
                    let btnArchivo = d.archivo ? `<a href="/contable/uploads/facturacion/${d.archivo}" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver Adjunto"><i class="bi bi-file-earmark-pdf"></i></a>` : '';
                    return `
                        <div class="d-inline-flex gap-1">
                            ${btnArchivo}
                            <button class="btn btn-sm btn-outline-secondary" title="Ver Factura" onclick='verFactura(${JSON.stringify(d)})'>
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" title="Editar Factura" onclick='editarFactura(${JSON.stringify(d)})'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar Factura" onclick="eliminarFactura(${d.id})">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $(window).on('resize', function () {
        tabla.columns.adjust();
    });

    $('#neto, #iva').on('input', function(){
        let n = parseFloat($('#neto').val()) || 0;
        let i = parseFloat($('#iva').val()) || 0;
        $('#total').val((n + i).toFixed(2));
    });

    // ==========================================
    // PARSER AFIP MEDIANTE PDF.JS (REVISADO)
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
                        let fragmentos = textContent.items.map(item => item.str.trim()).filter(str => str !== "");
                        let textoCompleto = fragmentos.join(" ");
                        
                        $('#seccionEscaneo label').text(labelOriginal);
                        
                        try {
                            // 1. FECHA EMISIÓN
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

                            // 2. FECHA VENCIMIENTO (REPARADO BUG)
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

                            // 3. PUNTO DE VENTA Y NRO COMPROBANTE
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

                            if(!ptoVentaDetectado) {
                                let matchPto = textoCompleto.match(/Punto\s*de\s*Venta[\s:]*(\d+)/i);
                                if(matchPto) $('#punto_venta').val(parseInt(matchPto[1], 10));
                            }
                            if(!nroCompDetectado) {
                                let matchNro = textoCompleto.match(/Comp\.\s*Nro[\s:]*(\d+)/i) || textoCompleto.match(/Nro[\s:]*(\d+)/i);
                                if(matchNro) $('#nro_factura').val(parseInt(matchNro[1], 10));
                            }

                            // 4. CLIENTE (POR CUIT)
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
                            let matchDetalle = textoCompleto.match(/Producto\s*\/\s*Servicio\s*(.*?)\s*\d+,00/i) || 
                                               textoCompleto.match(/Descripción\s*(.*?)\s*(?:Subtotal|Importe)/i) ||
                                               textoCompleto.match(/c\/IVA\s*(.*?)\s*Importe\s*Otros/i);

                            if (matchDetalle && matchDetalle[1]) {
                                $('#detalle').val(matchDetalle[1].trim());
                            } else {
                                let fragDetalle = fragmentos.find(f => f.includes("MantAcued") || (f.length > 30 && !f.includes("Razón Social") && !f.includes("Domicilio")));
                                $('#detalle').val(fragDetalle ? fragDetalle.trim() : "Carga automática AFIP: Verificar descripción interna.");
                            }

                            // 6. IMPORTES
                            let matchTotal = textoCompleto.match(/(?:Importe\s*Total|Total)[\s:]*\$?[\s:]*([\d.,]+)/i);
                            let matchNeto = textoCompleto.match(/(?:Neto\s*Gravado|Neto)[\s:]*\$?[\s:]*([\d.,]+)/i);
                            let matchIva = textoCompleto.match(/IVA\s*(?:21|10\.5)?%[\s:]*\$?[\s:]*([\d.,]+)/i);

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

                            let cartel = document.createElement("div");
                            cartel.className = "alert alert-success mt-2 py-1 eval-aviso";
                            cartel.innerHTML = "✨ <b>Análisis completado.</b> Verificá los campos antes de guardar.";
                            $('.eval-aviso').remove();
                            $('#seccionEscaneo').append(cartel);

                        } catch (err) {
                            console.error("Error procesando contenido del PDF: ", err);
                        }
                    });
                });
            });
        };
        fileReader.readAsArrayBuffer(file);
    });
});

function cargarSelects() {
    $.get('/contable/ajax/clientes.php?accion=listar', function(r) {
        let s = $('#cliente_id').empty().append('<option value="">Seleccione Cliente</option>');
        globalClientesParaMapeo = r.data ? r.data : [];
        globalClientesParaMapeo.forEach(c => s.append(`<option value="${c.id}">${c.nombre}</option>`));
    }, 'json');

    $.get('/contable/ajax/tipos_comprobante.php?accion=listar', function(r) {
        let s = $('#tipo_comprobante_id').empty().append('<option value="">Seleccione Tipo</option>');
        if(r.data) r.data.forEach(x => s.append(`<option value="${x.id}">${x.nombre}</option>`));
    }, 'json');

    $.get('/contable/ajax/centros.php?accion=listar', function(r) {
        let s = $('#centro_costo_id').empty().append('<option value="">Seleccione Centro</option>');
        if(r.data) r.data.forEach(x => s.append(`<option value="${x.id}">${x.nombre}</option>`));
    }, 'json');
}

window.abrirModal = function() {
    $('.eval-aviso').remove();
    $('#formFactura').find('input, textarea, select').prop('disabled', false);
    $('#btnGuardar, #seccionEscaneo').show();
    $('#seccionArchivoReal').hide();
    $('#tituloModal').text('Nueva Factura de Venta');
    $('#formFactura')[0].reset();
    $('#estado').val('DEBE');
    $('#id').val('');
    $('#archivo_actual').html('');
    modalFactura.show();
}

window.verFactura = function(data) {
    window.editarFactura(data);
    $('#tituloModal').text('Detalle de Factura (Solo Lectura)');
    $('#formFactura').find('input, textarea, select').prop('disabled', true);
    $('#btnGuardar, #seccionEscaneo').hide();
}

window.editarFactura = function(data) {
    $('.eval-aviso').remove();
    $('#formFactura').find('input, textarea, select').prop('disabled', false);
    $('#btnGuardar').show();
    $('#seccionEscaneo').hide(); 
    $('#seccionArchivoReal').show();
    $('#tituloModal').text('Editar Factura');
    $('#formFactura')[0].reset();
    
    $('#id').val(data.id);
    $('#fecha').val(data.fecha);
    $('#punto_venta').val(data.punto_venta);
    $('#nro_factura').val(data.nro_factura);
    $('#fecha_vencimiento').val(data.fecha_vencimiento);
    $('#detalle').val(data.detalle);
    $('#neto').val(data.neto);
    $('#iva').val(data.iva);
    $('#total').val(data.total);
    $('#observaciones').val(data.observaciones);
    $('#estado').val(data.estado ? data.estado : 'DEBE');

    setTimeout(() => {
        $('#tipo_comprobante_id').val(data.tipo_comprobante_id);
        $('#cliente_id').val(data.cliente_id);
        $('#centro_costo_id').val(data.centro_costo_id);
    }, 100);

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

    let inputReal = document.getElementById('archivo');
    let inputEscaneo = document.getElementById('archivo_escanear');
    
    if ((!inputReal || inputReal.files.length === 0) && (inputEscaneo && inputEscaneo.files.length > 0)) {
        formData.set('archivo', inputEscaneo.files[0]);
    }

    $.ajax({
        url: '/contable/ajax/facturacion.php?accion=guardar',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(resp) {
            tabla.ajax.reload(null, false);
            modalFactura.hide();
        }
    });
});

window.eliminarFactura = function(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer y eliminará el registro contable.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#212529', 
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/contable/ajax/facturacion.php?accion=eliminar', { id: id }, function(resp) {
                Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: 'El comprobante fue borrado con éxito.',
                    timer: 1500,
                    showConfirmButton: false
                });
                tabla.ajax.reload(null, false);
            }).fail(function() {
                Swal.fire('Error', 'No se pudo eliminar el registro contable.', 'error');
            });
        }
    });
}

window.filtrarPestaña = function(tipoPestaña) {
    estadoPestañaActual = tipoPestaña;
    tabla.draw();
}

function actualizarBadgesPestañas(datosFilas) {
    let contadorCobrar = 0;
    let contadorPagadas = 0;

    datosFilas.forEach(function(fila) {
        let factura = fila._aData;
        let estado = factura.estado ? factura.estado.toUpperCase() : 'DEBE';

        if (estado === 'DEBE' || estado === 'VER') {
            contadorCobrar++;
        } else if (estado === 'PAGADO') {
            contadorPagadas++;
        }
    });

    $('#cant-cobrar').text(contadorCobrar);
    $('#cant-pagadas').text(contadorPagadas);
}
</script>