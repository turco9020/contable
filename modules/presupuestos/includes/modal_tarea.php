<!-- MODAL APU -->
<div class="modal fade" id="modalTarea" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <!-- Se agrega modal-fullscreen-xl-down para pantallas chicas y un style max-width para mayor amplitud -->
    <div class="modal-dialog modal-xl modal-fullscreen-xl-down modal-dialog-centered" style="max-width: 95vw;">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalTareaTitle">
                    <i class="bi bi-tools me-2"></i>Análisis de Precio Unitario (APU)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body bg-light">
                <input type="hidden" id="tarea_id" value="0">
                
                <!-- DATOS GENERALES -->
                <div class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Código</label>
                        <input type="text" id="tarea_codigo" class="form-control" placeholder="Ej: H-01">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Descripción / Nombre de Tarea *</label>
                        <input type="text" id="tarea_nombre" class="form-control" placeholder="Ej: Hormigón Pobre E=10cm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Unidad *</label>
                        <select id="tarea_unidad" class="form-select">
                            <option value="M3">M3</option>
                            <option value="M2">M2</option>
                            <option value="ML">ML</option>
                            <option value="GL">GL</option>
                            <option value="UN">UN</option>
                            <option value="KG">KG</option>
                            <option value="TN">TN</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-none">
                        <label class="form-label fw-semibold">Rendimiento</label>
                        <input type="number" step="0.01" id="tarea_rendimiento" class="form-control text-end" value="1.00">
                    </div>
                </div>

                <!-- TABLA DESGLOSE CON RUBROS FIJOS -->
                <div class="table-responsive bg-white rounded border shadow-sm">
                    <table class="table table-bordered align-middle mb-0" id="tabla_desglose" style="width: 100%; min-width: 900px;">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 32%;">Recurso / Descripción</th>
                                <th style="width: 11%;" class="text-center">Fecha Act.</th>
                                <th style="width: 8%;" class="text-center">Unidad</th>
                                <th style="width: 13%;" class="text-end">Costo Unit ($)</th>
                                <th style="width: 10%;" class="text-end">Cantidad</th>
                                <th style="width: 13%;" class="text-end">Subtotal ($)</th>
                                <th style="width: 7%;" class="text-center">Observacion</th>
                                <th style="width: 6%;" class="text-center col-accion-header"></th>
                            </tr>
                        </thead>

                        <!-- SECCIÓN 1: MATERIALES -->
                        <tbody id="sec_MATERIAL">
                            <tr class="table-secondary fw-bold">
                                <td colspan="5">
                                    <i class="bi bi-box-seam me-1"></i> MATERIALES
                                </td>
                                <td class="text-end text-dark fw-semibold fs-6" id="subtotal_MATERIAL">$ 0,00</td>
                                <td colspan="2" class="text-end btn-add-cell">
                                    <button type="button" class="btn btn-sm btn-light text-dark border border-secondary btn-add-row" onclick="agregarFila('MATERIAL')">
                                        <i class="bi bi-plus-lg me-1"></i> Agregar Material
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- SECCIÓN 2: MANO DE OBRA -->
                        <tbody id="sec_MO">
                            <tr class="table-secondary fw-bold">
                                <td colspan="5">
                                    <i class="bi bi-people me-1"></i> MANO DE OBRA
                                </td>
                                <td class="text-end text-dark fw-semibold fs-6" id="subtotal_MO">$ 0,00</td>
                                <td colspan="2" class="text-end btn-add-cell">
                                    <button type="button" class="btn btn-sm btn-light text-dark border border-secondary btn-add-row" onclick="agregarFila('MO')">
                                        <i class="bi bi-plus-lg me-1"></i> Agregar Mano de Obra
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- SECCIÓN 3: EQUIPOS -->
                        <tbody id="sec_EQUIPO">
                            <tr class="table-secondary fw-bold">
                                <td colspan="5">
                                    <i class="bi bi-truck me-1"></i> EQUIPOS Y HERRAMIENTAS
                                </td>
                                <td class="text-end text-dark fw-semibold fs-6" id="subtotal_EQUIPO">$ 0,00</td>
                                <td colspan="2" class="text-end btn-add-cell">
                                    <button type="button" class="btn btn-sm btn-light text-dark border border-secondary btn-add-row" onclick="agregarFila('EQUIPO')">
                                        <i class="bi bi-plus-lg me-1"></i> Agregar Equipo
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- SECCIÓN 4: SUBCONTRATOS -->
                        <tbody id="sec_SUBCONTRATO">
                            <tr class="table-secondary fw-bold">
                                <td colspan="5">
                                    <i class="bi bi-briefcase me-1"></i> SUBCONTRATOS / OTROS
                                </td>
                                <td class="text-end text-dark fw-semibold fs-6" id="subtotal_SUBCONTRATO">$ 0,00</td>
                                <td colspan="2" class="text-end btn-add-cell">
                                    <button type="button" class="btn btn-sm btn-light text-dark border border-secondary btn-add-row" onclick="agregarFila('SUBCONTRATO')">
                                        <i class="bi bi-plus-lg me-1"></i> Agregar Subcontrato
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- PIE DE TABLA: TOTAL GENERAL -->
                        <tfoot>
                            <tr class="table-dark">
                                <td colspan="5" class="text-end fw-bold fs-6">COSTO UNITARIO TOTAL DE TAREA:</td>
                                <td class="text-end fw-bold fs-5 text-warning" id="costo_total_tarea">$ 0,00</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- OBSERVACIONES GENERALES DE LA TAREA -->
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold mb-0">
                            <i class="bi bi-file-earmark-text me-1"></i>Observaciones / Especificaciones Técnicas Generales
                        </label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="toggleAmpliarObs()">
                            <i class="bi bi-arrows-angle-expand me-1" id="iconObsExpand"></i><span id="lblObsExpand">Ampliar campo</span>
                        </button>
                    </div>
                    <textarea id="tarea_observaciones" class="form-control" rows="3" 
                        placeholder="Detalles constructivos, especificaciones de cómputo o notas explicativas..." 
                        style="resize: vertical; min-height: 90px; transition: all 0.2s ease;"></textarea>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="btnGuardarAPU" onclick="guardarTarea()"><i class="bi bi-save me-1"></i> Guardar APU</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SECUNDARIO: OBSERVACIONES POR FILA DE RECURSO -->
<div class="modal fade" id="modalObsFila" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-secondary text-white py-2">
                <h6 class="modal-title fw-bold mb-0">
                    <i class="bi bi-chat-left-text me-2"></i>Observación del Recurso
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="mb-2">
                    <small class="text-muted fw-semibold" id="modalObsRecursoNombre">Recurso: -</small>
                </div>
                <textarea id="modalObsTexto" class="form-control" rows="5" placeholder="Escriba aquí notas específicas, especificaciones o detalles de este insumo..."></textarea>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary px-3" onclick="confirmarObsFila()"><i class="bi bi-check-lg me-1"></i> Guardar Nota</button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT AMPLIAR OBSERVACIONES GENERALES -->
<script>
function toggleAmpliarObs() {
    const textarea = document.getElementById('tarea_observaciones');
    const icon = document.getElementById('iconObsExpand');
    const lbl = document.getElementById('lblObsExpand');
    
    if (textarea.rows === 3) {
        textarea.rows = 10;
        icon.className = 'bi bi-arrows-angle-contract me-1';
        lbl.innerText = 'Reducir campo';
    } else {
        textarea.rows = 3;
        icon.className = 'bi bi-arrows-angle-expand me-1';
        lbl.innerText = 'Ampliar campo';
    }
}
</script>