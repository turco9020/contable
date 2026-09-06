<?php
include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<style>
#modalPersonal .modal-body .tab-content {
    min-height: 480px;
    max-height: 70vh;
    overflow-y: auto;
}    
/* Pestañas superiores del modal discretas */
.modal-tabs-discreet .nav-link {
    color: #495057;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    margin-right: 4px;
    font-size: 0.88rem;
}
.modal-tabs-discreet .nav-link.active {
    color: #212529;
    background-color: #e9ecef;
    border-color: #ced4da;
    font-weight: 600;
}
/* Subtítulos de sección discretos y limpios */
.seccion-titulo {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 4px;
    margin-bottom: 12px;
}
.subtabs-adjuntos .nav-link {
    font-size: 0.82rem;
    padding: 4px 10px;
    color: #6c757d;
}
.subtabs-adjuntos .nav-link.active {
    color: #212529;
    background-color: #fff;
    border-bottom: 2px solid #6c757d;
    font-weight: 600;
}
/* Estilo discreto para la pestaña de Inactivos */
.nav-link-inactivo {
    color: #8c959f !important;
}
.nav-link-inactivo.active {
    color: #495057 !important;
    font-weight: 600;
}
</style>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-people me-2"></i> Gestión de Personal / RRHH
        </h4>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal('NUEVO')">
            <i class="bi bi-person-plus me-2"></i> Nuevo Legajo
        </button>
    </div>

    <!-- Pestañas de Clasificación -->
    <ul class="nav nav-tabs mb-3" id="tabPersonal" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold text-dark" id="operativo-tab" data-bs-toggle="tab" data-bs-target="#personal-content" type="button" onclick="filtrarClasificacion('OPERATIVO', this)">
                <i class="bi bi-person-gear me-1 text-secondary"></i> OPERARIO 
                <span class="badge bg-secondary ms-1" id="cant-operativo">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold text-dark" id="administrativo-tab" data-bs-toggle="tab" data-bs-target="#personal-content" type="button" onclick="filtrarClasificacion('ADMINISTRATIVO', this)">
                <i class="bi bi-building me-1 text-secondary"></i> ADMINISTRATIVO 
                <span class="badge bg-secondary ms-1" id="cant-administrativo">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold text-dark" id="maquinistas-tab" data-bs-toggle="tab" data-bs-target="#personal-content" type="button" onclick="filtrarClasificacion('MAQUINISTA', this)">
                <i class="bi bi-truck me-1 text-secondary"></i> MAQUINISTAS / CHOFERES 
                <span class="badge bg-secondary ms-1" id="cant-maquinistas">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold text-dark" id="obras-tab" data-bs-toggle="tab" data-bs-target="#personal-content" type="button" onclick="filtrarClasificacion('OBRAS / CAMPO', this)">
                <i class="bi bi-helmet me-1 text-secondary"></i> OBRAS / CAMPO 
                <span class="badge bg-secondary ms-1" id="cant-obras">0</span>
            </button>
        </li>
        <!-- Pestaña Inactivos apagada / discreta -->
        <li class="nav-item">
            <button class="nav-link nav-link-inactivo" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#personal-content" type="button" onclick="filtrarClasificacion('INACTIVO', this)">
                <i class="bi bi-person-x me-1"></i> INACTIVOS / BAJA 
                <span class="badge bg-light text-muted border ms-1" id="cant-inactivos">0</span>
            </button>
        </li>
    </ul>

    <!-- Tabla -->
    <div class="card shadow-sm border-0 p-3">
        <div class="table-responsive">
            <table id="tablaPersonal" class="table table-bordered table-striped w-100 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>CUIL / DNI</th>
                        <th>Apellido y Nombre</th>
                        <th>Puesto</th>
                        <th>Teléfono</th>
                        <th>Ingreso</th>
                        <th>Antigüedad</th>
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
<div class="modal fade" id="modalPersonal" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold" id="modalPersonalLabel"><i class="bi bi-person-badge me-2"></i> Legajo del Empleado</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formPersonal">
                <div class="modal-body p-3">
                    <input type="hidden" name="id" id="id">

                    <ul class="nav nav-tabs modal-tabs-discreet mb-3" id="modalTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="tab-basica-btn" data-bs-toggle="tab" data-bs-target="#tab-basica" type="button">
                                <i class="bi bi-person me-1"></i> Datos Personales y Registrales
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-vencimientos-btn" data-bs-toggle="tab" data-bs-target="#tab-vencimientos" type="button">
                                <i class="bi bi-shield-check me-1"></i> Indumentaria y Coberturas
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-historico-btn" data-bs-toggle="tab" data-bs-target="#tab-historico" type="button">
                                <i class="bi bi-journal-text me-1"></i> Histórico de Movimientos
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border p-3 rounded bg-white">
                        
                        <!-- TAB 1: DATOS PERSONALES, REGISTRALES Y PAGO -->
                        <div class="tab-pane fade show active" id="tab-basica">
                            
                            <!-- 1- DATOS PERSONALES -->
                            <div class="seccion-titulo"><i class="bi bi-person me-1"></i> 1. Datos Personales</div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Nombre</label>
                                    <input name="nombre" id="nombre" class="form-control form-control-sm" required placeholder="Juan Manuel">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Apellido</label>
                                    <input name="apellido" id="apellido" class="form-control form-control-sm" required placeholder="Pérez">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">CUIL / DNI</label>
                                    <input name="cuil" id="cuil" class="form-control form-control-sm" placeholder="20-30123456-7">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Fecha Nacimiento</label>
                                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control form-control-sm">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Teléfono / WhatsApp</label>
                                    <input name="telefono" id="telefono" class="form-control form-control-sm" placeholder="3424123456">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Email</label>
                                    <input type="email" name="email" id="email" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Domicilio</label>
                                    <input name="domicilio" id="domicilio" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Contacto Emergencia</label>
                                    <input name="contacto_emergencia" id="contacto_emergencia" class="form-control form-control-sm" placeholder="Nombre / Tel">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Clasificación</label>
                                    <select name="clasificacion" id="clasificacion" class="form-select form-select-sm" required>
                                        <option value="OPERATIVO">OPERARIO</option>
                                        <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                                        <option value="MAQUINISTA">MAQUINISTA / CHOFER</option>
                                        <option value="OBRAS / CAMPO">OBRAS / CAMPO</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Puesto / Función</label>
                                    <input name="puesto" id="puesto" class="form-control form-control-sm" placeholder="Ej: Oficial Maquinista">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Procedencia</label>
                                    <input name="procedencia" id="procedencia" class="form-control form-control-sm" placeholder="Recomendado, Muncipalidad, etc.">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Estado Laboral</label>
                                    <select name="estado" id="estado" class="form-select form-select-sm" required>
                                        <option value="ACTIVO">🟢 ACTIVO</option>
                                        <option value="LICENCIA">🟡 EN LICENCIA</option>
                                        <option value="INACTIVO">🔴 INACTIVO / BAJA</option>
                                    </select>
                                </div>
                            </div>

                            <!-- 2- DATOS REGISTRALES -->
                            <div class="seccion-titulo"><i class="bi bi-file-earmark-text me-1"></i> 2. Datos Registrales</div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Fecha de Ingreso</label>
                                    <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Situación Laboral</label>
                                    <select name="situacion_laboral" id="situacion_laboral" class="form-select form-select-sm">
                                        <option value="REGISTRADO">REGISTRADO</option>
                                        <option value="NO REGISTRADO">NO REGISTRADO</option>
                                        <option value="MONOTRIBUTO">MONOTRIBUTO</option>
                                        <option value="REG. OTRO CUIT">REG. OTRO CUIT</option>
                                        <option value="OTROS">OTROS</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Alta ARCA (AFIP)</label>
                                    <input type="date" name="fecha_alta_arca" id="fecha_alta_arca" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Convenio Aplicable</label>
                                    <input name="convenio_aplicable" id="convenio_aplicable" class="form-control form-control-sm" placeholder="UOCRA / Comercio">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Puesto Reg. ARCA</label>
                                    <input name="puesto_arca" id="puesto_arca" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Alta IERIC</label>
                                    <input type="date" name="fecha_alta_ieric" id="fecha_alta_ieric" class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- 3- CONDICIONES DE PAGO -->
                            <div class="seccion-titulo"><i class="bi bi-credit-card me-1"></i> 3. Condiciones de Pago</div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Cond. Pago</label>
                                    <input name="cond_pago" id="cond_pago" class="form-control form-control-sm" placeholder="Quincenal / Mensual">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Banco</label>
                                    <input name="banco" id="banco" class="form-control form-control-sm" placeholder="Ej: Banco Macro">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">N° CBU / CVU</label>
                                    <input name="cbu" id="cbu" class="form-control form-control-sm" placeholder="22 dígitos">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">N° CTA AFON / CESE</label>
                                    <input name="cta_cese" id="cta_cese" class="form-control form-control-sm" placeholder="Fondo Cese Laboral">
                                </div>
                            </div>

                            <!-- OBSERVACIONES LABORALES -->
                            <div class="seccion-titulo"><i class="bi bi-chat-left-text me-1"></i> Observaciones Laborales</div>
                            <div class="mb-3">
                                <textarea name="observaciones" id="observaciones" class="form-control form-control-sm" rows="2" placeholder="DETALLE, MOTIVOS DE BAJA, TIPO DE PLAN, VIGENCIA PLAN, OTROS"></textarea>
                            </div>

                            <!-- DOCUMENTACIÓN ADJUNTA -->
                            <div class="seccion-titulo"><i class="bi bi-paperclip me-1"></i> Documentación Adjunta</div>
                            <ul class="nav nav-tabs subtabs-adjuntos mb-2" id="tabAdjuntos">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#adj-documentacion" type="button">Documentación (DNI/Contrato)</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#adj-fotos" type="button">Foto Perfil</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#adj-varios" type="button">Varios / Recibos</button>
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
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="subirAdjunto('VARIOS', 'file_varios')">Subir</button>
                                    </div>
                                    <div id="lista_varios" class="list-group list-group-flush small"></div>
                                </div>
                            </div>

                        </div>

                        <!-- TAB 2: INDUMENTARIA Y COBERTURAS -->
                        <div class="tab-pane fade" id="tab-vencimientos">
                            
                            <!-- TALLES -->
                            <div class="seccion-titulo"><i class="bi bi-shield-check me-1"></i> Talles e Indumentaria</div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Talle Calzado</label>
                                    <input name="calzado_talle" id="calzado_talle" class="form-control form-control-sm" placeholder="Ej: 42">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Talle Pantalón</label>
                                    <input name="pantalon_talle" id="pantalon_talle" class="form-control form-control-sm" placeholder="Ej: 44">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Talle Camisa / Campera</label>
                                    <input name="camisa_talle" id="camisa_talle" class="form-control form-control-sm" placeholder="Ej: XL">
                                </div>
                            </div>

                            <!-- COBERTURAS Y SEGUROS -->
                            <div class="seccion-titulo"><i class="bi bi-hospital me-1"></i> Cobertura Médica, ART y Seguros</div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Obra Social / Prepaga</label>
                                    <input name="obra_social" id="obra_social" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">A.R.T. Compañía</label>
                                    <input name="art_compañia" id="art_compañia" class="form-control form-control-sm">
                                </div>

                                <!-- SEGUROS ACCESORIOS Y PERSONAL -->
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Seguro Acc. Personales (Compañía)</label>
                                    <input name="seguro_acc" id="seguro_acc" class="form-control form-control-sm" placeholder="Aseguradora">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Vencimiento Seguro Acc. Personales</label>
                                    <input type="date" name="vencimiento_art" id="vencimiento_art" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Descripción Cobertura Acc. Personales</label>
                                    <input name="seguro_acc_desc" id="seguro_acc_desc" class="form-control form-control-sm" placeholder="Monto / Detalle de póliza">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small mb-1">Seguro de Vida Obligatorio (SVO Compañía)</label>
                                    <input name="svo" id="svo" class="form-control form-control-sm" placeholder="Aseguradora SVO">
                                </div>
                            </div>

                            <!-- VENCIMIENTOS HABILITACIONES -->
                            <div class="seccion-titulo"><i class="bi bi-calendar-check me-1"></i> Vencimientos y Exámenes</div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Venc. Preocupacional / Periódico</label>
                                    <input type="date" name="vencimiento_preocupacional" id="vencimiento_preocupacional" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Venc. Carnet de Conducir</label>
                                    <input type="date" name="vencimiento_carnet_conducir" id="vencimiento_carnet_conducir" class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- DETALLE PRODUCTORES -->
                            <div class="seccion-titulo"><i class="bi bi-telephone-outbound me-1"></i> Datos de Productores y Contactos de Seguro</div>
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <textarea name="productores_seguro" id="productores_seguro" class="form-control form-control-sm" rows="2" placeholder="Nombre del productor, teléfono de contacto, n° de póliza general, etc."></textarea>
                                </div>
                            </div>

                        </div>

                        <!-- TAB 3: HISTÓRICO DE MOVIMIENTOS -->
                        <div class="tab-pane fade" id="tab-historico">
                            <div id="contenedor-nuevo-movimiento">
                                <div class="bg-light p-3 rounded border mb-3">
                                    <div class="seccion-titulo mb-2"><i class="bi bi-plus-circle me-1"></i> Registrar Evento / Novedad</div>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">Fecha</label>
                                            <input type="date" id="mov_fecha" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small mb-1">Tipo de Evento</label>
                                            <select id="mov_tipo" class="form-select form-select-sm">
                                                <option value="ENTREGA DE ROPA / EPP">ENTREGA DE ROPA / EPP</option>
                                                <option value="APERCIBIMIENTO / LLAMADO ATENCIÓN">APERCIBIMIENTO / LLAMADO ATENCIÓN</option>
                                                <option value="CARTA DOCUMENTO">CARTA DOCUMENTO</option>
                                                <option value="LICENCIA / INASISTENCIA">LICENCIA / INASISTENCIA</option>
                                                <option value="CAMBIO DE PUESTO">CAMBIO DE PUESTO</option>
                                                <option value="OTRO">OTRO</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small mb-1">Detalle / Motivo</label>
                                            <input id="mov_detalle" class="form-control form-control-sm" placeholder="Ej: Se entregan 2 pantalones talle 44 y calzado n° 42">
                                        </div>
                                        <div class="col-12 text-end mt-2">
                                            <button type="button" class="btn btn-sm btn-dark" onclick="guardarMovimiento()">
                                                <i class="bi bi-save me-1"></i> Guardar Novedad
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="tablaMovimientos" class="table table-bordered table-hover table-sm align-middle w-100">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo Evento</th>
                                            <th>Detalle</th>
                                            <th>Registrado Por</th>
                                            <th class="text-center" style="width: 50px;">#</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyMovimientos"></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-dark" id="btnGuardar">Guardar Legajo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalBS;
