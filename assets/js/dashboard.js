// ======================================================
// DASHBOARD
// ======================================================

$(document).ready(function () {

    cargarDashboard();

});

// ======================================================
// CARGAR DASHBOARD
// ======================================================

function cargarDashboard() {

    $.ajax({

        url: '/contable/ajax/dashboard.php',

        type: 'GET',

        dataType: 'json',

        success: function (data) {

            // =====================
            // CARDS SUPERIORES
            // =====================

            $('#saldoDisponible').html(formatoMoneda(data.saldo));

            $('#gastosHoy').html(formatoMoneda(data.gastos_hoy));

            $('#gastosMes').html(formatoMoneda(data.gastos_mes));

            $('#categoriaTopNombre').html(data.categoria_top);

            $('#categoriaTopTotal').html(formatoMoneda(data.categoria_total));

            $('#categoriaTopPorcentaje').html(data.categoria_porcentaje + '% del gasto mensual');

            // =====================
            // CENTROS DE COSTO
            // =====================

            renderCentros(data.centros);

            // =====================
            // PRÓXIMAMENTE
            // =====================

            // renderCajas(data.cajas);
            // renderUltimosGastos(data.ultimos_gastos);
            // renderUltimosMovimientos(data.movimientos);

        },

        error: function (xhr) {

            console.error(xhr.responseText);

        }

    });

}

// ======================================================
// FORMATO MONEDA
// ======================================================

function formatoMoneda(valor) {

    return '$ ' + Number(valor).toLocaleString(
        'es-AR',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    );

}

// ======================================================
// CENTROS DE COSTO
// ======================================================

function renderCentros(centros) {

    let html = '';

    if (!centros || centros.length === 0) {

        html = `
            <div class="alert alert-light mb-0">
                No existen gastos este mes.
            </div>
        `;

        $('#centrosCostosDashboard').html(html);

        return;

    }

    let mayor = 0;

    centros.forEach(c => {

        if (Number(c.total) > mayor) {

            mayor = Number(c.total);

        }

    });

    centros.forEach(c => {

        let porcentaje = mayor > 0
            ? Math.round((Number(c.total) / mayor) * 100)
            : 0;

        html += `

            <div class="mb-3">

                <div class="d-flex justify-content-between">

                    <strong>${c.nombre}</strong>

                    <strong>${formatoMoneda(c.total)}</strong>

                </div>

                <div class="progress mt-1" style="height:12px;">

                    <div
                        class="progress-bar bg-success"
                        style="width:${porcentaje}%">

                    </div>

                </div>

            </div>

        `;

    });

    $('#centrosCostosDashboard').html(html);

}