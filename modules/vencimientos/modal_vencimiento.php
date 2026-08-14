<!-- MODAL GESTIÓN DE VENCIMIENTOS (Estilo Unificado) -->
<div class="modal fade" id="modalVencimiento" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModalVencimiento">
                    <i class="bi bi-calendar-check me-2"></i>Gestión de Vencimiento / Compromiso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formVencimiento" action="acciones.php?accion=guardar" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="vencimiento_id">

                    <!-- SECCIÓN 1: DATOS BÁSICOS Y CLASIFICACIÓN -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">1. Información General y Clasificación</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Concepto / Título <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" id="titulo" class="form-control fw-bold" placeholder="Ej: Factura Litoral Gas / Patente" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Categoría <span class="text-danger">*</span></label>
                            <select name="categoria_id" id="categoria_id" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <?php
                                $q_cat = mysqli_query($conexion, "SELECT id, nombre FROM categorias ORDER BY nombre ASC");
                                if ($q_cat) {
                                    while ($c = mysqli_fetch_assoc($q_cat)) {
                                        echo "<option value='{$c['id']}'>" . htmlspecialchars($c['nombre']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Subcategoría <span class="text-danger">*</span></label>
                            <select name="subcategoria_id" id="subcategoria_id" class="form-select" disabled required>
                                <option value="">-- Elija Categoría --</option>
                            </select>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: PROVEEDOR Y DOCUMENTACIÓN -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">2. Proveedor y Adjuntos</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Proveedor</label>
                            <select name="proveedor_id" id="proveedor_id" class="form-select">
                                <option value="">-- Seleccionar Proveedor --</option>
                                <?php
                                $q_prov = mysqli_query($conexion, "SELECT id, nombre, cuit FROM proveedores ORDER BY nombre ASC");
                                if ($q_prov) {
                                    while ($p = mysqli_fetch_assoc($q_prov)) {
                                        $cuit_str = $p['cuit'] ? " ({$p['cuit']})" : "";
                                        echo "<option value='{$p['id']}'>" . htmlspecialchars($p['nombre']) . $cuit_str . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Factura / Comprobante (Adjunto)</label>
                            <input type="file" name="archivo" id="archivo" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
                        </div>
                    </div>

                    <!-- SECCIÓN 3: IMPORTE, VENCIMIENTO Y CUOTAS -->
                    <div class="row g-3 p-3 bg-light rounded-3 border mb-4">
                        <div class="col-12 mb-1">
                            <h6 class="text-uppercase text-dark fw-bold small"><i class="bi bi-cash-stack me-1"></i> Condiciones de Pago e Importes</h6>
                        </div>
                        
                        <!-- Switch Cuotas -->
                        <div class="col-12 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="es_cuotas" name="es_cuotas" value="1">
                                <label class="form-check-label fw-bold small text-dark" for="es_cuotas">¿Es una compra/gasto financiado en cuotas?</label>
                            </div>
                        </div>

                        <!-- Panel Config Cuotas -->
                        <div class="row g-3 d-none w-100 m-0 p-0 mb-3" id="bloque_cuotas">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Cantidad de Cuotas</label>
                                <input type="number" name="total_cuotas" id="total_cuotas" class="form-control" value="1" min="1" max="120">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Modo de Cálculo</label>
                                <select name="modo_calculo" id="modo_calculo" class="form-select">
                                    <option value="total">Ingresar Monto Total</option>
                                    <option value="cuota">Ingresar Monto por Cuota</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" id="lbl_monto_calculado">Monto por Cuota estimado</label>
                                <input type="text" class="form-control bg-white fw-bold text-dark" id="monto_calculado_preview" readonly value="$ 0,00">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold" id="lbl_monto">Monto ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="monto" id="monto" class="form-control fw-bold fs-5 text-dark" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">1° Vencimiento <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Días Aviso Previo</label>
                            <input type="number" name="dias_aviso" id="dias_aviso" class="form-control" value="7" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Observaciones / Detalle</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="2" placeholder="Notas adicionales sobre este compromiso..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark px-5">Guardar Vencimiento</button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Selector específico para evitar colisión con modalGasto
    $('#formVencimiento #categoria_id').change(function() {
        let catId = $(this).val();
        let $sub = $('#formVencimiento #subcategoria_id');

        if (catId) {
            $.ajax({
                url: '../../ajax/get_subcategorias.php',
                type: 'GET',
                data: { categoria_id: catId },
                dataType: 'json',
                success: function(data) {
                    $sub.empty().append('<option value="">-- Seleccionar Subcategoría --</option>');
                    if (data && data.length > 0) {
                        $.each(data, function(i, item) {
                            $sub.append(`<option value="${item.id}">${item.nombre}</option>`);
                        });
                        $sub.prop('disabled', false);
                    } else {
                        $sub.append('<option value="">-- Sin Subcategorías --</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    $sub.empty().append('<option value="">-- Error al cargar --</option>').prop('disabled', true);
                }
            });
        } else {
            $sub.empty().append('<option value="">-- Elija Categoría --</option>').prop('disabled', true);
        }
    });

    // Lógica de cálculo de Cuotas
    const formV = document.getElementById('formVencimiento');
    const chkCuotas = formV.querySelector('#es_cuotas');
    const bloqueCuotas = formV.querySelector('#bloque_cuotas');
    const inputTotalCuotas = formV.querySelector('#total_cuotas');
    const selectModo = formV.querySelector('#modo_calculo');
    const inputMonto = formV.querySelector('#monto');
    const lblMonto = formV.querySelector('#lbl_monto');
    const previewCalculo = formV.querySelector('#monto_calculado_preview');
    const lblCalculado = formV.querySelector('#lbl_monto_calculado');

    chkCuotas.addEventListener('change', function () {
        if (this.checked) {
            bloqueCuotas.classList.remove('d-none');
            if (parseInt(inputTotalCuotas.value) < 2) inputTotalCuotas.value = 2;
        } else {
            bloqueCuotas.classList.add('d-none');
            inputTotalCuotas.value = 1;
            lblMonto.innerHTML = 'Monto ($) <span class="text-danger">*</span>';
        }
        calcularMontoCuotas();
    });

    inputMonto.addEventListener('input', calcularMontoCuotas);
    inputTotalCuotas.addEventListener('input', calcularMontoCuotas);
    selectModo.addEventListener('change', calcularMontoCuotas);

    function calcularMontoCuotas() {
        if (!chkCuotas.checked) return;

        const cuotas = parseInt(inputTotalCuotas.value) || 1;
        const valMonto = parseFloat(inputMonto.value) || 0;

        if (selectModo.value === 'total') {
            lblMonto.innerHTML = 'Monto TOTAL ($) <span class="text-danger">*</span>';
            lblCalculado.textContent = 'Monto por Cuota';
            const cuotaValor = cuotas > 0 ? (valMonto / cuotas) : 0;
            previewCalculo.value = "$ " + cuotaValor.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            lblMonto.innerHTML = 'Monto POR CUOTA ($) <span class="text-danger">*</span>';
            lblCalculado.textContent = 'Monto TOTAL Final';
            const totalValor = valMonto * cuotas;
            previewCalculo.value = "$ " + totalValor.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }
});
</script>