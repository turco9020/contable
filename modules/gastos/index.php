<?php
include '../../includes/header.php';
include '../../includes/sidebar.php';

$esAdmin = isset($_SESSION['rol']) && strcasecmp($_SESSION['rol'], 'admin') === 0;
?>

<div class="content">

    <!-- CABECERA DEL MÓDULO -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark mb-0">
        <i class="bi bi-calculator text-secondary me-2"></i> Gestión de Gastos
    </h4>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalImportarAFIP">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i> Importar AFIP
        </button>
        <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal()">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Gasto
        </button>
    </div>
</div>

    <!-- SECCIÓN DE FILTROS -->
    <div class="card p-3 shadow-sm mb-3 border-0">
        <div class="row g-2">
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Desde</label>
                <input type="date" id="f_desde" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Hasta</label>
                <input type="date" id="f_hasta" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Centro</label>
                <select id="f_centro" class="form-select form-select-sm"></select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Categoría</label>
                <select id="f_categoria" class="form-select form-select-sm"></select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Obra</label>
                <select id="f_obra" class="form-select form-select-sm"></select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-sm btn-dark w-100" onclick="aplicarFiltros()">Filtrar</button>
                <button class="btn btn-sm btn-outline-secondary ms-1" onclick="limpiarFiltros()" title="Limpiar Filtros">X</button>
            </div>
        </div>
    </div>

    <!-- CONTENEDOR DE LA TABLA -->
    <div class="card p-3 shadow-sm border-0">
        <div class="table-responsive">
            <table id="tablaGastos" class="table table-bordered table-striped w-100">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo comprobante</th>
                        <th>Número</th>         
                        <th>Medio de pago</th>
                        <th>Proveedor</th>
                        <th>Detalle</th>
                        <th>Neto</th>
                        <th>IVA</th>
                        <th>Ret IIBB</th>
                        <th>Otros tributos</th>         
                        <th>Total</th>
                        <th>Centro</th>
                        <th>Obra</th>
                        <th>Categoria</th>
                        <th>Subcategoria</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/modal_gasto.php'; ?>
<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/modal_importar.php'; ?>
<?php include '../../includes/footer.php'; ?>

<script>
let modalGasto;
let tabla;
let buscadoresTom = {};
const esAdmin = <?php echo $esAdmin ? 'true' : 'false'; ?>;

window.aplicarFiltros = function() {
    tabla.ajax.reload();
}

window.limpiarFiltros = function() {
    $('#f_desde, #f_hasta').val('');
    $('#f_centro, #f_categoria, #f_obra').val('');
    tabla.ajax.reload();
}

