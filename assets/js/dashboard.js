// ======================================================
// DASHBOARD
// ======================================================

// Evita la doble ejecución si el script se incluye más de una vez en el DOM
if (typeof window.dashboardInicializado !== 'undefined') {
    // Ya fue cargado previamente, no vuelve a declarar variables
} else {
    window.dashboardInicializado = true;

    var saldosOcultos = true; // Estado global para ocultar/mostrar saldos
    var datosCajasGlobal = [];

    $(document).ready(function () {
        cargarDashboard();
        cargarSaldosDashboard();
    });

    // ======================================================
    // VER DETALLE DE TAREA DESDE EL DASHBOARD
    // ======================================================
    function verTareaDashboard(idTarea) {
        $.get('/contable/modules/tareas/acciones.php?accion=obtener', { id: idTarea }, function(res) {
            if (!res.success) {
                Swal.fire('Error', res.message || 'No se pudo obtener la tarea.', 'error');
                return;
            }

            let t = res.tarea;
            
            // Remueve la marca de NUEVA cuando la abre
            $(`#tarea-card-${t.id}`).find('.badge-nueva').remove();

            Swal.fire({
                title: `<strong>${t.titulo}</strong>`,
                html: `
                    <div class="text-start fs-6">
                        <p class="mb-2"><strong>Descripción:</strong><br>${t.descripcion || '<i>Sin descripción</i>'}</p>
                        <hr class="my-2">
                        <p class="mb-1"><strong>Estado:</strong> <span class="badge bg-secondary">${t.estado}</span></p>
                        <p class="mb-1"><strong>Prioridad:</strong> <span class="badge bg-info text-dark">${t.prioridad}</span></p>
                        <p class="mb-1"><strong>Vencimiento:</strong> ${t.fecha_limite || 'Sin fecha'}</p>
                        <p class="mb-0"><strong>Asignado a:</strong> ${t.asignado_nombre || 'Sin asignar'}</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-box-arrow-up-right me-1"></i> Ir al Tablero Kanban',
                cancelButtonText: 'Cerrar',
                confirmButtonColor: '#070b13'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/contable/modules/tareas/';
                }
            });
        }, 'json').fail(function(xhr) {
            console.error("Error AJAX Tareas:", xhr.responseText);
            Swal.fire('Error', 'No se pudo conectar con el servidor para obtener el detalle de la tarea.', 'error');
        });
    }

    // ======================================================
    // CARGAR DASHBOARD (INCLUYE RENDER DE TAREAS)
    // ======================================================

    function cargarDashboard() {
        $.ajax({
            url: '/contable/ajax/dashboard.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                // Cards superiores
                $('#saldoDisponible').html(formatoMoneda(data.saldo));
                $('#gastosHoy').html(formatoMoneda(data.gastos_hoy));
                $('#gastosMes').html(formatoMoneda(data.gastos_mes));
                $('#categoriaTopNombre').html(data.categoria_top);
                $('#categoriaTopTotal').html(formatoMoneda(data.categoria_total));
                $('#categoriaTopPorcentaje').html(data.categoria_porcentaje + '% del gasto mensual');

                // Centros de costo
                renderCentros(data.centros);

                // Renderizar tareas pendientes si vienen en el payload AJAX
                if (data.tareas) {
                    renderTareasDashboard(data.tareas);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    // ======================================================
    // RENDERIZAR TAREAS (CON RESALTE Y MODAL)
    // ======================================================

    function renderTareasDashboard(tareas) {
        let $contenedor = $('#contenedorTareasDashboard');
        if ($contenedor.length === 0) return;

        if (!tareas || tareas.length === 0) {
            $contenedor.html('<div class="alert alert-light mb-0 small">No tenés tareas pendientes.</div>');
            return;
        }

        let html = '';
        tareas.forEach(t => {
            // Detección de tareas creadas en las últimas 48 horas
            let fechaCreacion = t.created_at ? new Date(t.created_at) : new Date();
            let ahora = new Date();
            let horasDiferencia = (ahora - fechaCreacion) / (1000 * 60 * 60);
            
            let esNueva = horasDiferencia <= 48;
            let borderClase = esNueva ? 'border-start border-4 border-info shadow' : 'border-0 shadow-sm';
            let badgeNueva = esNueva ? '<span class="badge bg-info text-white badge-nueva mb-1"><i class="bi bi-bell-fill me-1"></i>NUEVA</span>' : '';

            html += `
                <div class="col-12 col-md-6 col-xl-4 mb-2" id="tarea-card-${t.id}">
                    <div class="card h-100 ${borderClase} p-2" 
                         onclick="verTareaDashboard(${t.id})" 
                         style="border-radius: 6px; background: #ffffff; cursor: pointer; transition: transform 0.15s;"
                         onmouseover="this.style.transform='scale(1.02)'" 
                         onmouseout="this.style.transform='scale(1)'">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            ${badgeNueva}
                            <span class="badge bg-light text-dark border small ms-auto">${t.prioridad || 'Media'}</span>
                        </div>

                        <div class="fw-bold text-dark text-truncate small mt-1">
                            ${t.titulo}
                        </div>

                        <div class="text-muted small text-truncate mt-1" style="font-size: 11px;">
                            <i class="bi bi-calendar-event me-1"></i>Vence: ${t.fecha_limite || 'Sin fecha'}
                        </div>
                    </div>
                </div>
            `;
        });

        $contenedor.html(html);
    }

    // ======================================================
    // CARGAR CAJAS Y SALDOS
    // ======================================================

    function cargarSaldosDashboard() {
        $.get('/contable/ajax/movimientos_caja.php?accion=saldos', function (data) {
            console.log("Datos recibidos de cajas:", data);

            if (!data || !Array.isArray(data) || data.length === 0) {
                $('#cardsCajas').html('<div class="col-12 text-muted small py-2">No hay cajas activas disponibles.</div>');
                return;
            }

            datosCajasGlobal = data;
            renderTarjetasCajas();

        }, 'json').fail(function (xhr, status, error) {
            console.error("Error en AJAX:", xhr.responseText);
            $('#cardsCajas').html('<div class="col-12 text-danger small py-2"><i class="bi bi-exclamation-triangle me-1"></i>Error al obtener los saldos.</div>');
        });
    }

    // ======================================================
    // RENDERIZAR TARJETAS DE CAJA (CON OJITO OCULTAR/MOSTRAR)
    // ======================================================

    function renderTarjetasCajas() {
        let html = '';
        let iconoOjo = saldosOcultos ? 'bi-eye-slash-fill' : 'bi-eye-fill';
        let tituloOjo = saldosOcultos ? 'Mostrar saldos' : 'Ocultar saldos';

        datosCajasGlobal.forEach(caja => {
            let valorSaldo = Number(caja.saldo);
            
            let saldoTexto = saldosOcultos 
                ? '$ ••••••••' 
                : formatoMoneda(valorSaldo);

            let colorClase = (!saldosOcultos && valorSaldo < 0) ? 'text-danger' : 'text-dark';

            html += `
                <div class="col-12 col-md-4 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 p-3" style="background-color: #ffffff; border-radius: 8px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase fw-semibold text-muted small text-truncate" title="${caja.nombre}">
                                ${caja.nombre}
                            </span>
                            <div class="p-1 rounded bg-light text-secondary d-flex align-items-center justify-content-center">
                                <i class="bi bi-wallet2" style="font-size: 14px;"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <h5 class="fw-bold mb-0 ${colorClase}">
                                ${saldoTexto}
                            </h5>
                            <button type="button" class="btn btn-link text-secondary p-0 ms-2 text-decoration-none" onclick="toggleOcultarSaldos()" title="${tituloOjo}">
                                <i class="bi ${iconoOjo}" style="font-size: 16px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#cardsCajas').html(html);
    }

    function toggleOcultarSaldos() {
        saldosOcultos = !saldosOcultos;
        renderTarjetasCajas();
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
                        <div class="progress-bar bg-success" style="width:${porcentaje}%"></div>
                    </div>
                </div>
            `;
        });

        $('#centrosCostosDashboard').html(html);
    }
}