<?php
include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<style>
#modalFlota .modal-body .tab-content {
    min-height: 480px;
    max-height: 65vh;
    overflow-y: auto;
}    
.modal-tabs-discreet .nav-link {
    color: #495057;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    margin-right: 4px;
    font-size: 0.9rem;
}
.modal-tabs-discreet .nav-link.active {
    color: #212529;
    background-color: #e9ecef;
    border-color: #ced4da;
    font-weight: 600;
}
.subtabs-adjuntos .nav-link {
    font-size: 0.85rem;
    padding: 5px 12px;
    color: #6c757d;
}
.subtabs-adjuntos .nav-link.active {
    color: #212529;
    background-color: #fff;
    border-bottom: 2px solid #6c757d;
    font-weight: 600;
}
</style>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-truck me-2"></i> Gestión de Flota y Mantenimiento
        </h4>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal('NUEVO')">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Registro
        </button>
    </div>

    <!-- Pestañas de Navegación de Flota -->
    <ul class="nav nav-tabs mb-3" id="tabFlota" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold text-dark" id="vehiculos-tab" data-bs-toggle="tab" data-bs-target="#flota-content" type="button" role="tab" onclick="filtrarClasificacion('VEHICULO', this)">
                <i class="bi bi-car-front me-1 text-secondary"></i> VEHÍCULOS 
                <span class="badge bg-secondary ms-1" id="cant-vehiculos">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-dark" id="maquinarias-tab" data-bs-toggle="tab" data-bs-target="#flota-content" type="button" role="tab" onclick="filtrarClasificacion('MAQUINARIA', this)">
                <i class="bi bi-gear-wide-connected me-1 text-secondary"></i> MAQUINARIAS 
                <span class="badge bg-secondary ms-1" id="cant-maquinarias">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-dark" id="herramientas-tab" data-bs-toggle="tab" data-bs-target="#flota-content" type="button" role="tab" onclick="filtrarClasificacion('HERRAMIENTA', this)">
                <i class="bi bi-tools me-1 text-secondary"></i> HERRAMIENTAS / EQUIPOS 
                <span class="badge bg-secondary ms-1" id="cant-herramientas">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-secondary" id="baja-tab" data-bs-toggle="tab" data-bs-target="#flota-content" type="button" role="tab" onclick="filtrarClasificacion('BAJA', this)">
                <i class="bi bi-x-circle me-1 text-muted"></i> BAJA / VENDIDO 
                <span class="badge bg-light text-muted border ms-1" id="cant-baja">0</span>
            </button>
        </li>
    </ul>

    <!-- Tabla de Unidades -->
    <div class="card shadow-sm border-0 p-3">
        <div class="table-responsive">
            <table id="tablaFlota" class="table table-bordered table-striped w-100 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Dominio / Patente</th>
                        <th>Marca y Modelo</th>
                        <th>Tipo</th>
                        <th>Año</th>
                        <th>Uso (Km / Hs)</th>
                        <th>Próximo Service</th>
                        <th>Cargado Por</th>
                        <th>Estado</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- MODAL PRINCIPAL -->
