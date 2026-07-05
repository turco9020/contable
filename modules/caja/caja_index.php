<?php
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/sidebar.php';
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>💰 Caja</h3>

        <button class="btn btn-dark" onclick="abrirModal()">
            + Nuevo Movimiento
        </button>
    </div>

    <!-- SALDOS -->
    <div class="row mb-4" id="cardsSaldos">

    </div>

    <!-- TABLA -->
    <table id="tablaMovimientos" class="table table-bordered table-striped w-100">

        <thead>
            <tr>
                <th>Fecha</th>
                <th>Caja</th>
                <th>Tipo</th>
                <th>Concepto</th>
                <th>Comprobante</th>
                <th>Importe</th>
                <th>Archivo</th>
                <th>Acciones</th>
            </tr>
        </thead>

    </table>

</div>

<!-- MODAL -->

<div class="modal fade" id="modalMovimiento">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">
                <h5>Movimiento de Caja</h5>
            </div>

            <div class="modal-body">

                <form id="formMovimiento" enctype="multipart/form-data">

                    <input type="hidden" name="id" id="id">

                    <div class="row">

                        <div class="col-md-4">
                            <label>Fecha</label>
                            <input type="date"
                                   id="fecha"
                                   name="fecha"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label>Caja</label>
                            <select id="caja_id"
                                    name="caja_id"
                                    class="form-control"
                                    required>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Tipo</label>
                            <select id="tipo"
                                    name="tipo"
                                    class="form-control">

                                <option value="INGRESO">INGRESO</option>
                                <option value="EGRESO">EGRESO</option>
                                <option value="TRANSFERENCIA">TRANSFERENCIA</option>

                            </select>
                        </div>

                    </div>

                    <div class="mt-3">
                        <label>Concepto</label>
                        <input type="text"
                               id="concepto"
                               name="concepto"
                               class="form-control"
                               required>
                    </div>

                    <div class="row mt-3">

                        <div class="col-md-6">
                            <label>Comprobante</label>
                            <input type="text"
                                   id="comprobante"
                                   name="comprobante"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Importe</label>
                            <input type="number"
                                   step="0.01"
                                   id="importe"
                                   name="importe"
                                   class="form-control"
                                   required>
                        </div>

                    </div>

                    <div class="mt-3">
                        <label>Observaciones</label>

                        <textarea
                            id="observaciones"
                            name="observaciones"
                            class="form-control"
                            rows="3"></textarea>
                    </div>

                    <div class="mt-3">
                        <label>Archivo</label>

                        <input type="file"
                               id="archivo"
                               name="archivo"
                               class="form-control">

                        <div id="archivo_actual" class="mt-2"></div>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-dark w-100">
                            Guardar
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/includes/footer.php'; ?>

<script>

let tabla;
let modalMovimiento;

// =======================
// CARGAR CAJAS
// =======================

function cargarCajas(){

    $.get('/contable/ajax/cajas.php?accion=listar', function(r){

        let select = $('#caja_id');

        select.empty();

        r.data.forEach(c => {

            select.append(`
                <option value="${c.id}">
                    ${c.nombre}
                </option>
            `);

        });

    }, 'json');
}

// =======================
// SALDOS
// =======================

function cargarSaldos(){

    $.get('/contable/ajax/movimientos_caja.php?accion=saldos', function(data){

        let html = '';

        data.forEach(caja => {

            let saldo = Number(caja.saldo).toLocaleString(
                'es-AR',
                {
                    minimumFractionDigits:2,
                    maximumFractionDigits:2
                }
            );

            html += `
                <div class="col-md-3 mb-3">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h6>${caja.nombre}</h6>

                            <h4>$ ${saldo}</h4>

                        </div>

                    </div>

                </div>
            `;
        });

        $('#cardsSaldos').html(html);

    }, 'json');
}


// =======================
// DATATABLE
// =======================

