<?php
// Conexión a la Base de Datos (en /config)
require_once __DIR__ . '/../../config/database.php';

// Inclusiones de diseño y autenticación (en /includes)
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Consultas para los selects
$obras = $conn->query("SELECT id, nombre FROM obras ORDER BY nombre ASC");
$categorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$subcategorias = $conn->query("SELECT id, nombre FROM subcategorias ORDER BY nombre ASC");
$centros = $conn->query("SELECT id, nombre FROM centros_costos ORDER BY nombre ASC");
$clientes = $conn->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
$proveedores = $conn->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC");
$usuarios = $conn->query("SELECT id, usuario FROM usuarios ORDER BY usuario ASC");
$cajas = $conn->query("SELECT id, nombre FROM cajas ORDER BY nombre ASC");
?>
<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-file-earmark-bar-graph-fill me-2 text-muted"></i> Centro General de Reportes e Informes
        </h4>
    </div>

    <!-- PANEL DE CONTROL DE FILTROS -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white fw-bold py-3">
            <i class="bi bi-funnel-fill me-2"></i> Configuración del Informe
        </div>
        <div class="card-body p-4 bg-light">
            <form id="formFiltrosReporte">
                
                <!-- TIPO DE REPORTE A GENERAR -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">1. Seleccione el Tipo de Reporte</label>
                        <select name="tipo_reporte" id="tipo_reporte" class="form-select form-select-lg border-dark fw-semibold">
                            <option value="posicion_iva">Posición Fiscal IVA (IVA Ventas vs. IVA Compras)</option>
                            <option value="gastos_generales">Gastos y Compras (Detallado)</option>
                            <option value="ventas_generales">Ventas y Facturación (Detallado)</option>
                            <option value="ventas_cliente">Ventas por Cliente</option>
                            <option value="compras_proveedor">Compras por Proveedor</option>
                            <option value="ventas_obra">Ventas por Obra / Proyecto</option>
                            <option value="compras_obra">Compras por Obra / Proyecto</option>
                            <option value="ventas_centro">Ventas por Centro de Costo</option>
                            <option value="compras_centro">Compras por Centro de Costo</option>
                            <option value="resumen_anual_gastos">Resumen Anual e Incidencia % de Gastos</option>
                            <option value="historico_cajas">Informe Histórico de Movimientos de Cajas</option>
                            <option value="informe_retenciones">Informe de Retenciones en Ventas</option>
                            <option value="informe_cheques">Informe Histórico de Cheques</option>
                            <option value="historico_usuarios">Auditoría / Movimientos por Usuario</option>
                        </select>
                    </div>
                </div>

                <hr class="text-secondary opacity-25">

                <!-- MATRIZ DE FILTROS DE BÚSQUEDA -->
                <label class="form-label fw-bold mb-3 text-secondary">2. Aplique los Filtros Deseados</label>
                <div class="row g-3">
                    
                    <!-- Rango de Fechas -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Fecha Desde</label>
                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control">
                    </div>

                    <!-- Obra / Proyecto -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Obra / Proyecto</label>
                        <select name="obra_id" id="obra_id" class="form-select">
                            <option value="">-- Todas las Obras --</option>
                            <?php while ($o = $obras->fetch_assoc()): ?>
                                <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Centro de Costos -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Centro de Costos</label>
                        <select name="centro_costo_id" id="centro_costo_id" class="form-select">
                            <option value="">-- Todos los Centros --</option>
                            <?php while ($cc = $centros->fetch_assoc()): ?>
                                <option value="<?= $cc['id'] ?>"><?= htmlspecialchars($cc['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Categoría -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Categoría</label>
                        <select name="categoria_id" id="categoria_id" class="form-select">
                            <option value="">-- Todas las Categorías --</option>
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Subcategoría (Independiente) -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Subcategoría (Independiente)</label>
                        <select name="subcategoria_id" id="subcategoria_id" class="form-select">
                            <option value="">-- Todas las Subcategorías --</option>
                            <?php while ($sub = $subcategorias->fetch_assoc()): ?>
                                <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Cliente -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Cliente</label>
                        <select name="cliente_id" id="cliente_id" class="form-select">
                            <option value="">-- Todos los Clientes --</option>
                            <?php while ($cli = $clientes->fetch_assoc()): ?>
                                <option value="<?= $cli['id'] ?>"><?= htmlspecialchars($cli['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Proveedor -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Proveedor</label>
                        <select name="proveedor_id" id="proveedor_id" class="form-select">
                            <option value="">-- Todos los Proveedores --</option>
                            <?php while ($prov = $proveedores->fetch_assoc()): ?>
                                <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Cajas -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Caja</label>
                        <select name="caja_id" id="caja_id" class="form-select">
                            <option value="">-- Todas las Cajas --</option>
                            <?php while ($cj = $cajas->fetch_assoc()): ?>
                                <option value="<?= $cj['id'] ?>"><?= htmlspecialchars($cj['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Usuario Carga -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Usuario Carga</label>
                        <select name="usuario_id" id="usuario_id" class="form-select">
                            <option value="">-- Todos los Usuarios --</option>
                            <?php while ($u = $usuarios->fetch_assoc()): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['usuario']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Tipo Movimiento de Caja -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Tipo Movimiento Caja</label>
                        <select name="tipo_movimiento" id="tipo_movimiento" class="form-select">
                            <option value="">-- Todos --</option>
                            <option value="INGRESO">INGRESO</option>
                            <option value="EGRESO">EGRESO</option>
                            <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                        </select>
                    </div>

                </div>

                <!-- ACCIONES -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary px-3" onclick="limpiarFiltros()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
                    </button>
                    <button type="button" class="btn btn-dark px-4 fw-bold" onclick="consultarReporte()">
                        <i class="bi bi-search me-1"></i> Generar Reporte
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- TABLA DE RESULTADOS -->
    <div class="card shadow-sm border-0 d-none" id="contenedorResultados">
        <div class="card-body p-4">
            <!-- ENCABEZADO DE PERÍODO Y DETALLES PARA IMPRESIÓN -->
            <div id="infoPeriodoReporte" class="mb-3 p-3 bg-light rounded border border-secondary border-opacity-25">
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle w-100" id="tablaReportes">
                    <thead class="table-dark" id="theadReportes"></thead>
                    <tbody id="tbodyReportes"></tbody>
                    <tfoot class="table-secondary fw-bold" id="tfootReportes"></tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>

<script>
let dataTableReporte = null;

function consultarReporte() {
    let formData = $('#formFiltrosReporte').serialize();

    // Obtener valores de fechas ingresadas
    let fDesde = $('#fecha_desde').val();
    let fHasta = $('#fecha_hasta').val();
    let tipoReporteTexto = $('#tipo_reporte option:selected').text();

    // Formatear el texto del período
    let textoPeriodo = '';
    if (fDesde && fHasta) {
        textoPeriodo = `Período consultado: <strong>${fDesde}</strong> al <strong>${fHasta}</strong>`;
    } else if (fDesde) {
        textoPeriodo = `Período consultado: Desde <strong>${fDesde}</strong> (Sin fecha límite superior)`;
    } else if (fHasta) {
        textoPeriodo = `Período consultado: Hasta <strong>${fHasta}</strong>`;
    } else {
        textoPeriodo = `Período consultado: <strong>Histórico Completo (Sin filtro de fechas)</strong>`;
    }

    $.ajax({
        url: 'obtener_reportes.php',
        type: 'GET',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (!res.status) {
                Swal.fire('Error', res.message || 'Error al procesar la consulta', 'error');
                return;
            }

            $('#contenedorResultados').removeClass('d-none');

            // Renderizar la caja informativa en pantalla
            let htmlInfo = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-primary mb-1">${tipoReporteTexto}</h5>
                        <p class="mb-0 text-muted small"><i class="bi bi-calendar-event me-1"></i> ${textoPeriodo}</p>
                    </div>
                    <div class="text-end text-muted small">
                        <span>Generado el: ${new Date().toLocaleDateString('es-AR')} ${new Date().toLocaleTimeString('es-AR', {hour: '2-digit', minute:'2-digit'})}</span>
                    </div>
                </div>`;
            $('#infoPeriodoReporte').html(htmlInfo);

            // 1. Destruir DataTable previo si existe
            if ($.fn.DataTable.isDataTable('#tablaReportes')) {
                $('#tablaReportes').DataTable().clear().destroy();
            }

            // 2. Limpiar la tabla
            $('#tablaReportes').empty();

            // 3. Construir el <thead>
            let theadHtml = '<thead><tr class="table-dark">';
            res.columns.forEach(col => {
                theadHtml += `<th>${col}</th>`;
            });
            theadHtml += '</tr></thead>';

            // 4. Construir el <tbody>
            let tbodyHtml = '<tbody>';
            if (res.data && res.data.length > 0) {
                res.data.forEach(row => {
                    tbodyHtml += '<tr>';
                    Object.values(row).forEach(val => {
                        tbodyHtml += `<td>${val !== null ? val : '-'}</td>`;
                    });
                    tbodyHtml += '</tr>';
                });
            }
            tbodyHtml += '</tbody>';

            // 5. Construir el <tfoot>
            let tfootHtml = '';
            if (res.total !== undefined && res.total !== null) {
                let colCount = res.columns.length;
                let colspanLeft = colCount > 1 ? colCount - 1 : 1;
                
                tfootHtml = `<tfoot class="table-secondary fw-bold"><tr>`;
                if (colCount > 1) {
                    tfootHtml += `<td colspan="${colspanLeft}" class="text-end">TOTAL ACUMULADO:</td>`;
                    tfootHtml += `<td class="text-end fw-bold">$ ${parseFloat(res.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>`;
                } else {
                    tfootHtml += `<td class="text-end fw-bold">TOTAL: $ ${parseFloat(res.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>`;
                }
                tfootHtml += `</tr></tfoot>`;
            }

            // 6. Inyectar HTML
            $('#tablaReportes').html(theadHtml + tbodyHtml + tfootHtml);

            // Texto sin HTML para las cabeceras de PDF/Print/Excel
            let tituloExportacion = `${tipoReporteTexto} - (${textoPeriodo.replace(/<\/?[^>]+(>|$)/g, "")})`;

            // 7. Volver a inicializar DataTables configurando botones de exportación con la fecha
            dataTableReporte = $('#tablaReportes').DataTable({
                responsive: true,
                destroy: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        title: tituloExportacion
                    },
                    {
                        extend: 'excel',
                        title: tituloExportacion,
                        messageTop: `Fecha de emisión: ${new Date().toLocaleDateString('es-AR')}`
                    },
                    {
                        extend: 'pdf',
                        title: tituloExportacion,
                        messageTop: `Emisión: ${new Date().toLocaleDateString('es-AR')}`
                    },
                    {
                        extend: 'print',
                        title: `<h3 style="text-align:center;">${tipoReporteTexto}</h3>`,
                        messageTop: `<p style="text-align:center; font-weight:bold; margin-bottom: 20px;">${textoPeriodo} | Generado: ${new Date().toLocaleDateString('es-AR')}</p>`
                    }
                ]
            });
        },
        error: function(xhr, status, error) {
            Swal.fire({
                title: 'Error de Servidor',
                text: 'Hubo una falla al procesar la consulta. Verifique la conexión o el backend.',
                icon: 'error'
            });
        }
    });
}

function limpiarFiltros() {
    $('#formFiltrosReporte')[0].reset();
    if ($.fn.DataTable.isDataTable('#tablaReportes')) {
        $('#tablaReportes').DataTable().clear().destroy();
        $('#tablaReportes').empty();
    }
    $('#contenedorResultados').addClass('d-none');
}
</script>