<div class="modal fade" id="modalFlota" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalFlotaLabel"><i class="bi bi-truck me-2"></i> Ficha de Flota</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formFlota">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="id">

                    <ul class="nav nav-tabs modal-tabs-discreet mb-3" id="modalTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="tab-basica-btn" data-bs-toggle="tab" data-bs-target="#tab-basica" type="button">
                                <i class="bi bi-info-circle me-1"></i> Información Básica
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-tecnica-btn" data-bs-toggle="tab" data-bs-target="#tab-tecnica" type="button">
                                <i class="bi bi-wrench me-1"></i> Ficha Técnica y Vencimientos
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-services-btn" data-bs-toggle="tab" data-bs-target="#tab-services" type="button">
                                <i class="bi bi-journal-check me-1"></i> Histórico de Services
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-gastos-btn" data-bs-toggle="tab" data-bs-target="#tab-gastos" type="button">
                                <i class="bi bi-currency-dollar me-1"></i> Gastos Vinculados
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border p-3 rounded bg-white">
                        
                        <!-- PESTAÑA 1: INFORMACIÓN BÁSICA -->
                        <div class="tab-pane fade show active" id="tab-basica">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Clasificación</label>
                                    <select name="clasificacion" id="clasificacion" class="form-select" required>
                                        <option value="VEHICULO">VEHÍCULO</option>
                                        <option value="MAQUINARIA">MAQUINARIA</option>
                                        <option value="HERRAMIENTA">HERRAMIENTA / EQUIPO</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Dominio / Patente</label>
                                    <input name="dominio_patente" id="dominio_patente" class="form-control" placeholder="Ej: AB123CD / MEZ326">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Marca</label>
                                    <input name="marca" id="marca" class="form-control" required placeholder="Ej: Citroën / Caterpillar">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Modelo</label>
                                    <input name="modelo" id="modelo" class="form-control" required placeholder="Ej: Berlingo / 416">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tipo de Unidad</label>
                                    <select name="tipo" id="tipo" class="form-select" required>
                                        <option value="">SELECCIONAR...</option>
                                        <option value="AUTOMÓVIL">AUTOMÓVIL</option>
                                        <option value="PICKUP / CAMIONETA">PICKUP / CAMIONETA</option>
                                        <option value="CAMIÓN CHASIS">CAMIÓN CHASIS</option>
                                        <option value="CAMIÓN TRACTOR">CAMIÓN TRACTOR</option>
                                        <option value="CAMIÓN VOLCADOR">CAMIÓN VOLCADOR</option>
                                        <option value="CARRETON /ACOPLADO / SEMIRREMOLQUE">CARRETON/ ACOPLADO / SEMIRREMOLQUE</option>
                                        <option value="RETROPALA">RETROPALA</option>
                                        <option value="MINICARGADORA">MINICARGADORA</option>
                                        <option value="EXCAVADORA">EXCAVADORA</option>
                                        <option value="PALA CARGADORA">PALA CARGADORA</option>
                                        <option value="GENERADOR ELÉCTRICO">GENERADOR ELÉCTRICO</option>
                                        <option value="COMPRESOR">COMPRESOR</option>
                                        <option value="MOTOBOMBA">MOTOBOMBA</option>
                                        <option value="BOMBA ELECTRICA">BOMBA ELECTRICA</option>
                                        <option value="PLACA VIBRATORIA / PISÓN">PLACA VIBRATORIA / PISÓN</option>
                                        <option value="MOTOSIERRA / DESMALEZADORA">MOTOSIERRA / DESMALEZADORA</option>
                                        <option value="IMPLEMENTO MARTILLO">IMPLEMENTO MARTILLO</option>
                                        <option value="IMPLEMENTO MAQUINA">IMPLEMENTO MAQUINA</option>
                                        <option value="OTRO">OTRO</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Año</label>
                                    <input type="number" name="anio" id="anio" class="form-control" placeholder="Ej: 2022">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Unidad de Medición</label>
                                    <select name="unidad_medida" id="unidad_medida" class="form-select" required>
                                        <option value="KM">KILÓMETROS (KM)</option>
                                        <option value="HORAS">HORAS DE USO (HS)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Km / Hs al Comprar</label>
                                    <input type="number" step="0.01" name="km_horas_inicial" id="km_horas_inicial" class="form-control" value="0">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Km / Hs Actuales</label>
                                    <input type="number" step="0.01" name="km_horas_actual" id="km_horas_actual" class="form-control" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Próximo Service (Km / Hs)</label>
                                    <input type="number" step="0.01" name="proximo_service" id="proximo_service" class="form-control" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Titular del Dominio</label>
                                    <input name="titular" id="titular" class="form-control" placeholder="Razón social / Nombre">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Fecha de Adquisición</label>
                                    <input type="date" name="fecha_adquisicion" id="fecha_adquisicion" class="form-control">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Estado Operativo</label>
                                    <select name="estado" id="estado" class="form-select" required>
                                        <option value="ACTIVO">🟢 ACTIVO</option>
                                        <option value="DESACTIVO">🟡 EN MANTENIMIENTO / DESACTIVO</option>
                                        <option value="BAJA">🔴 BAJA / VENDIDO</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Observaciones / Descripción</label>
                                    <textarea name="descripcion" id="descripcion" class="form-control" rows="2"></textarea>
                                </div>

                                <!-- SUB-PESTAÑAS DE ADJUNTOS -->
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold border-bottom pb-2">Archivos y Documentación Adjunta</h6>
                                    
                                    <ul class="nav nav-tabs subtabs-adjuntos mb-3" id="tabAdjuntos">
                                        <li class="nav-item">
                                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#adj-documentacion" type="button">
                                                <i class="bi bi-file-earmark-pdf me-1"></i> Documentación
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#adj-fotos" type="button">
                                                <i class="bi bi-image me-1"></i> Fotos
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#adj-varios" type="button">
                                                <i class="bi bi-folder me-1"></i> Varios
                                            </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content p-2 bg-light rounded border">
                                        <div class="tab-pane fade show active" id="adj-documentacion">
                                            <div class="d-flex mb-2 gap-2">
                                                <input type="file" id="file_doc" class="form-control form-control-sm">
                                                <button type="button" class="btn btn-sm btn-secondary" onclick="subirAdjunto('DOCUMENTO', 'file_doc')">Subir</button>
                                            </div>
                                            <div id="lista_doc" class="list-group list-group-flush small"></div>
                                        </div>
                                        <div class="tab-pane fade" id="adj-fotos">
                                            <div class="d-flex mb-2 gap-2">
                                                <input type="file" id="file_foto" class="form-control form-control-sm" accept="image/*">
                                                <button type="button" class="btn btn-sm btn-secondary" onclick="subirAdjunto('FOTO', 'file_foto')">Subir</button>
                                            </div>
                                            <div id="lista_foto" class="row g-2"></div>
                                        </div>
                                        <div class="tab-pane fade" id="adj-varios">
                                            <div class="d-flex mb-2 gap-2">
                                                <input type="file" id="file_varios" class="form-control form-control-sm">
                                                <button type="button" class="btn btn-sm btn-secondary" onclick="subirAdjunto('FACTURA', 'file_varios')">Subir</button>
                                            </div>
                                            <div id="lista_varios" class="list-group list-group-flush small"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 2: FICHA TÉCNICA -->
                        <div class="tab-pane fade" id="tab-tecnica">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Aceite Motor (Tipo)</label>
                                    <input name="aceite_motor" id="aceite_motor" class="form-control" placeholder="Ej: 15W40 Semi">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Cantidad Motor</label>
                                    <input name="cant_aceite_motor" id="cant_aceite_motor" class="form-control" placeholder="Ej: 7.5 Litros">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Aceite Caja (Tipo)</label>
                                    <input name="aceite_caja" id="aceite_caja" class="form-control" placeholder="Ej: 80W90">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Cantidad Caja</label>
                                    <input name="cant_aceite_caja" id="cant_aceite_caja" class="form-control" placeholder="Ej: 2 Litros">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Aceite Diferencial (Tipo)</label>
                                    <input name="aceite_diferencial" id="aceite_diferencial" class="form-control" placeholder="Ej: 80W90">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Cantidad Diferencial</label>
                                    <input name="cant_aceite_diferencial" id="cant_aceite_diferencial" class="form-control" placeholder="Ej: 3 Litros">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Aceite Hidráulico (Tipo)</label>
                                    <input name="aceite_hidraulico" id="aceite_hidraulico" class="form-control" placeholder="Ej: ISO 68 / 15W40 / ATF">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Cantidad Hidráulico</label>
                                    <input name="cant_aceite_hidraulico" id="cant_aceite_hidraulico" class="form-control" placeholder="Ej: 45 Litros">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Filtros (Marcas, Códigos y Equivalencias)</label>
                                    <textarea name="filtros_codigos" id="filtros_codigos" class="form-control" rows="2" placeholder="Aceite: Mann W712 | Aire: Fram CA..."></textarea>
                                </div>

                                <hr class="my-2">

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Vencimiento VTV</label>
                                    <input type="date" name="vencimiento_vtv" id="vencimiento_vtv" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Compañía de Seguro</label>
                                    <input name="compañia_seguro" id="compañia_seguro" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">N° de Póliza</label>
                                    <input name="nro_poliza" id="nro_poliza" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Vencimiento Seguro</label>
                                    <input type="date" name="vencimiento_seguro" id="vencimiento_seguro" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 3: SERVICES -->
                        <div class="tab-pane fade" id="tab-services">
                            <div class="bg-light p-3 rounded mb-3 border" id="boxNuevoService">
                                <h6 class="fw-bold mb-2"><i class="bi bi-plus-circle me-1"></i> Cargar Service / Mantenimiento</h6>
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <input type="date" id="svc_fecha" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" step="0.01" id="svc_lectura" class="form-control form-control-sm" placeholder="Km / Horas actuales">
                                    </div>
                                    <div class="col-md-3">
                                        <input id="svc_taller" class="form-control form-control-sm" placeholder="Taller / Realizado por">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" step="0.01" id="svc_costo" class="form-control form-control-sm" placeholder="Costo $">
                                    </div>
                                    <div class="col-md-10">
                                        <input id="svc_trabajo" class="form-control form-control-sm" placeholder="Detalle del trabajo realizado">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-secondary btn-sm w-100 fw-bold" onclick="guardarService()"><i class="bi bi-save"></i> Agregar</button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-striped border align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Km / Hs</th>
                                            <th>Taller / Responsable</th>
                                            <th>Trabajo Realizado</th>
                                            <th>Registrado Por</th>
                                            <th class="text-end">Costo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbServices"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- PESTAÑA 4: GASTOS VINCULADOS -->
                        <div class="tab-pane fade" id="tab-gastos">
                            <ul class="list-group list-group-flush mb-3" id="listaGastosVehiculo"></ul>
                            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border">
                                <span class="fw-bold text-dark fs-6">TOTAL GASTOS ACUMULADOS:</span>
                                <span class="fw-bold text-danger fs-5" id="totalGastosAcumulados">$ 0,00</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-dark" id="btnGuardar">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalBS;