document.addEventListener("DOMContentLoaded", function(){

    modalMovimiento = new bootstrap.Modal(
        document.getElementById('modalMovimiento')
    );

    cargarCajas();
    cargarSaldos();

    tabla = $('#tablaMovimientos').DataTable({

        ajax:'/contable/ajax/movimientos_caja.php?accion=listar',

        order:[[0,'desc']],

        columns:[

            {
                data:'fecha'
            },

            {
                data:'caja'
            },

            {
                data:'tipo',
                render:function(d){

                    if(d == 'INGRESO'){
                        return `<span class="badge bg-success">INGRESO</span>`;
                    }

                    if(d == 'EGRESO'){
                        return `<span class="badge bg-danger">EGRESO</span>`;
                    }

                    return `<span class="badge bg-primary">TRANSFERENCIA</span>`;
                }
            },

            {
                 data:'concepto',
                 render:function(data,type,row){

                   if(row.origen == 'GASTO'){

                              return `
                   <a href="#"
                   onclick="verGasto(${row.referencia_id});return false;">

                    ${data}

                </a>
            `;
                    }

                  return data;
                 }
            },

            {
                data:'comprobante'
            },

            {
                data:'importe',
                render:function(d){

                    return '$ ' +
                        Number(d).toLocaleString(
                            'es-AR',
                            {
                                minimumFractionDigits:2
                            }
                        );
                }
            },

            {
                data:'archivo',
                render:function(d){

                    if(!d) return '-';

                    return `
                        <a
                            href="/contable/uploads/caja/${d}"
                            target="_blank"
                            class="btn btn-sm btn-secondary">

                            Ver

                        </a>
                    `;
                }
            },

            {
                data:null,
                orderable:false,

                render:function(d){

                    return `
                        <button
                            class="btn btn-sm btn-primary"
                            onclick='editar(${JSON.stringify(d)})'>

                            Editar

                        </button>

                        <button
                            class="btn btn-sm btn-outline-danger"
                            onclick="eliminar(${d.id})">

                            Eliminar

                        </button>
                    `;
                }
            }

        ]

    });

});

// =======================
// NUEVO
// =======================

window.abrirModal = function(){

    $('#formMovimiento')[0].reset();

    $('#id').val('');

    $('#archivo').val('');

    $('#archivo_actual').html('');

    modalMovimiento.show();
}

// =======================
// GUARDAR
// =======================

$('#formMovimiento').submit(function(e){

    e.preventDefault();

    let formData = new FormData(this);

    $.ajax({

        url:'/contable/ajax/movimientos_caja.php?accion=guardar',

        method:'POST',

        data:formData,

        contentType:false,

        processData:false,

        success:function(resp){

            console.log(resp);

            tabla.ajax.reload();

            cargarSaldos();

            modalMovimiento.hide();
        }

    });

});

// =======================
// ELIMINAR
// =======================

window.eliminar = function(id){

    if(!confirm('¿Eliminar movimiento?')) return;

    if(prompt('Escribí OK para confirmar') !== 'OK') return;

    $.post(
        '/contable/ajax/movimientos_caja.php?accion=eliminar',
        {id},
        function(resp){

            console.log(resp);

            tabla.ajax.reload();

            cargarSaldos();

        }
    );

}

// =======================
// VER GASTO
// =======================


function verGasto(id){

    $.get(
        '/contable/ajax/gastos.php?accion=obtener&id=' + id,
        function(data){

            // reutilizamos exactamente el mismo modal
            window.ver(data);

        },
        'json'
    );

}

// =======================
// EDITAR
// =======================

window.editar = function(data){

    $('#formMovimiento')[0].reset();

    $('#id').val(data.id);

    $('#fecha').val(data.fecha);

    $('#caja_id').val(data.caja_id);

    $('#tipo').val(data.tipo);

    $('#concepto').val(data.concepto);

    $('#comprobante').val(data.comprobante);

    $('#importe').val(data.importe);

    $('#observaciones').val(data.observaciones);

    // ARCHIVO

    if(data.archivo){

        $('#archivo_actual').html(`
            <a
                href="/contable/uploads/caja/${data.archivo}"
                target="_blank"
                class="btn btn-sm btn-secondary">

                Ver archivo actual

            </a>
        `);

    }else{

        $('#archivo_actual').html('');
    }

    $('#archivo').val('');

    modalMovimiento.show();
}


</script>