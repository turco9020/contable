<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Conexión a la base de datos
require_once __DIR__ . '/../../config/database.php';
$conexion = $conn; 

// 2. Encabezado e interfaz global
require_once __DIR__ . '/../../includes/header.php';

// 3. Menú Lateral (Sidebar)
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="content">

    <!-- CABECERA DEL MÓDULO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-calendar-check text-secondary me-2"></i> Control de Vencimientos y Pagos
        </h4>
        <button class="btn btn-dark d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalVencimiento">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Vencimiento / Gasto
        </button>
    </div>

    <!-- MENSAJES DE ALERTA -->
    <?php if (isset($_GET['res'])): ?>
        <?php if ($_GET['res'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Operación realizada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['res'] === 'success_pago'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                <i class="bi bi-cash-stack me-2"></i> Pago registrado con éxito y movimiento de caja asentado.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['res'] === 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Ocurrió un error al procesar la solicitud.' ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- CONTENEDOR DE LA TABLA -->
    <div class="card p-3 shadow-sm border-0">
        <div class="table-responsive">
            <table id="tablaVencimientos" class="table table-bordered table-striped w-100 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 35px;" class="text-center"></th> <!-- Columna para el botón + -->
                        <th>ID</th>
                        <th>Concepto / Título</th>
                        <th>Cuota</th>
                        <th>Categoría / Subcat.</th>
                        <th>Proveedor</th>
                        <th>Monto</th>
                        <th>F. Vencimiento</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consulta SQL adaptada al DESCRIBE exacto de tu tabla
                    $query = "SELECT v.*, 
                                    p.nombre AS proveedor_nombre, 
                                    c.nombre AS cat_nombre, 
                                    s.nombre AS subcat_nombre,
                                    (SELECT COUNT(*) FROM vencimientos cuotas WHERE cuotas.vencimiento_padre_id = v.id) AS total_hijos
                            FROM vencimientos v 
                            LEFT JOIN proveedores p ON v.proveedor_id = p.id 
                            LEFT JOIN categorias c ON v.categoria_id = c.id
                            LEFT JOIN subcategorias s ON v.subcategoria_id = s.id
                            WHERE (v.vencimiento_padre_id IS NULL OR v.vencimiento_padre_id = 0 OR v.vencimiento_padre_id = '')
                            ORDER BY v.fecha_vencimiento ASC";

                    $res = mysqli_query($conexion, $query);
                    $hoy = new DateTime();

                    if ($res && mysqli_num_rows($res) > 0) {
                        while ($r = mysqli_fetch_assoc($res)) {
                            // Manejo seguro de fechas para evitar errores fatales
                            $fecha_venc_str = $r['fecha_vencimiento'] ? $r['fecha_vencimiento'] : date('Y-m-d');
                            $fecha_venc = new DateTime($fecha_venc_str);
                            $es_vencido = ($fecha_venc < $hoy && $r['estado'] === 'PENDIENTE');

                            // 1. Badge de Cuotas
                            $total_cuotas = (int)($r['total_cuotas'] ?? 1);
                            $total_hijos  = (int)($r['total_hijos'] ?? 0);

                            $badge_cuota = ($total_cuotas > 1 || $total_hijos > 0) 
                                ? "<span class='badge bg-info text-dark'>{$total_cuotas} Cuotas</span>" 
                                : "<span class='badge bg-light text-muted border'>Única</span>";

                            // 2. Badge de Estado (coincidiendo con tu ENUM: 'PENDIENTE', 'PAGADO', 'ANULADO')
                            if ($r['estado'] === 'PAGADO') {
                                $badge_estado = "<span class='badge bg-success'>PAGADO</span>";
                            } elseif ($r['estado'] === 'ANULADO') {
                                $badge_estado = "<span class='badge bg-secondary'>ANULADO</span>";
                            } else {
                                $badge_estado = $es_vencido 
                                    ? "<span class='badge bg-danger'>VENCIDO</span>" 
                                    : "<span class='badge bg-warning text-dark'>PENDIENTE</span>";
                            }

                            // 3. Categoría y Subcategoría
                            $cat_texto = !empty($r['cat_nombre']) ? htmlspecialchars($r['cat_nombre']) : 'General';
                            if (!empty($r['subcat_nombre'])) {
                                $cat_texto .= " <small class='text-muted'>(" . htmlspecialchars($r['subcat_nombre']) . ")</small>";
                            }

                            // 4. Proveedor
                            $prov_texto = !empty($r['proveedor_nombre']) ? htmlspecialchars($r['proveedor_nombre']) : '<em class="text-muted">N/A</em>';
                        ?>
                            <tr data-id="<?= $r['id'] ?>">
                                <!-- Columna 1: Botón Expandir -->
                                <td class="text-center">
                                    <?php if ($total_hijos > 0 || $total_cuotas > 1): ?>
                                        <button class="btn btn-sm btn-outline-primary btn-expandir-cuotas" title="Ver desglose de cuotas">
                                            <i class="bi bi-plus-circle-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>

                                <!-- Columna 2: ID -->
                                <td class="fw-bold text-secondary">#<?= $r['id'] ?></td>

                                <!-- Columna 3: Título y Descripción -->
                                <td>
                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($r['titulo']) ?></span>
                                    <?php if (!empty($r['descripcion'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars($r['descripcion']) ?></small>
                                    <?php endif; ?>
                                </td>

                                <!-- Columna 4: Cuota -->
                                <td><?= $badge_cuota ?></td>

                                <!-- Columna 5: Categoría / Subcat -->
                                <td><?= $cat_texto ?></td>

                                <!-- Columna 6: Proveedor -->
                                <td><?= $prov_texto ?></td>

                                <!-- Columna 7: Monto -->
                                <td class="fw-bold text-dark">$ <?= number_format((float)$r['monto'], 2, ',', '.') ?></td>

                                <!-- Columna 8: Fecha Vencimiento -->
                                <td><?= date('d/m/Y', strtotime($fecha_venc_str)) ?></td>

                                <!-- Columna 9: Estado -->
                                <td><?= $badge_estado ?></td>

                                <!-- Columna 10: Acciones -->
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <?php if ($r['estado'] === 'PENDIENTE'): ?>
                                            <button type="button" 
                                                class="btn btn-sm btn-outline-success btn-pagar-vencimiento" 
                                                title="Pagar Vencimiento - Registrar Gasto"
                                                data-id="<?= $r['id'] ?>" 
                                                data-titulo="<?= htmlspecialchars($r['titulo']) ?>" 
                                                data-monto="<?= $r['monto'] ?>"
                                                data-categoria="<?= $r['categoria_id'] ?? '' ?>"
                                                data-subcategoria="<?= $r['subcategoria_id'] ?? '' ?>"
                                                data-proveedor="<?= $r['proveedor_id'] ?? '' ?>">
                                                <i class="bi bi-cash-coin"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if (!empty($r['archivo'])): ?>
                                            <a href="/contable/uploads/vencimientos/<?= $r['archivo'] ?>" 
                                            target="_blank" 
                                            class="btn btn-sm btn-outline-dark" 
                                            title="Ver Adjunto">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        <?php endif; ?>

                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarVencimiento(<?= $r['id'] ?>)">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                        } 
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php 
include_once '../../includes/modal_gasto.php'; 
include_once 'modal_vencimiento.php'; 
include_once '../../includes/footer.php';
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Ocultar la alerta de Bootstrap a los 3 segundos
    setTimeout(function() {
        let alertEl = document.querySelector('.alert');
        if (alertEl) {
            let bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
            bsAlert.close();
        }
    }, 3000);

    // 2. Limpiar la URL para que no reaparezca la alerta al refrescar (F5)
    if (window.location.search.includes('res=')) {
        let cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});
    
let tablaVencimientos;
let buscadoresTom = {};

// --- FUNCIONES ESPECÍFICAS PARA EL FORMULARIO DE GASTO ---
function cargarTiposGasto(){
    return $.get('/contable/ajax/tipos_comprobante.php?accion=listar', r=>{
        let s = $('#formGasto #tipo_comprobante_id');
        s.empty().append('<option value="">Seleccione</option>');
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
}

function cargarMediosGasto(){
    return $.get('/contable/ajax/medios_pago.php?accion=listar', r=>{
        let s = $('#formGasto #medio_pago_id');
        s.empty().append('<option value="">Seleccione</option>');
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
}

function cargarCentrosGasto(){
    return $.get('/contable/ajax/centros.php?accion=listar', r=>{
        let s = $('#formGasto #centro_costo_id'); 
        s.empty().append(`<option value="">-- Seleccionar --</option>`);
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
}

function cargarObrasGasto(){
    return $.get('/contable/ajax/obras.php?accion=listar', r=>{
        let s = $('#formGasto #obra_id');
        s.empty().append('<option value="">-- Seleccionar --</option>');
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
} 

function cargarCategoriasGasto(){
    return $.get('/contable/ajax/categorias.php?accion=listar', r=>{
        let s = $('#formGasto #categoria_id');
        s.empty().append(`<option value="">-- Seleccionar --</option>`);
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
}

function cargarSubcategoriasGasto(categoria_id){
    let s = $('#formGasto #subcategoria_id');
    
    if (buscadoresTom['#formGasto #subcategoria_id']) {
        buscadoresTom['#formGasto #subcategoria_id'].destroy();
        delete buscadoresTom['#formGasto #subcategoria_id'];
    }

    if(!categoria_id){
        s.html(`<option value="">-- Seleccionar --</option>`);
        return $.Deferred().resolve();
    }

    return $.get('/contable/ajax/get_subcategorias.php', {categoria_id}, r=>{
        s.empty().append(`<option value="">-- Seleccionar --</option>`);
        r.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
}

function cargarProveedoresGasto(){
    return $.get('/contable/ajax/get_proveedores.php', r=>{
        let s = $('#formGasto #proveedor_id');
        s.empty().append('<option value="">-- Seleccionar --</option>');
        r.forEach(p=>{ s.append(`<option value="${p.id}">${p.nombre} (${p.cuit})</option>`); });
    },'json');
}

function cargarCajasGasto(){
    return $.get('/contable/ajax/cajas.php?accion=listar', function(r){
        let s = $('#formGasto #caja_id');
        s.empty().append('<option value="">Seleccionar</option>');
        r.data.forEach(x=>{
            s.append(`<option value="${x.id}">${x.nombre}</option>`);
        });
    },'json');
}

function aplicarBuscadoresGasto() {
    const IDs = [
        '#formGasto #proveedor_id', 
        '#formGasto #centro_costo_id', 
        '#formGasto #obra_id', 
        '#formGasto #categoria_id', 
        '#formGasto #subcategoria_id'
    ];
    
    IDs.forEach(id => {
        let el = document.querySelector(id);
        if (!el) return;

        if (buscadoresTom[id]) {
            buscadoresTom[id].destroy();
        }

        if (typeof TomSelect !== 'undefined') {
            buscadoresTom[id] = new TomSelect(el, {
                create: false,
                sortField: { field: "text", order: "asc" },
                placeholder: "-- Seleccionar o Buscar --",
                allowEmptyOption: true
            });
        }
    });
}

document.addEventListener("DOMContentLoaded", function(){

    tablaVencimientos = $('#tablaVencimientos').DataTable({
        responsive: true,
        autoWidth: false,
        dom: 'Bfrtip',
        columnDefs: [
            { orderable: false, targets: [0, 9] } // Desactiva el ordenamiento en la columna del "+" y en "Acciones"
        ],
        buttons: [
            { 
                extend: 'excel', 
                text: 'Excel', 
                className: 'btn btn-sm btn-success', 
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } // Exporta solo columnas de datos
            },
            { 
                extend: 'pdf', 
                text: 'PDF', 
                className: 'btn btn-sm btn-secondary', 
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } 
            },
            { 
                extend: 'print', 
                text: 'Imprimir', 
                className: 'btn btn-sm btn-secondary', 
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } 
            }
        ],
        order: [[ 7, "asc" ]], // Ordena por la columna de Fecha Vencimiento
        pageLength: 15,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
    });

    $(window).on('resize', function () {
        if (tablaVencimientos) {
            tablaVencimientos.columns.adjust().responsive.recalc();
        }
    });

    // --- ACCIÓN: EXPANDIR / COLAPSAR CUOTAS HIJAS ---
    $(document).on('click', '.btn-expandir-cuotas', function () {
        let tr = $(this).closest('tr');
        let row = tablaVencimientos.row(tr);
        let idPadre = tr.data('id');
        let icon = $(this).find('i');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('bi-dash-circle-fill').addClass('bi-plus-circle-fill');
        } else {
            icon.removeClass('bi-plus-circle-fill').addClass('bi-arrow-repeat spin');

            $.get('ajax_cuotas.php', { padre_id: idPadre }, function (res) {
                let htmlCuotas = `
                    <div class="p-3 bg-light rounded border border-2 border-primary-subtle ms-4 my-2 shadow-sm">
                        <h6 class="fw-bold mb-2 text-primary d-flex align-items-center">
                            <i class="bi bi-layers me-2"></i> Desglose de Cuotas
                        </h6>
                        <table class="table table-sm table-hover bg-white mb-0 border align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Cuota N°</th>
                                    <th>F. Vencimiento</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>`;

                if (res.data && res.data.length > 0) {
                    res.data.forEach(c => {
                        let badgeEstado = c.estado === 'PAGADO' 
                            ? '<span class="badge bg-success">PAGADO</span>' 
                            : '<span class="badge bg-warning text-dark">PENDIENTE</span>';

                        let btnPagar = c.estado === 'PENDIENTE' ? `
                            <button type="button" 
                                    class="btn btn-sm btn-outline-success btn-pagar-vencimiento py-0 px-2" 
                                    data-id="${c.id}" 
                                    data-titulo="${c.titulo} (Cuota ${c.nro_cuota}/${c.total_cuotas})" 
                                    data-monto="${c.monto}"
                                    data-categoria="${c.categoria_id}"
                                    data-subcategoria="${c.subcategoria_id}"
                                    data-proveedor="${c.proveedor_id}">
                                <i class="bi bi-cash-coin"></i> Pagar
                            </button>` : '';

                        let fVenc = c.fecha_vencimiento ? c.fecha_vencimiento.split('-').reverse().join('/') : '-';

                        htmlCuotas += `
                            <tr>
                                <td><b>Cuota ${c.nro_cuota}</b> / ${c.total_cuotas}</td>
                                <td>${fVenc}</td>
                                <td class="fw-bold">$ ${parseFloat(c.monto).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${badgeEstado}</td>
                                <td class="text-end">${btnPagar}</td>
                            </tr>`;
                    });
                } else {
                    htmlCuotas += `<tr><td colspan="5" class="text-center text-muted">No se encontraron cuotas adicionales.</td></tr>`;
                }

                htmlCuotas += `</tbody></table></div>`;

                row.child(htmlCuotas).show();
                tr.addClass('shown');
                icon.removeClass('bi-arrow-repeat spin').addClass('bi-dash-circle-fill');
            }, 'json');
        }
    });

    // Cambio dinámico de categoría en modal gasto
    $(document).on('change', '#formGasto #categoria_id', function() {
        let catId = $(this).val();
        cargarSubcategoriasGasto(catId).done(() => {
            let el = document.querySelector('#formGasto #subcategoria_id');
            if (el && typeof TomSelect !== 'undefined') {
                if (buscadoresTom['#formGasto #subcategoria_id']) {
                    buscadoresTom['#formGasto #subcategoria_id'].destroy();
                }
                buscadoresTom['#formGasto #subcategoria_id'] = new TomSelect(el, {
                    create: false,
                    sortField: { field: "text", order: "asc" },
                    placeholder: "-- Seleccionar o Buscar --",
                    allowEmptyOption: true
                });
            }
        });
    });

    // Cálculo dinámico de totales en el modal de gasto
    $('#formGasto #neto, #formGasto #iva, #formGasto #ret_iibb, #formGasto #otros_tributos').on('input', function() {
        const limpiarNum = (val) => {
            if (!val) return 0;
            let s = val.toString().replace(/[^\d,.-]/g, '');
            if (s.includes(',') && s.includes('.')) {
                s = s.replace(/\./g, '');
            } else if ((s.match(/\./g) || []).length > 1) {
                s = s.replace(/\./g, '');
            }
            s = s.replace(',', '.');
            return parseFloat(s) || 0;
        };

        let neto  = limpiarNum($('#formGasto #neto').val());
        let iva   = limpiarNum($('#formGasto #iva').val());
        let iibb  = limpiarNum($('#formGasto #ret_iibb').val());
        let otros = limpiarNum($('#formGasto #otros_tributos').val());

        let sumaTotal = neto + iva + iibb + otros;

        $('#formGasto #total').val(sumaTotal.toLocaleString('es-AR', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        }));
    });

    // Submit del formulario de Gastos
    $('#formGasto').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: '/contable/ajax/gastos.php?accion=guardar',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(resp) {
                const modalGasto = bootstrap.Modal.getInstance(document.getElementById('modalGasto'));
                if (modalGasto) modalGasto.hide();

                Swal.fire({
                    title: '¡Pago Registrado!',
                    text: 'El vencimiento ha sido pagado y el gasto registrado en caja.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        });
    });

    // ACCIÓN: PRESIONAR BOTÓN "PAGAR" (MONEDITA)
    $(document).on('click', '.btn-pagar-vencimiento', function() {
        const id = $(this).data('id');
        const titulo = $(this).data('titulo');
        const monto = parseFloat($(this).data('monto')) || 0;
        const categoriaId = $(this).data('categoria');
        const subcategoriaId = $(this).data('subcategoria');
        const proveedorId = $(this).data('proveedor');

        // Resetear formulario
        $('#formGasto')[0].reset();
        
        // Asignar Vencimiento ID oculto
        if ($('#vencimiento_id_gasto').length === 0) {
            $('#formGasto').append(`<input type="hidden" name="vencimiento_id" id="vencimiento_id_gasto" value="${id}">`);
        } else {
            $('#vencimiento_id_gasto').val(id);
        }

        // Destruir búsquedas TomSelect previas para refrescar
        Object.keys(buscadoresTom).forEach(k => {
            if (buscadoresTom[k]) buscadoresTom[k].destroy();
        });
        buscadoresTom = {};

        // Cargar combos específicos usando Promise.all nativo
        Promise.all([
            cargarCentrosGasto(), 
            cargarCajasGasto(), 
            cargarTiposGasto(), 
            cargarMediosGasto(), 
            cargarObrasGasto(), 
            cargarCategoriasGasto(), 
            cargarProveedoresGasto()
        ]).then(function() {
            // Precargar datos
            $('#formGasto #detalle').val('Pago Vencimiento: ' + titulo);
            $('#formGasto #fecha').val(new Date().toISOString().split('T')[0]);
            $('#formGasto #neto').val(monto.toFixed(2)).trigger('input');

            if (proveedorId) $('#formGasto #proveedor_id').val(proveedorId);
            if (categoriaId) $('#formGasto #categoria_id').val(categoriaId);

            let promesaSubcat = categoriaId ? cargarSubcategoriasGasto(categoriaId) : cargarSubcategoriasGasto('');

            promesaSubcat.done(() => {
                if (subcategoriaId) $('#formGasto #subcategoria_id').val(subcategoriaId);
                
                aplicarBuscadoresGasto();

                if (buscadoresTom['#formGasto #proveedor_id'] && proveedorId) {
                    buscadoresTom['#formGasto #proveedor_id'].setValue(proveedorId, true);
                }
                if (buscadoresTom['#formGasto #categoria_id'] && categoriaId) {
                    buscadoresTom['#formGasto #categoria_id'].setValue(categoriaId, true);
                }
                if (buscadoresTom['#formGasto #subcategoria_id'] && subcategoriaId) {
                    buscadoresTom['#formGasto #subcategoria_id'].setValue(subcategoriaId, true);
                }
            });

            // Abrir Modal de Gasto
            const modalGasto = new bootstrap.Modal(document.getElementById('modalGasto'));
            modalGasto.show();
        }).catch(err => {
            console.error("Error al cargar combos del modal:", err);
        });
    });

});

function eliminarVencimiento(id) {
    Swal.fire({
        title: '¿Confirmación crítica?',
        text: 'Para eliminar este registro permanentemente, escribe la palabra "ELIMINAR":',
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Escribe ELIMINAR aquí...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#212529',
        confirmButtonText: 'Confirmar eliminación',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) return '¡Debes escribir la palabra de confirmación!';
            if (value !== 'ELIMINAR') return 'La palabra no coincide. Intenta de nuevo (en mayúsculas).';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `acciones.php?accion=eliminar&id=${id}`;
        }
    });
}
</script>