let tabla;
let clasificacionActual = 'VEHICULO';

// Función para formatear con punto como separador de miles sin decimales
function formatearEntero(val) {
    let num = Math.round(parseFloat(val) || 0);
    return new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 }).format(num);
}

function filtrarClasificacion(tipo, btn) {
    clasificacionActual = tipo;
    tabla.ajax.url('/contable/ajax/vehiculos.php?accion=listar&clasificacion=' + clasificacionActual).load();
}

window.abrirModal = function(modo) {
    $('#formFlota')[0].reset();
    $('#id').val('');
    $('#tbServices, #listaGastosVehiculo, #lista_doc, #lista_foto, #lista_varios').html('');
    $('#totalGastosAcumulados').text('$ 0,00');
    $('#btnGuardar, #boxNuevoService, input, select, textarea').prop('disabled', false);
    $('#tab-basica-btn').tab('show');

    if(modo === 'NUEVO') {
        $('#modalFlotaLabel').html('<i class="bi bi-plus-circle me-2"></i> Registrar Unidad');
        $('#clasificacion').val(clasificacionActual === 'BAJA' ? 'VEHICULO' : clasificacionActual);
    }
    modalBS.show();
}

window.editar = function(id, soloVer = false) {
    let d = tabla.rows().data().toArray().find(x => x.id == id);
    if(!d) return;

    $('#formFlota')[0].reset();
    $('#id').val(d.id);
    $('#clasificacion').val(d.clasificacion);
    $('#dominio_patente').val(d.dominio_patente);
    $('#marca').val(d.marca);
    $('#modelo').val(d.modelo);
    $('#tipo').val(d.tipo);
    $('#anio').val(d.anio);
    $('#unidad_medida').val(d.unidad_medida);
    $('#km_horas_inicial').val(d.km_horas_inicial);
    $('#km_horas_actual').val(d.km_horas_actual);
    $('#proximo_service').val(d.proximo_service);
    $('#titular').val(d.titular);
    $('#fecha_adquisicion').val(d.fecha_adquisicion);
    $('#estado').val(d.estado);
    $('#descripcion').val(d.descripcion);
    
    $('#aceite_motor').val(d.aceite_motor);
    $('#cant_aceite_motor').val(d.cant_aceite_motor);
    $('#aceite_caja').val(d.aceite_caja);
    $('#cant_aceite_caja').val(d.cant_aceite_caja);
    $('#aceite_diferencial').val(d.aceite_diferencial);
    $('#cant_aceite_diferencial').val(d.cant_aceite_diferencial);
    $('#aceite_hidraulico').val(d.aceite_hidraulico);
    $('#cant_aceite_hidraulico').val(d.cant_aceite_hidraulico);

    $('#filtros_codigos').val(d.filtros_codigos);
    $('#vencimiento_vtv').val(d.vencimiento_vtv);
    $('#vencimiento_seguro').val(d.vencimiento_seguro);
    $('#compañia_seguro').val(d.compañia_seguro);
    $('#nro_poliza').val(d.nro_poliza);

    cargarArchivos(d.id);
    cargarServices(d.id);
    cargarGastos(d.id);

    if(soloVer) {
        $('#modalFlotaLabel').html('<i class="bi bi-eye me-2"></i> Ficha de Unidad (Solo Lectura)');
        $('#btnGuardar, #boxNuevoService').hide();
        $('#formFlota input, #formFlota select, #formFlota textarea').prop('disabled', true);
    } else {
        $('#modalFlotaLabel').html('<i class="bi bi-pencil me-2"></i> Editar Unidad');
        $('#btnGuardar, #boxNuevoService').show();
        $('#formFlota input, #formFlota select, #formFlota textarea').prop('disabled', false);
    }

    $('#tab-basica-btn').tab('show');
    modalBS.show();
}