let tabla;
let clasificacionActual = 'OPERATIVO';

// Cálculo dinámico de años y meses
function calcularTiempo(fechaDesde) {
    if (!fechaDesde) return '-';
    let inicio = new Date(fechaDesde);
    let hoy = new Date();

    if (isNaN(inicio.getTime())) return '-';

    let años = hoy.getFullYear() - inicio.getFullYear();
    let meses = hoy.getMonth() - inicio.getMonth();
    let dias = hoy.getDate() - inicio.getDate();

    if (dias < 0) meses--;
    if (meses < 0) {
        años--;
        meses += 12;
    }

    let partes = [];
    if (años > 0) partes.push(`${años} ${años === 1 ? 'año' : 'años'}`);
    if (meses > 0) partes.push(`${meses} ${meses === 1 ? 'mes' : 'meses'}`);
    if (partes.length === 0) return 'Menos de 1 mes';

    return partes.join(', ');
}

// Formato explícito en dos líneas limpias para la tabla
function renderAntiguedad(row) {
    let antIngreso = calcularTiempo(row.fecha_ingreso);
    let antArca = calcularTiempo(row.fecha_alta_arca);

    return `<div class="text-nowrap" style="font-size: 0.82rem; line-height: 1.3;">
        <span class="text-muted">Ingreso:</span> ${antIngreso}<br>
        <span class="text-muted">Alta:</span> ${antArca}
    </div>`;
}

