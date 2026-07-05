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

function cargarCajas(selector='#caja_id'){

    return $.get(
        '/contable/ajax/cajas.php?accion=listar',
        function(r){

            let s=$(selector);

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