function cargarArchivos(vId) {
    $.get('/contable/ajax/vehiculos.php?accion=listar_archivos', { vehiculo_id: vId }, function(r) {
        let lDoc = $('#lista_doc').empty();
        let lFoto = $('#lista_foto').empty();
        let lVar = $('#lista_varios').empty();

        if(r.archivos) {
            r.archivos.forEach(a => {
                let url = '/contable/uploads/vehiculos/' + a.archivo;
                let item = `<div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <a href="${url}" target="_blank" class="text-truncate text-decoration-none" style="max-width: 80%;">
                        <i class="bi bi-file-earmark me-1"></i> ${a.nombre_original}
                    </a>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="eliminarArchivo(${a.id}, ${vId})">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>`;

                if(a.tipo_adjunto === 'DOCUMENTO') lDoc.append(item);
                else if(a.tipo_adjunto === 'FACTURA') lVar.append(item);
                else if(a.tipo_adjunto === 'FOTO') {
                    lFoto.append(`<div class="col-3 position-relative">
                        <a href="${url}" target="_blank">
                            <img src="${url}" class="img-thumbnail w-100" style="height: 70px; object-fit: cover;">
                        </a>
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 py-0 px-1" onclick="eliminarArchivo(${a.id}, ${vId})">&times;</button>
                    </div>`);
                }
            });
        }
    }, 'json');
}

