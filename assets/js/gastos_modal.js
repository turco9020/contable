// =======================
// MODAL GASTOS
// Archivo compartido
// =======================

let modalGasto;

// =======================
// INICIALIZAR
// =======================

function iniciarModalGasto(){

    if(!modalGasto){

        modalGasto = new bootstrap.Modal(
            document.getElementById('modalGasto')
        );

    }

}

// =======================
// TIPOS
// =======================

function cargarTipos(){

    return $.get(
        '/contable/ajax/tipos_comprobante.php?accion=listar',
        function(r){

            let s = $('#tipo_comprobante_id');

            s.empty();

            s.append('<option value="">Seleccione</option>');

            r.data.forEach(x=>{

                s.append(
                    `<option value="${x.id}">
                        ${x.nombre}
                    </option>`
                );

            });

        },
        'json'
    );

}

// =======================
// MEDIOS
// =======================

function cargarMedios(){

    return $.get(
        '/contable/ajax/medios_pago.php?accion=listar',
        function(r){

            let s = $('#medio_pago_id');

            s.empty();

            s.append('<option value="">Seleccione</option>');

            r.data.forEach(x=>{

                s.append(
                    `<option value="${x.id}">
                        ${x.nombre}
                    </option>`
                );

            });

        },
        'json'
    );

}

// =======================
// CAJAS
// =======================

function cargarCajas(selector = '#formGasto #caja_id'){

    const s = $(selector);
console.log("Selector:", selector);
console.log($(selector));
    return $.get(
        '/contable/ajax/cajas.php?accion=listar',
        function(r){

            s.empty();

            s.append('<option value="">Seleccione</option>');

            r.data.forEach(function(x){

                s.append(`
                    <option value="${x.id}">
                        ${x.nombre}
                    </option>
                `);

            });

        },
        'json'
    );

}

// =======================
// CENTROS
// =======================

function cargarCentros(){

    return $.get(
        '/contable/ajax/centros.php?accion=listar',
        function(r){

            let s = $('#centro_costo_id');

            s.empty();

            s.append('<option value="">Seleccione</option>');

            r.data.forEach(function(x){

                s.append(`
                    <option value="${x.id}">
                        ${x.nombre}
                    </option>
                `);

            });

        },
        'json'
    );

}

// =======================
// OBRAS
// =======================

function cargarObras(){

    return $.get(
        '/contable/ajax/obras.php?accion=listar',
        function(r){

            let s = $('#obra_id');

            s.empty();

            s.append('<option value="">Seleccione</option>');

            r.data.forEach(function(x){

                s.append(`
                    <option value="${x.id}">
                        ${x.nombre}
                    </option>
                `);

            });

        },
        'json'
    );

}

// =======================
// CATEGORIAS
// =======================

function cargarCategorias(){

    return $.get(
        '/contable/ajax/categorias.php?accion=listar',
        function(r){

            let s = $('#categoria_id');

            s.empty();

            s.append('<option value="">Seleccione</option>');

            r.data.forEach(function(x){

                s.append(`
                    <option value="${x.id}">
                        ${x.nombre}
                    </option>
                `);

            });

        },
        'json'
    );

}

// =======================
// SUBCATEGORIAS
// =======================

function cargarSubcategorias(categoria_id){

    if(!categoria_id){

        $('#subcategoria_id').html(
            '<option value="">Seleccione</option>'
        );

        return $.Deferred().resolve();

    }

    return $.get(

        '/contable/ajax/get_subcategorias.php',

        {categoria_id:categoria_id},

        function(r){

            let s = $('#subcategoria_id');

            s.empty();

            s.append('<option value="">Seleccione</option>');

            r.forEach(function(x){

                s.append(`
                    <option value="${x.id}">
                        ${x.nombre}
                    </option>
                `);

            });

        },

        'json'

    );

} 

// =======================
// PROVEEDORES
// =======================

function cargarProveedores(){

    return $.get(

        '/contable/ajax/get_proveedores.php',

        function(r){

            let s = $('#proveedor_id');

            s.empty();

            s.append('<option value="">Seleccione</option>');

            r.forEach(function(x){

                s.append(`
                    <option value="${x.id}">
                        ${x.nombre} (${x.cuit})
                    </option>
                `);

            });

        },

        'json'

    );

}

// =======================
// EDITAR
// =======================

window.editarGasto = function(data){

    iniciarModalGasto();

    const form = $('#formGasto');

    $('#tituloModalGasto').text('Editar Gasto');

    form[0].reset();

    form.find('input,select,textarea')
        .prop('disabled',false);

    $('#btnGuardarGasto').show();
    $('#archivo').show();

    $.when(

        cargarCentros(),
        cargarCajas('#formGasto #caja_id'),
        cargarCategorias(),
        cargarProveedores(),
        cargarTipos(),
        cargarMedios(),
        cargarObras()

    ).done(function(){

        // Completa TODOS los controles del formulario
        for(let k in data){

            let campo = form.find('[name="'+k+'"]');

            if(!campo.length) continue;

            if(k==='fecha' && data[k]){

                campo.val(data[k].substring(0,10));

            }else{

                campo.val(data[k]);

            }

        }

        // Forzar la selección de Caja
            if(data.caja_id){

                form.find('[name="caja_id"]')
                 .val(String(data.caja_id))
                 .trigger('change');

        }

        console.log(
    form.find('[name="caja_id"]').html()
);

        if(data.categoria_id){

            cargarSubcategorias(data.categoria_id).done(function(){

                form.find('[name="subcategoria_id"]')
                    .val(data.subcategoria_id);

            });

        }

        form.find('[name="neto"]').trigger('input');

    });

    if(data.archivo){

        $('#archivo_actual').html(`
            <div class="alert alert-info py-2 d-flex justify-content-between align-items-center">

                <strong>${data.archivo}</strong>

                <a
                    href="/contable/uploads/gastos/${data.archivo}"
                    target="_blank"
                    class="btn btn-dark btn-sm">

                    Ver archivo

                </a>

            </div>
        `);

        $('#btnEliminarArchivo')
            .show()
            .data('id',data.id);

    }else{

        $('#archivo_actual').html('');

        $('#btnEliminarArchivo').hide();

    }

    modalGasto.show();

};


// =======================
// VER
// =======================

window.mostrarModalGasto = function(data){

    window.editarGasto(data);

    setTimeout(function(){

        $('#tituloModalGasto').text('Detalle del Gasto');

        $('#formGasto')
            .find('input, select, textarea')
            .prop('disabled', true);

        $('#btnGuardarGasto').hide();

        $('#btnEliminarArchivo').hide();

        $('#archivo').hide();

    },300);

};