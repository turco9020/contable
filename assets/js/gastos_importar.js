$(document).ready(function() {
    let comprobantesProcesados = [];

    // Analizar CSV al enviar el formulario
    $('#formUploadAFIP').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append('action', 'preview');

        $.ajax({
            url: '/contable/ajax/importar_afip.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    comprobantesProcesados = res.data;
                    $('#lblNuevos').text(`${res.totales.nuevos} Nuevos`);
                    $('#lblDuplicados').text(`${res.totales.duplicados} Duplicados (se omitirán)`);
                    
                    let html = '';
                    res.data.forEach(item => {
                        let filaClase = item.es_duplicado ? 'table-secondary opacity-50' : '';
                        let badgeEstado = item.es_duplicado 
                            ? '<span class="badge bg-secondary">DUPLICADO</span>' 
                            : '<span class="badge bg-success">NUEVO</span>';
                        
                        let badgeVal = item.estado_validacion === 'PENDIENTE'
                            ? '<span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> PENDIENTE (>= $800k)</span>'
                            : '<span class="badge bg-light text-dark">APROBADO</span>';

                        let tipoComp = item.tipo_comprobante_id || item.tipo_comprobante || '-';
                        let numComp  = item.numero_comprobante || `${String(item.punto_venta).padStart(4, '0')}-${String(item.numDesde).padStart(8, '0')}`;

                        html += `<tr class="${filaClase}">
                            <td>${badgeEstado}</td>
                            <td>${item.fecha}</td>
                            <td>${tipoComp}</td>
                            <td>${numComp}</td>
                            <td>${item.cuit_emisor}</td>
                            <td>${item.razon_social}</td>
                            <td class="text-end font-monospace">$${parseFloat(item.monto_total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>
                            <td>${badgeVal}</td>
                        </tr>`;
                    });

                    $('#tbodyPreviewAFIP').html(html);
                    $('#seccionPrevisualizacion').show();
                    $('#btnConfirmarImportacion').show();
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Ocurrió un error al procesar el archivo. Revisa la consola (F12).');
                console.error(error, xhr.responseText);
            }
        });
    });

    // Confirmar e Importar
    $('#btnConfirmarImportacion').on('click', function() {
        let nuevosItems = comprobantesProcesados.filter(i => !i.es_duplicado);
        if (nuevosItems.length === 0) {
            alert('No hay comprobantes nuevos para importar.');
            return;
        }

        $.ajax({
            url: '/contable/ajax/importar_afip.php',
            type: 'POST',
            data: {
                action: 'confirm_import',
                items: JSON.stringify(nuevosItems)
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    alert(`¡Éxito! Se importaron ${res.imported_count} comprobantes correctamente.`);
                    $('#modalImportarAFIP').modal('hide');
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Ocurrió un error al guardar los gastos. Revisa la consola (F12).');
                console.error(error, xhr.responseText);
            }
        });
    });
});