function subirAdjunto(tipo, inputId) {
    let vId = $('#id').val();
    if(!vId) {
        Swal.fire('Atención', 'Guarde la unidad antes de adjuntar archivos.', 'warning');
        return;
    }
    let fileInput = document.getElementById(inputId);
    if(!fileInput.files[0]) return;

    let formData = new FormData();
    formData.append('vehiculo_id', vId);
    formData.append('tipo_adjunto', tipo);
    formData.append('archivo', fileInput.files[0]);

    $.ajax({
        url: '/contable/ajax/vehiculos.php?accion=subir_adjunto',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function() {
            fileInput.value = '';
            cargarArchivos(vId);
        }
    });
}

function eliminarArchivo(id, vId) {
    if(confirm('¿Eliminar este archivo adjunto?')) {
        $.post('/contable/ajax/vehiculos.php?accion=eliminar_archivo', { id }, function() {
            cargarArchivos(vId);
        });
    }
}

function cargarServices(vId) {
    $.get('/contable/ajax/vehiculos.php?accion=listar_services', { vehiculo_id: vId }, function(r) {
        let tb = $('#tbServices').empty();
        if(r.services && r.services.length > 0) {
            r.services.forEach(s => {
                let fmt = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(s.costo);
                tb.append(`<tr>
                    <td>${s.fecha}</td>
                    <td>${formatearEntero(s.lectura_km_horas)}</td>
                    <td>${s.realizado_por || '-'}</td>
                    <td>${s.trabajo_realizado}</td>
                    <td><span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person me-1"></i> ${s.usuario_nombre || 'Sistema'}</span></td>
                    <td class="text-end fw-bold">${fmt}</td>
                </tr>`);
            });
        } else {
            tb.append('<tr><td colspan="6" class="text-center text-muted py-2">Sin services registrados.</td></tr>');
        }
    }, 'json');
}

function guardarService() {
    let vId = $('#id').val();
    if(!vId) return;

    let data = {
        vehiculo_id: vId,
        fecha_service: $('#svc_fecha').val(),
        lectura_km_horas: $('#svc_lectura').val(),
        realizado_por: $('#svc_taller').val(),
        costo: $('#svc_costo').val(),
        trabajo_realizado: $('#svc_trabajo').val()
    };

    $.post('/contable/ajax/vehiculos.php?accion=guardar_service', data, function() {
        cargarServices(vId);
        tabla.ajax.reload(null, false);
        $('#svc_lectura, #svc_taller, #svc_costo, #svc_trabajo').val('');
    }, 'json');
}

