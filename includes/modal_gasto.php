<!-- MODAL -->
<div class="modal fade" id="modalGasto" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="tituloModal"><i class="bi bi-calculator me-2"></i>Gestión de Gasto</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formGasto" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id">

                    <!-- SECCIÓN 1: CLASIFICACIÓN Y TIEMPO -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">1. Clasificación del Gasto</h6>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Centro de Costo</label>
                            <select name="centro_costo_id" id="centro_costo_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Obra</label>
                            <select name="obra_id" id="obra_id" class="form-select"></select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Categoría</label>
                            <select name="categoria_id" id="categoria_id" class="form-select"></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Subcategoría</label>
                            <select name="subcategoria_id" id="subcategoria_id" class="form-select"></select>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: COMPROBANTE Y PAGO -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">2. Datos del Comprobante y Origen del Pago</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Proveedor</label>
                            <select name="proveedor_id" id="proveedor_id" class="form-select"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tipo Comprobante</label>
                            <select name="tipo_comprobante_id" id="tipo_comprobante_id" class="form-select"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Número de Comprobante</label>
                            <input type="text" name="numero_comprobante" id="numero_comprobante" class="form-control" placeholder="Ej: 0001-00004321">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Medio de Pago</label>
                            <select name="medio_pago_id" id="medio_pago_id" class="form-select"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Caja Afectada</label>
                            <select id="caja_id" name="caja_id" class="form-select" required>
                                <option value="">Seleccionar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Archivo Adjunto</label>
                            <input type="file" name="archivo" id="archivo" class="form-control">
                            <div id="archivo_actual" class="mt-2"></div>
                            <button type="button" id="btnEliminarArchivo" class="btn btn-sm btn-outline-danger w-100 mt-2" style="display:none;">
                                <i class="bi bi-trash me-1"></i> Eliminar archivo actual
                            </button>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Detalle / Concepto</label>
                            <input type="text" name="detalle" id="detalle" class="form-control" placeholder="Breve descripción del egreso...">
                        </div>
                    </div>

                    <!-- SECCIÓN 3: LIQUIDACIÓN DE IMPORTES -->
                    <div class="row g-2 p-3 bg-light rounded-3 border align-items-end mb-4">
                        <div class="col-12 mb-1">
                            <h6 class="text-uppercase text-dark fw-bold small"><i class="bi bi-cash-coin me-1"></i> Desglose de Importes</h6>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Neto Gravado</label>
                            <input name="neto" id="neto" class="form-control text-end" placeholder="0,00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">IVA</label>
                            <input name="iva" id="iva" class="form-control text-end" placeholder="0,00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Retención IIBB</label>
                            <input name="ret_iibb" id="ret_iibb" class="form-control text-end" placeholder="0,00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Otros Tributos</label>
                            <input name="otros_tributos" id="otros_tributos" class="form-control text-end" placeholder="0,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark mb-1">Total del Gasto</label>
                            <input name="total" id="total" class="form-control text-end fw-bold bg-white text-dark fs-5 border-dark" readonly placeholder="$ 0,00">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark px-5">Guardar Registro</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>