function filtrarClasificacion(tipo, btn) {
    clasificacionActual = tipo;
    tabla.ajax.url('/contable/ajax/personal.php?accion=listar&clasificacion=' + clasificacionActual).load();
}

window.abrirModal = function(modo) {
    $('#formPersonal')[0].reset();
    $('#id').val('');
    $('#lista_doc, #lista_foto, #lista_varios, #bodyMovimientos').html('');
    $('#btnGuardar, input, select, textarea').prop('disabled', false);
    $('#contenedor-nuevo-movimiento').show();
    $('#tab-basica-btn').tab('show');

    if(modo === 'NUEVO') {
        $('#modalPersonalLabel').html('<i class="bi bi-person-plus me-2"></i> Nuevo Legajo');
        if(clasificacionActual !== 'INACTIVO') {
            $('#clasificacion').val(clasificacionActual);
        }
    }
    modalBS.show();
}

window.editar = function(id, soloVer = false) {
    let d = tabla.rows().data().toArray().find(x => x.id == id);
    if(!d) return;

    $('#formPersonal')[0].reset();
    $('#id').val(d.id);
    $('#clasificacion').val(d.clasificacion);
    $('#cuil').val(d.cuil);
    $('#apellido').val(d.apellido);
    $('#nombre').val(d.nombre);
    $('#puesto').val(d.puesto);
    $('#fecha_nacimiento').val(d.fecha_nacimiento);
    $('#fecha_ingreso').val(d.fecha_ingreso);
    $('#telefono').val(d.telefono);
    $('#email').val(d.email);
    $('#domicilio').val(d.domicilio);
    $('#contacto_emergencia').val(d.contacto_emergencia);
    $('#estado').val(d.estado);

    $('#situacion_laboral').val(d.situacion_laboral);
    $('#procedencia').val(d.procedencia);
    $('#fecha_alta_ieric').val(d.fecha_alta_ieric);
    $('#fecha_alta_arca').val(d.fecha_alta_arca);
    $('#convenio_aplicable').val(d.convenio_aplicable);
    $('#puesto_arca').val(d.puesto_arca);
    $('#seguro_acc').val(d.seguro_acc);
    $('#seguro_acc_desc').val(d.seguro_acc_desc);
    $('#svo').val(d.svo);
    $('#cond_pago').val(d.cond_pago);
    $('#banco').val(d.banco);
    $('#cbu').val(d.cbu);
    $('#cta_cese').val(d.cta_cese);
    
    $('#calzado_talle').val(d.calzado_talle);
    $('#pantalon_talle').val(d.pantalon_talle);
    $('#camisa_talle').val(d.camisa_talle);
    $('#obra_social').val(d.obra_social);
    $('#art_compañia').val(d.art_compañia);
    $('#vencimiento_preocupacional').val(d.vencimiento_preocupacional);
    $('#vencimiento_carnet_conducir').val(d.vencimiento_carnet_conducir);
    $('#vencimiento_art').val(d.vencimiento_art);
    $('#observaciones').val(d.observaciones);
    $('#productores_seguro').val(d.productores_seguro);

    cargarArchivos(d.id);
    cargarMovimientos(d.id);

    if(soloVer) {
        $('#modalPersonalLabel').html('<i class="bi bi-eye me-2"></i> Legajo de Personal (Solo Lectura)');
        $('#btnGuardar').hide();
        $('#contenedor-nuevo-movimiento').hide();
        $('#formPersonal input, #formPersonal select, #formPersonal textarea').prop('disabled', true);
    } else {
        $('#modalPersonalLabel').html('<i class="bi bi-pencil me-2"></i> Editar Legajo');
        $('#btnGuardar').show();
        $('#contenedor-nuevo-movimiento').show();
        $('#formPersonal input, #formPersonal select, #formPersonal textarea').prop('disabled', false);
    }

    $('#tab-basica-btn').tab('show');
    modalBS.show();
}