function cargarGastos(vId) {
    $.get('/contable/ajax/vehiculos.php?accion=listar_gastos', { vehiculo_id: vId }, function(r) {
        let lista = $('#listaGastosVehiculo').empty();
        let totalSuma = 0;

        if (r.gastos && r.gastos.length > 0) {
            r.gastos.forEach(g => {
                let monto = parseFloat(g.total) || 0;
                totalSuma += monto;

                let totalFormateado = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto);
                let fechaFormateada = g.fecha ? g.fecha.split('-').reverse().join('/') : '-';
                let prov = g.proveedor ? g.proveedor : 'Gasto General';
                let comp = g.numero_comprobante ? `Comp: ${g.numero_comprobante}` : 'Sin comprobante';

                let botonAdjunto = g.archivo ? 
                    `<a href="/contable/uploads/gastos/${g.archivo}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2" title="Ver Comprobante Gasto"><i class="bi bi-file-earmark-pdf"></i></a>` : 
                    `<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" disabled title="Sin adjunto"><i class="bi bi-eye-slash"></i></button>`;

                lista.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                        <div>
                            <i class="bi bi-cart-dash text-danger me-2"></i> 
                            <span class="fw-semibold">${prov}</span> 
                            <small class="text-muted ms-2">(${comp} - ${fechaFormateada})</small>
                            <span class="text-secondary d-block" style="font-size:0.75rem">${g.detalle || ''}</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-danger">${totalFormateado}</span>
                            ${botonAdjunto}
                        </div>
                    </li>
                `);
            });
        } else {
            lista.append('<li class="list-group-item text-muted text-center py-2 small">No hay gastos asociados a este vehículo.</li>');
        }

        let totalGeneralFormateado = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(totalSuma);
        $('#totalGastosAcumulados').text(totalGeneralFormateado);
    }, 'json');
}

window.eliminar = function(id) {
    if(confirm('¿Desea eliminar esta unidad de la flota?')) {
        $.post('/contable/ajax/vehiculos.php?accion=eliminar', { id }, function() {
            tabla.ajax.reload(null, false);
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    modalBS = new bootstrap.Modal(document.getElementById('modalFlota'));

    tabla = $('#tablaFlota').DataTable({
        ajax: {
            url: '/contable/ajax/vehiculos.php?accion=listar&clasificacion=VEHICULO',
            dataSrc: function(json) {
                if(clasificacionActual === 'VEHICULO') $('#cant-vehiculos').text(json.data.length);
                else if(clasificacionActual === 'MAQUINARIA') $('#cant-maquinarias').text(json.data.length);
                else if(clasificacionActual === 'HERRAMIENTA') $('#cant-herramientas').text(json.data.length);
                else if(clasificacionActual === 'BAJA') $('#cant-baja').text(json.data.length);
                return json.data;
            }
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
            },
            { 
                extend: 'colvis', 
                text: 'Columnas', 
                className: 'btn btn-sm btn-secondary' 
            }
        ],
        columns: [
            { data: 'id' },
            { data: 'dominio_patente', render: d => d ? `<strong>${d}</strong>` : '-' },
            { data: null, render: d => `${d.marca} ${d.modelo}` },
            { data: 'tipo' },
            { data: 'anio', render: d => d ? d : '-' },
            { data: null, render: d => `${formatearEntero(d.km_horas_actual)} ${d.unidad_medida}` },
            { data: null, render: d => `${formatearEntero(d.proximo_service)} ${d.unidad_medida}` },
            { data: 'usuario_creador', render: d => d ? `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person me-1"></i> ${d}</span>` : `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person me-1"></i> Sistema</span>` },
            { data: 'estado', render: d => {
                if(d === 'ACTIVO') return '<span class="badge bg-success">ACTIVO</span>';
                if(d === 'BAJA') return '<span class="badge bg-danger">BAJA / VENDIDO</span>';
                return '<span class="badge bg-secondary">DESACTIVO</span>';
            }},
            {
                data: null,
                className: 'text-center',
                render: d => `
                    <button class="btn btn-sm btn-outline-secondary" title="Ver Ficha" onclick="editar(${d.id}, true)"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editar(${d.id}, false)"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminar(${d.id})"><i class="bi bi-trash"></i></button>
                `
            }
        ]
    });

    $('#formFlota').submit(function(e) {
        e.preventDefault();
        $.post('/contable/ajax/vehiculos.php?accion=guardar', $(this).serialize(), function(r) {
            let res = JSON.parse(r);
            if(res.status === 'OK') {
                tabla.ajax.reload(null, false);
                modalBS.hide();
            }
        });
    });
});
</script>

<?php include '../../../includes/footer.php'; ?>