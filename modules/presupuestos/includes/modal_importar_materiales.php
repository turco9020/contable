<!-- Modal Importación de Materiales CSV -->
<div class="modal fade" id="modalImportarMateriales" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-file-earmark-spreadsheet me-2"></i> Importar Materiales (CSV)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <!-- GUÍA PASO A PASO -->
        <div class="alert alert-light border border-secondary-subtle shadow-sm mb-4 p-3">
          <h6 class="fw-bold text-dark mb-2">
            <i class="bi bi-info-circle-fill text-primary me-2"></i>¿Cómo preparar y guardar tu archivo correctamente?
          </h6>
          <ol class="small text-secondary mb-2 ps-3">
            <li class="mb-1">
              Descarga o abre tu archivo de Excel con los materiales (puedes usar la función <strong>"Exportar Excel"</strong> de la tabla como plantilla).
            </li>
            <li class="mb-1">
              Asegúrate de respetar las columnas requeridas: 
              <span class="badge bg-secondary">ID (Opcional)</span> 
              <span class="badge bg-secondary">Material / Nombre</span> 
              <span class="badge bg-secondary">Precio Bulto</span> 
              <span class="badge bg-secondary">Unidad Medida</span> 
              <span class="badge bg-secondary">Precio Unitario</span> 
              <span class="badge bg-secondary">Proveedor</span>
            </li>
            <li class="mb-1">
              En Excel, ve a <strong>Archivo &gt; Guardar como</strong>.
            </li>
            <li>
              En el desplegable de tipo de archivo, selecciona <strong>CSV (delimitado por comas) (*.csv)</strong> o <strong>CSV UTF-8 (*.csv)</strong> y guarda la plantilla.
            </li>
          </ol>
          <div class="small bg-warning-subtle text-warning-emphasis p-2 rounded border border-warning-subtle">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Nota:</strong> Si incluyes el <code>ID</code> o el <code>Nombre</code> de un material ya existente, el sistema lo <strong>actualizará</strong> en lugar de crear uno nuevo.
          </div>
        </div>

        <!-- Paso 1: Seleccionar Archivo -->
        <form id="formUploadMateriales" enctype="multipart/form-data" class="mb-4">
          <div class="row g-3 align-items-end">
            <div class="col-md-9">
              <label for="archivo_csv" class="form-label fw-bold mb-1">Seleccionar archivo CSV guardado:</label>
              <input type="file" class="form-control" id="archivo_csv" name="archivo_csv" accept=".csv" required>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-dark w-100">
                <i class="bi bi-search me-1"></i> Analizar Archivo
              </button>
            </div>
          </div>
        </form>

        <!-- Paso 2: Previsualización -->
        <div id="seccionPrevisualizacion" style="display: none;">
          <div class="alert alert-info d-flex justify-content-between align-items-center py-2 mb-3">
            <div>
              <span class="badge bg-success fs-6 me-1" id="lblNuevos">0 Nuevos</span>
              <span class="badge bg-primary fs-6" id="lblActualizados">0 a Actualizar</span>
            </div>
            <small class="text-muted">Verifica los cambios en la lista antes de confirmar.</small>
          </div>

          <!-- Tabla Previsualización -->
          <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
            <table class="table table-sm table-hover border align-middle text-nowrap">
              <thead class="table-dark sticky-top">
                <tr>
                  <th>Acción</th>
                  <th>ID</th>
                  <th>Material / Nombre</th>
                  <th>Unidad Medida</th>
                  <th class="text-end">Precio Bulto</th>
                  <th class="text-end">Precio Unitario</th>
                  <th>Proveedor</th>
                </tr>
              </thead>
              <tbody id="tbodyPreviewMateriales"></tbody>
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