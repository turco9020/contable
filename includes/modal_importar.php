<!-- Modal Importación AFIP -->
<div class="modal fade" id="modalImportarAFIP" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="tituloModal">
          <i class="bi bi-file-earmark-spreadsheet me-2"></i> Importar Comprobantes AFIP (CSV)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <!-- Paso 1: Seleccionar Archivo -->
        <form id="formUploadAFIP" enctype="multipart/form-data" class="mb-4">
          <div class="row g-3 align-items-end">
            <div class="col-md-9">
              <label for="archivo_afip" class="form-label fw-bold mb-1">Seleccionar archivo CSV descargado de AFIP:</label>
              <input type="file" class="form-control" id="archivo_afip" name="archivo_afip" accept=".csv" required>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-dark w-100">
                <i class="bi bi-search me-1"></i> Analizar Archivo
              </button>
            </div>
          </div>
        </form>

        <!-- Paso 2: Previsualización de Comprobantes -->
        <div id="seccionPrevisualizacion" style="display: none;">
          <div class="alert alert-info d-flex justify-content-between align-items-center py-2 mb-3">
            <div>
              <span class="badge bg-success fs-6 me-1" id="lblNuevos">0 Nuevos</span>
              <span class="badge bg-secondary fs-6" id="lblDuplicados">0 Duplicados (se omitirán)</span>
            </div>
            <small>Los gastos mayores a $800.000 ingresarán como <strong>PENDIENTE DE VALIDACIÓN</strong>.</small>
          </div>

          <!-- Tabla Previsualización -->
          <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
            <table class="table table-sm table-hover border align-middle text-nowrap">
              <thead class="table-dark sticky-top">
                <tr>
                  <th>Estado</th>
                  <th>Fecha</th>
                  <th>Tipo</th>
                  <th>Comprobante</th>
                  <th>CUIT</th>
                  <th>Proveedor</th>
                  <th class="text-end">Monto Total</th>
                  <th>Validación Requ.</th>
                </tr>
              </thead>
              <tbody id="tbodyPreviewAFIP"></tbody>
            </table>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnConfirmarImportacion" style="display:none;">
          <i class="bi bi-cloud-upload me-1"></i> Confirmar e Importar Registros
        </button>
      </div>
    </div>
  </div>
</div>