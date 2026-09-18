let datosPrevisualizados = [];

$(document).ready(function() {

    // 1. Analizar archivo CSV (Paso 1)
    $('#formUploadMateriales').submit(function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        formData.append('action', 'preview');

        $.ajax({
            url: '/contable/modules/presupuestos/ajax/importar_materiales.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function() {
                $('#tbodyPreviewMateriales').html('<tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm text-dark me-2"></div>Procesando archivo...</td></tr>');
                $('#seccionPrevisualizacion').show();
                $('#btnConfirmarImportacion').hide();
            },
            success: function(res) {
                if (res.status === 'success') {
                    datosPrevisualizados = res.data;

                    $('#lblNuevos').text(`${res.totales.nuevos} Nuevos`);
                    $('#lblActualizados').text(`${res.totales.actualizados} a Actualizar`);

                    let html = '';
                    res.data.forEach(item => {
                        let badgeAccion = item.existe 
                            ? '<span class="badge bg-primary"><i class="bi bi-arrow-repeat me-1"></i>Actualizar</span>'
                            : '<span class="badge bg-success"><i class="bi bi-plus-circle me-1"></i>Nuevo</span>';

                        let formatMoneda = (v) => '$ ' + parseFloat(v || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                        html += `
                            <tr>
                                <td>${badgeAccion}</td>
                                <td>${item.id ? item.id : '-'}</td>
                                <td class="fw-bold">${item.nombre}</td>
                                <td>${item.unidad_medida}</td>
                                <td class="text-end">${formatMoneda(item.precio_bulto)}</td>
                                <td class="text-end fw-bold">${formatMoneda(item.precio_unitario)}</td>
                                <td>${item.proveedor || '-'}</td>
                            </tr>
                        `;
                    });

                    $('#tbodyPreviewMateriales').html(html);
                    $('#btnConfirmarImportacion').show();
                } else {
                    Swal.fire('Error', res.message || 'Error al procesar el archivo.', 'error');
                    $('#seccionPrevisualizacion').hide();
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                $('#seccionPrevisualizacion').hide();
            }
        });
    });

    // 2. Confirmar e importar registros a BD (Paso 2)
    $('#btnConfirmarImportacion').click(function() {
        if (!datosPrevisualizados.length) return;

        $.ajax({
            url: '/contable/modules/presupuestos/ajax/importar_materiales.php',
            type: 'POST',
            data: {
                action: 'confirm_import',
                items: JSON.stringify(datosPrevisualizados)
            },
            dataType: 'json',
            beforeSend: function() {
                $('#btnConfirmarImportacion').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');
            },
            success: function(res) {
                $('#btnConfirmarImportacion').prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> Confirmar e Importar Registros');
                
                if (res.status === 'success') {
                    Swal.fire({
                        title: '¡Importación Completa!',
                        text: `Se procesaron ${res.imported_count} registros correctamente.`,
                        icon: 'success',
                        confirmButtonColor: '#212529'
                    });

                    // Cerrar modal y resetear formulario
                    $('#modalImportarMateriales').modal('hide');
                    $('#formUploadMateriales')[0].reset();
                    $('#seccionPrevisualizacion').hide();
                    datosPrevisualizados = [];

                    // Recargar DataTables de materiales si está presente
                    if (typeof tabla !== 'undefined') {
                        tabla.ajax.reload(null, false);
                    }
                } else {
                    Swal.fire('Error', res.message || 'Ocurrió un error al guardar.', 'error');
                }
            },
            error: function() {
                $('#btnConfirmarImportacion').prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> Confirmar e Importar Registros');
                Swal.fire('Error', 'No se pudo completar la importación.', 'error');
            }
        });
    });
});