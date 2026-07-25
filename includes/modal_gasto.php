<!-- MODAL -->
<div class="modal fade" id="modalGasto">
<div class="modal-dialog modal-xl">
<div class="modal-content">

<div class="modal-header">
<h5>Gasto</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form id="formGasto">

<input type="hidden" name="id" id="id">

<div class="row">

<div class="col-md-3">
<label>Fecha</label>
<input type="date" name="fecha" id="fecha" class="form-control mb-2">
</div>

<div class="col-md-3">
<label>Centro</label>
<select name="centro_costo_id" id="centro_costo_id" class="form-control mb-2" required></select>
</div>

<div class="col-md-3">
<label>Obra</label>
<select name="obra_id" id="obra_id" class="form-control mb-2"></select>
</div>

<div class="col-md-3">
<label>Categoría</label>
<select name="categoria_id" id="categoria_id" class="form-control mb-2"></select>
</div>

<div class="col-md-3">
<label>Subcategoría</label>
<select name="subcategoria_id" id="subcategoria_id" class="form-control mb-2"></select>
</div>

<div class="col-md-4">
<label>Tipo comprobante</label>
<select name="tipo_comprobante_id" id="tipo_comprobante_id" class="form-control mb-2"></select>
</div>

<div class="col-md-4">
<label>Número</label>
<input type="text" name="numero_comprobante" id="numero_comprobante" class="form-control mb-2">
</div>

<div class="col-md-4">
<label>Proveedor</label>
<select name="proveedor_id" id="proveedor_id" class="form-control mb-2"></select>
</div>

<div class="col-md-4">
<label>Medio de pago</label>
<select name="medio_pago_id" id="medio_pago_id" class="form-control mb-2"></select>
</div>

<div class="col-md-4">
<label>Caja</label>
<select id="caja_id" name="caja_id" class="form-control" required>
<option value="">Seleccionar</option>
</select>
</div>

<div class="col-md-4">
<label>Detalle</label>
<input type="text" name="detalle" id="detalle" class="form-control mb-2">
</div>

<div class="col-md-4">
<label>Comprobante</label>
<input type="file" name="archivo" id="archivo" class="form-control mb-2">
<div id="archivo_actual" class="mb-2"></div>
<button type="button" id="btnEliminarArchivo" class="btn btn-sm btn-outline-danger mt-2" style="display:none;">
    Eliminar archivo
</button>
</div>

<div class="col-md-2"><input name="neto" id="neto" class="form-control mb-2" placeholder="Neto"></div>
<div class="col-md-2"><input name="iva" id="iva" class="form-control mb-2" placeholder="IVA"></div>
<div class="col-md-2"><input name="ret_iibb" id="ret_iibb" class="form-control mb-2" placeholder="IIBB"></div>
<div class="col-md-2"><input name="otros_tributos" id="otros_tributos" class="form-control mb-2" placeholder="Otros"></div>
<div class="col-md-2"><input name="total" id="total" class="form-control mb-2 fw-bold" readonly></div>

</div>

<button class="btn btn-dark w-100">Guardar</button>

</form>

</div>
</div>
</div>
</div>