function cargarMovimientos(pId) {
    $.get('/contable/ajax/personal.php?accion=listar_movimientos', { personal_id: pId }, function(r) {
        let body = $('#bodyMovimientos').empty();
        if(r.data && r.data.length > 0) {
            r.data.forEach(m => {
                let f = m.fecha ? m.fecha.split('-').reverse().join('/') : '-';
                body.append(`
                    <tr>
                        <td class="fw-semibold">${f}</td>
                        <td><span class="badge bg-secondary">${m.tipo_evento}</span></td>
                        <td>${m.detalle}</td>
                        <td><small class="text-muted">${m.usuario || 'Sistema'}</small></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="eliminarMovimiento(${m.id}, ${pId})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        } else {
            body.append(`<tr><td colspan="5" class="text-center text-muted">Sin registros históricos</td></tr>`);
        }
    }, 'json');
}

function guardarMovimiento() {
    let pId = $('#id').val();
    if(!pId) {
        Swal.fire('Atención', 'Guarde el registro de la persona antes de agregar movimientos.', 'warning');
        return;
    }

    let fecha = $('#mov_fecha').val();
    let tipo_evento = $('#mov_tipo').val();
    let detalle = $('#mov_detalle').val();

    if(!detalle) {
        Swal.fire('Atención', 'Complete el detalle del movimiento.', 'warning');
        return;
    }

    $.post('/contable/ajax/personal.php?accion=guardar_movimiento', { personal_id: pId, fecha, tipo_evento, detalle }, function() {
        $('#mov_detalle').val('');
        cargarMovimientos(pId);
    });
}

function eliminarMovimiento(id, pId) {
    if(confirm('¿Eliminar este registro del histórico?')) {
        $.post('/contable/ajax/personal.php?accion=eliminar_movimiento', { id }, function() {
            cargarMovimientos(pId);
        });
    }
}

function cargarArchivos(pId) {
    $.get('/contable/ajax/personal.php?accion=listar_archivos', { personal_id: pId }, function(r) {
        let lDoc = $('#lista_doc').empty();
        let lFoto = $('#lista_foto').empty();
        let lVar = $('#lista_varios').empty();

        if(r.archivos) {
            r.archivos.forEach(a => {
                let url = '/contable/uploads/personal/' + a.archivo;
                let item = `<div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <a href="${url}" target="_blank" class="text-truncate text-decoration-none" style="max-width: 80%;">
                        <i class="bi bi-file-earmark me-1"></i> ${a.nombre_original}
                    </a>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="eliminarArchivo(${a.id}, ${pId})">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>`;

                if(a.tipo_adjunto === 'DOCUMENTO') lDoc.append(item);
                else if(a.tipo_adjunto === 'VARIOS') lVar.append(item);
                else if(a.tipo_adjunto === 'FOTO') {
                    lFoto.append(`<div class="col-3 position-relative">
                        <a href="${url}" target="_blank">
                            <img src="${url}" class="img-thumbnail w-100" style="height: 70px; object-fit: cover;">
                        </a>
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 py-0 px-1" onclick="eliminarArchivo(${a.id}, ${pId})">&times;</button>
                    </div>`);
                }
            });
        }
    }, 'json');
}

function subirAdjunto(tipo, inputId) {
    let pId = $('#id').val();
    if(!pId) {
        Swal.fire('Atención', 'Guarde el registro antes de adjuntar archivos.', 'warning');
        return;
    }
    let fileInput = document.getElementById(inputId);
    if(!fileInput.files[0]) return;

    let formData = new FormData();
    formData.append('personal_id', pId);
    formData.append('tipo_adjunto', tipo);
    formData.append('archivo', fileInput.files[0]);

    $.ajax({
        url: '/contable/ajax/personal.php?accion=subir_adjunto',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function() {
            fileInput.value = '';
            cargarArchivos(pId);
        }
    });
}

function eliminarArchivo(id, pId) {
    if(confirm('¿Eliminar este archivo adjunto?')) {
        $.post('/contable/ajax/personal.php?accion=eliminar_archivo', { id }, function() {
            cargarArchivos(pId);
        });
    }
}

window.eliminar = function(id) {
    if(confirm('¿Desea eliminar a esta persona del sistema?')) {
        $.post('/contable/ajax/personal.php?accion=eliminar', { id }, function() {
            tabla.ajax.reload(null, false);
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    modalBS = new bootstrap.Modal(document.getElementById('modalPersonal'));

    tabla = $('#tablaPersonal').DataTable({
        ajax: {
            url: '/contable/ajax/personal.php?accion=listar&clasificacion=OPERATIVO',
            dataSrc: function(json) {
                if(clasificacionActual === 'OPERATIVO') $('#cant-operativo').text(json.data.length);
                else if(clasificacionActual === 'ADMINISTRATIVO') $('#cant-administrativo').text(json.data.length);
                else if(clasificacionActual === 'MAQUINISTA') $('#cant-maquinistas').text(json.data.length);
                else if(clasificacionActual === 'OBRAS / CAMPO') $('#cant-obras').text(json.data.length);
                else if(clasificacionActual === 'INACTIVO') $('#cant-inactivos').text(json.data.length);
                return json.data;
            }
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel', className: 'btn btn-success btn-sm', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] } },
            { extend: 'print', text: '<i class="bi bi-printer me-1"></i> Imprimir', className: 'btn btn-secondary btn-sm', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] } },
            { extend: 'colvis', text: 'Columnas', className: 'btn btn-sm btn-secondary' }
        ],
        columns: [
            { data: 'id' },
            { data: 'cuil', render: d => d ? `<strong>${d}</strong>` : '-' },
            { data: null, render: d => `${d.apellido}, ${d.nombre}` },
            { data: 'puesto', render: d => d || '-' },
            { data: 'telefono', render: d => d || '-' },
            { data: 'fecha_ingreso', render: d => d ? d.split('-').reverse().join('/') : '-' },
            { data: null, render: d => renderAntiguedad(d) },
            { data: 'usuario_creador', render: d => `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person me-1"></i> ${d || 'Sistema'}</span>` },
            { data: 'estado', render: d => `<span class="badge ${d === 'ACTIVO' ? 'bg-success' : (d === 'LICENCIA' ? 'bg-warning text-dark' : 'bg-secondary')}">${d}</span>` },
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

    $('#formPersonal').submit(function(e) {
        e.preventDefault();
        $.post('/contable/ajax/personal.php?accion=guardar', $(this).serialize(), function(r) {
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