<?php
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="topbar d-flex justify-content-between align-items-center">
    <h5>Gastos</h5>
    <button class="btn btn-dark" onclick="abrirModal()">+ Nuevo</button>
</div>

<div class="content">
<div class="card p-3 shadow-sm mb-3">
    <div class="row g-2">
        <div class="col-md-2">
            <label class="small">Desde</label>
            <input type="date" id="f_desde" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="small">Hasta</label>
            <input type="date" id="f_hasta" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="small">Centro</label>
            <select id="f_centro" class="form-select form-select-sm"></select>
        </div>
        <div class="col-md-2">
            <label class="small">Categoría</label>
            <select id="f_categoria" class="form-select form-select-sm"></select>
        </div>
        <div class="col-md-2">
            <label class="small">Obra</label>
            <select id="f_obra" class="form-select form-select-sm"></select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-sm btn-dark w-100" onclick="aplicarFiltros()">Filtrar</button>
            <button class="btn btn-sm btn-outline-secondary ms-1" onclick="limpiarFiltros()" title="Limpiar">X</button>
        </div>
    </div>
</div>
<div class="card p-3 shadow-sm">
<table id="tablaGastos" class="table table-bordered table-striped">
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
<th>Acciones</th>
</tr>
</thead>
</table>
</div>

</div>

<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/modal_gasto.php'; ?>
<?php include '../../includes/footer.php'; ?>

<script>
let modalGasto;

window.aplicarFiltros = function() {
    $('#tablaGastos').DataTable().ajax.reload();
}

window.limpiarFiltros = function() {
    $('#f_desde, #f_hasta').val('');
    $('#f_centro, #f_categoria, #f_obra').val('');
    $('#tablaGastos').DataTable().ajax.reload();
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
    if(!categoria_id){
        $('#subcategoria_id').html(`<option value="">-- Seleccionar --</option>`);
        return $.Deferred().resolve();
    }
    return $.get('/contable/ajax/get_subcategorias.php',{categoria_id}, r=>{
        let s=$('#subcategoria_id');
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

window.abrirModal = function(){
    $('#formGasto')[0].reset();
    $('#id').val('');
    $('#archivo_actual').html('');
    $('#btnEliminarArchivo').hide();
    $('#formGasto input, select').prop('disabled', false);
    $('#formGasto button[type="submit"], #formGasto .btn-dark').show();
    
    $.when(cargarCentros(), cargarCajas(), cargarTipos(), cargarMedios(), cargarObras(), cargarCategorias(), cargarProveedores())
     .done(() => { modalGasto.show(); });
}

document.addEventListener("DOMContentLoaded", function() {
    modalGasto = new bootstrap.Modal(document.getElementById('modalGasto'));

    let tabla = $('#tablaGastos').DataTable({
        responsive: true,
        scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'colvis', text: 'Columnas', className: 'btn btn-sm btn-secondary' },
            { extend: 'excel', text: 'Excel', className: 'btn btn-sm btn-secondary' }
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
            { data: 'neto', className: 'text-end', render: d => renderMoneda(d) },
            { data: 'iva', className: 'text-end', render: d => renderMoneda(d) },
            { data: 'ret_iibb', className: 'none', render: d => renderMoneda(d) },
            { data: 'otros_tributos', className: 'none', render: d => renderMoneda(d) },
            { data: 'total', className: 'text-end fw-bold', render: d => renderMoneda(d) },
            { data: 'centro' },
            { data: 'obra' },
            { data: 'categoria' },
            { data: 'subcategoria' },
            { 
                data: null, 
                orderable: false, 
                render: function(data) {
                    // Si el registro tiene archivo adjunto, agregamos la carpeta uniforme
                    let btnArchivo = data.archivo ? `<a href="/contable/uploads/gastos/${data.archivo}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver Adjunto Gasto">📁</a>` : '';
                    
                    return `
                        <div class="d-flex gap-1 align-items-center">
                            ${btnArchivo}
                            <button class="btn btn-sm btn-secondary" onclick='ver(${JSON.stringify(data)})'>Ver</button>
                            <button class="btn btn-sm btn-primary" onclick='editar(${JSON.stringify(data)})'>Editar</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${data.id})">Eliminar</button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#categoria_id').on('change', function() { cargarSubcategorias($(this).val()); });

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
            success: function(resp) {
                tabla.ajax.reload();
                modalGasto.hide();
            }
        });
    });

    window.ver = function(data) {
        window.editar(data);
        setTimeout(() => {
            $('#formGasto input, select').prop('disabled', true);
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

            if(data.categoria_id) {
                cargarSubcategorias(data.categoria_id).done(() => {
                    $('#subcategoria_id').val(data.subcategoria_id);
                });
            }
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
        if (!confirm("¿Eliminar gasto? Escribí OK para confirmar.")) return;
        let c = prompt("Escribí OK");
        if (c !== "OK") return;
        $.post('/contable/ajax/gastos.php?accion=eliminar', { id }, () => { tabla.ajax.reload(); });
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
    if (!confirm('¿Eliminar archivo?')) return;
    $.post('/contable/ajax/gastos.php?accion=eliminar_archivo', { id }, function() {
        $('#archivo_actual').html('');
        $('#btnEliminarArchivo').hide();
        $('#tablaGastos').DataTable().ajax.reload();
    });
});
</script>