function renderMoneda(val) {
    if (!val || val == 0) return '$ 0,00';
    let limpio = val.toString().replace(/[^0-9.-]+/g, "");
    let num = parseFloat(limpio);
    if (isNaN(num)) return '$ 0,00';

    return '$ ' + num.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function cargarTipos(){
    return $.get('/contable/ajax/tipos_comprobante.php?accion=listar', r=>{
        let s = $('#tipo_comprobante_id');
        s.empty().append('<option value="">Seleccione</option>');
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
}

function cargarMedios(){
    return $.get('/contable/ajax/medios_pago.php?accion=listar', r=>{
        let s = $('#medio_pago_id');
        s.empty().append('<option value="">Seleccione</option>');
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
}

function cargarCentros(){
    return $.get('/contable/ajax/centros.php?accion=listar', r=>{
        let s = $('#centro_costo_id, #f_centro'); 
        s.empty().append(`<option value="">-- Seleccionar --</option>`);
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
}

function cargarObras(){
    return $.get('/contable/ajax/obras.php?accion=listar', r=>{
        let s = $('#obra_id, #f_obra');
        s.empty().append('<option value="">-- Seleccionar --</option>');
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
} 

function cargarCategorias(){
    return $.get('/contable/ajax/categorias.php?accion=listar', r=>{
        let s = $('#categoria_id, #f_categoria');
        s.empty().append(`<option value="">-- Seleccionar --</option>`);
        r.data.forEach(x=>{ s.append(`<option value="${x.id}">${x.nombre}</option>`); });
    },'json');
}

function cargarSubcategorias(categoria_id){
    let s = $('#subcategoria_id');
    
    if (buscadoresTom['#subcategoria_id']) {
        buscadoresTom['#subcategoria_id'].destroy();
        delete buscadoresTom['#subcategoria_id'];
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

function cargarProveedores(){
    return $.get('/contable/ajax/get_proveedores.php', r=>{
        let s=$('#proveedor_id');
        s.empty().append('<option value="">-- Seleccionar --</option>');
        r.forEach(p=>{ s.append(`<option value="${p.id}">${p.nombre} (${p.cuit})</option>`); });
    },'json');
}

function cargarCajas(selector = '#caja_id'){
    $.get('/contable/ajax/cajas.php?accion=listar', function(r){
        let s = $(selector);
        s.empty();
        s.append('<option value="">Seleccionar</option>');
        r.data.forEach(x=>{
            s.append(`<option value="${x.id}">${x.nombre}</option>`);
        });
    },'json');
}

function aplicarBuscadores() {
    const IDs = ['#proveedor_id', '#centro_costo_id', '#obra_id', '#categoria_id', '#subcategoria_id'];
    
    IDs.forEach(id => {
        let el = document.querySelector(id);
        if (!el) return;

        if (buscadoresTom[id]) {
            buscadoresTom[id].destroy();
        }

        buscadoresTom[id] = new TomSelect(el, {
            create: false,
            sortField: { field: "text", order: "asc" },
            placeholder: "-- Seleccionar o Buscar --",
            allowEmptyOption: true
        });
    });
}

window.abrirModal = function(){
    $('#formGasto')[0].reset();
    $('#id').val('');
    $('#archivo_actual').html('');
    $('#btnEliminarArchivo').hide();
    $('#formGasto input, select').prop('disabled', false);
    $('#formGasto button[type="submit"], #formGasto .btn-dark').show();
    
    $.when(cargarCentros(), cargarCajas(), cargarTipos(), cargarMedios(), cargarObras(), cargarCategorias(), cargarProveedores())
     .done(() => { 
         cargarSubcategorias('').done(() => {
             modalGasto.show();
             setTimeout(aplicarBuscadores, 150); 
         });
     });
}

document.addEventListener("DOMContentLoaded", function() {
    modalGasto = new bootstrap.Modal(document.getElementById('modalGasto'));

    tabla = $('#tablaGastos').DataTable({
        responsive: true,
        scrollX: false,
        autoWidth: false,
        dom: 'Bfrtip',
        order: [[1, 'desc']],
        buttons: [
            { 
                extend: 'excel', 
                text: 'Excel', 
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17]
                }
            },
            {   extend: 'print', 
                text: 'Imprimir', 
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17]
                }
            },
            { 
                extend: 'colvis', 
                text: 'Columnas', 
                className: 'btn btn-sm btn-secondary' 
            }
        ],
        ajax: {
            url: '/contable/ajax/gastos.php?accion=listar',
            data: function(d) {
                d.f_desde = $('#f_desde').val();
                d.f_hasta = $('#f_hasta').val();
                d.f_centro = $('#f_centro').val();
                d.f_categoria = $('#f_categoria').val();
                d.f_obra = $('#f_obra').val();
            }
        },
        columns: [
            { data: 'id', visible: true },
            { data: 'fecha', render: function(d, type) { 
                if (type === 'display' && d) { let p = d.split('-'); return `${p[2]}/${p[1]}/${p[0]}`; }
                return d;
            }},
            { data: 'tipo_comprobante', className: 'none' },
            { data: 'numero_comprobante', className: 'none' },
            { data: 'medio_pago', className: 'none' },
            { data: 'proveedor', defaultContent: '' },
            { data: 'detalle', className: 'none' },
            { data: 'neto', className: 'none', render: d => renderMoneda(d) },
            { data: 'iva', className: 'text-end', render: d => renderMoneda(d) },
            { data: 'ret_iibb', className: 'none', render: d => renderMoneda(d) },
            { data: 'otros_tributos', className: 'none', render: d => renderMoneda(d) },
            { data: 'total', className: 'text-end fw-bold', render: d => renderMoneda(d) },
            { data: 'centro' },
            { data: 'obra' },
            { data: 'categoria' },
            { data: 'subcategoria' },
            {
                data: 'usuario_nombre',
                visible: true,
                render: function(d) {
                    return d ? `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i> ${d}</span>` : '<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i>Sistema</span>';
                }
            },
            {
                data: 'estado_validacion',
                render: function(d) {
                    if (d === 'PENDIENTE') {
                        return '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pendiente</span>';
                    }
                    return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aprobado</span>';
                }
            },
            { 
                data: null, 
                orderable: false, 
                className: 'text-end',
                render: function(data) {
                    let btnArchivo = data.archivo ? `<a href="/contable/uploads/gastos/${data.archivo}" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver Adjunto Gasto"><i class="bi bi-file-earmark-pdf"></i></a>` : '';
                    
                    let btnAprobar = '';
                    if (esAdmin && data.estado_validacion === 'PENDIENTE') {
                        btnAprobar = `
                            <button class="btn btn-sm btn-success" title="Aprobar Gasto (> $800k)" onclick="aprobarGasto(${data.id})">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        `;
                    }

                    return `
                        <div class="d-inline-flex gap-1 justify-content-end">
                            ${btnAprobar}
                            ${btnArchivo}
                            <button class="btn btn-sm btn-outline-secondary" title="Ver Gasto" onclick='ver(${JSON.stringify(data)})'>
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" title="Editar Gasto" onclick='editar(${JSON.stringify(data)})'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar Gasto" onclick="eliminar(${data.id})">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $(window).on('resize', function () {
        tabla.columns.adjust().responsive.recalc();
    });

    $(document).on('change', '#categoria_id', function() {
        let catId = $(this).val();
        cargarSubcategorias(catId).done(() => {
            let el = document.querySelector('#subcategoria_id');
            if (el) {
                buscadoresTom['#subcategoria_id'] = new TomSelect(el, {
                    create: false,
                    sortField: { field: "text", order: "asc" },
                    placeholder: "-- Seleccionar o Buscar --",
                    allowEmptyOption: true
                });
            }
        });
    });

    $('#neto, #iva, #ret_iibb, #otros_tributos').on('input', function() {
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

        let neto  = limpiarNum($('#neto').val());
        let iva   = limpiarNum($('#iva').val());
        let iibb  = limpiarNum($('#ret_iibb').val());
        let otros = limpiarNum($('#otros_tributos').val());

        let sumaTotal = neto + iva + iibb + otros;

        $('#total').val(sumaTotal.toLocaleString('es-AR', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        }));
    });

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
                tabla.ajax.reload();
                modalGasto.hide();

                if (resp.estado_validacion === 'PENDIENTE') {
                    Swal.fire({
                        title: 'Gasto registrado',
                        text: 'Al superar los $800.000, el gasto ha quedado registrado en estado "Pendiente de Validación" hasta ser verificado por el Administrador.',
                        icon: 'info',
                        confirmButtonColor: '#212529'
                    });
                } else {
                    Swal.fire({
                        title: '¡Guardado!',
                        text: 'El gasto fue guardado correctamente.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            }
        });
    });

    window.aprobarGasto = function(id) {
        Swal.fire({
            title: '¿Validar este gasto?',
            text: 'Confirmas que revisaste este gasto de gran porte y autorizas su validación.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#212529',
            confirmButtonText: 'Sí, validar gasto',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/contable/ajax/gastos.php?accion=aprobar', { id }, function(res) {
                    if (res.status === 'OK') {
                        Swal.fire({
                            title: '¡Aprobado!',
                            text: 'El gasto ha sido validado exitosamente.',
                            icon: 'success',
                            confirmButtonColor: '#212529'
                        });
                        tabla.ajax.reload();
                    } else {
                        Swal.fire('Error', res.message || 'No se pudo validar.', 'error');
                    }
                }, 'json');
            }
        });
    }

    window.ver = function(data) {
        window.editar(data);
        setTimeout(() => {
            $('#formGasto input, select').prop('disabled', true);
            Object.keys(buscadoresTom).forEach(key => {
                if(buscadoresTom[key]) buscadoresTom[key].disable();
            });
            $('#formGasto button[type="submit"], #btnEliminarArchivo').hide();
        }, 500); 
    }

    window.editar = function(data) {
        $('#formGasto')[0].reset();
        $('#formGasto input, select').prop('disabled', false); 
        $('#formGasto button').show();

        $.when(
            cargarCentros(), cargarCajas(), cargarCategorias(), cargarProveedores(), 
            cargarTipos(), cargarMedios(), cargarObras()
        ).done(function() {
            for (let k in data) {
                let el = document.getElementById(k);
                if (el && k !== 'archivo') { el.value = data[k]; }
            }

            $('#neto').trigger('input'); 

            let promesaSubcat = data.categoria_id ? cargarSubcategorias(data.categoria_id) : cargarSubcategorias('');

            promesaSubcat.done(() => {
                $('#subcategoria_id').val(data.subcategoria_id);
                aplicarBuscadores();

                if (buscadoresTom['#proveedor_id']) buscadoresTom['#proveedor_id'].setValue(data.proveedor_id, true);
                if (buscadoresTom['#centro_costo_id']) buscadoresTom['#centro_costo_id'].setValue(data.centro_costo_id, true);
                if (buscadoresTom['#obra_id']) buscadoresTom['#obra_id'].setValue(data.obra_id, true);
                if (buscadoresTom['#categoria_id']) buscadoresTom['#categoria_id'].setValue(data.categoria_id, true);
                
                if (buscadoresTom['#subcategoria_id'] && data.subcategoria_id) {
                    buscadoresTom['#subcategoria_id'].setValue(data.subcategoria_id, true);
                }
            });
        });

        if (data.archivo) {
            $('#archivo_actual').html(`
                <div class="alert alert-info py-1 px-2 mb-0 d-flex justify-content-between align-items-center">
                    <small>Archivo: <b>${data.archivo}</b></small>
                    <a href="/contable/uploads/gastos/${data.archivo}" target="_blank" class="btn btn-xs btn-dark">Ver</a>
                </div>`);
            $('#btnEliminarArchivo').show().data('id', data.id);
        } else {
            $('#archivo_actual').html('');
            $('#btnEliminarArchivo').hide();
        }

        modalGasto.show();
    }

    window.eliminar = function(id) {
        Swal.fire({
            title: '¿Eliminar este gasto?',
            text: 'Esta acción impactará directamente en tus saldos contables. Escribe "ELIMINAR" para proceder:',
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
                if (!value) {
                    return '¡Debes escribir la palabra de confirmación!';
                }
                if (value !== 'ELIMINAR') {
                    return 'La palabra no coincide. Intenta de nuevo (en mayúsculas).';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/contable/ajax/gastos.php?accion=eliminar', { id }, () => { 
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El comprobante de gasto ha sido removido.',
                        icon: 'success',
                        confirmButtonColor: '#212529'
                    });
                    tabla.ajax.reload(); 
                });
            }
        });
    }

    $(document).ready(function() {
        cargarCentros();
        cargarCajas();
        cargarObras();
        cargarCategorias();
    });
});

$(document).on('click', '#btnEliminarArchivo', function() {
    let id = $(this).data('id');
    
    Swal.fire({
        title: '¿Eliminar archivo adjunto?',
        text: 'El documento se borrará permanentemente del servidor. Escribe "ELIMINAR" para confirmar:',
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Escribe ELIMINAR aquí...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#212529',
        confirmButtonText: 'Eliminar archivo',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return '¡Debes escribir la palabra de confirmación!';
            }
            if (value !== 'ELIMINAR') {
                return 'La palabra no coincide. Intenta de nuevo.';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/contable/ajax/gastos.php?accion=eliminar_archivo', { id }, function() {
                Swal.fire({
                    title: 'Adjunto Removido',
                    text: 'El archivo fue eliminado con éxito.',
                    icon: 'success',
                    confirmButtonColor: '#212529'
                });
                $('#archivo_actual').html('');
                $('#btnEliminarArchivo').hide();
                tabla.ajax.reload();
            });
        }
    });
});
</script>
<!-- Script de Importación AFIP -->
<script src="/contable/assets/js/gastos_importar.js"></script>