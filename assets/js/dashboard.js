// ======================================================
// DASHBOARD - PERFIL CONTADOR Y ADMIN
// ======================================================

if (typeof window.dashboardInicializado !== 'undefined') {
    // Ya fue cargado previamente
} else {
    window.dashboardInicializado = true;

    // Variables globales de estado
    var periodoCentrosActual = 'actual';
    var cajasOcultas = new Set(); // IDs de cajas ocultas
    var datosCajasGlobal = [];
    var datosRubrosGlobal = { mes: [], mes_anterior: [], anual: [] }; // Almacenamiento de rubros (3 estados)

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
    // CARGAR DASHBOARD (INDICADORES, CENTROS Y TABLAS)
    // ======================================================

    function cargarDashboard() {
        $.ajax({
            url: '/contable/ajax/dashboard.php',
            type: 'GET',
            data: { periodo_centros: periodoCentrosActual },
            dataType: 'json',
            success: function (data) {
                // Métricas principales
                $('#saldoDisponible').html(formatoMoneda(data.saldo));
                $('#gastosHoy').html(formatoMoneda(data.gastos_hoy || 0));
                $('#gastosMes').html(formatoMoneda(data.gastos_mes));
                
                // Nuevas métricas agregadas
                if ($('#gastosMesAnterior').length) $('#gastosMesAnterior').html(formatoMoneda(data.gastos_mes_anterior));
                if ($('#ventasMes').length) $('#ventasMes').html(formatoMoneda(data.ventas_mes));
                if ($('#ventasMesAnterior').length) $('#ventasMesAnterior').html(formatoMoneda(data.ventas_mes_anterior));
                if ($('#rentabilidadMes').length) $('#rentabilidadMes').html(formatoMoneda(data.rentabilidad_mes));
                if ($('#rentabilidadMesAnterior').length) $('#rentabilidadMesAnterior').html(formatoMoneda(data.rentabilidad_mes_anterior));
                if ($('#diferenciaIva').length) $('#diferenciaIva').html(formatoMoneda(data.diferencia_iva));
                if ($('#retencionesMes').length) $('#retencionesMes').html(formatoMoneda(data.retenciones_mes));
                if ($('#chequesEmitidosMes').length) $('#chequesEmitidosMes').html(formatoMoneda(data.cheques_emitidos_mes));
                if ($('#chequesEmitidosTotal').length) $('#chequesEmitidosTotal').html(formatoMoneda(data.cheques_emitidos_total));

                // Rubros (Mes Actual / Mes Pasado / Anual)
                if (data.rubros_mes) {
                    datosRubrosGlobal.mes = data.rubros_mes || [];
                    datosRubrosGlobal.mes_anterior = data.rubros_mes_anterior || [];
                    datosRubrosGlobal.anual = data.rubros_anual || [];
                    renderRubros('mes');

                    // Sincronizar Mayor Categoría del Mes para Vista Contador
                    if (datosRubrosGlobal.mes.length > 0) {
                        let top = datosRubrosGlobal.mes[0];
                        $('#categoriaTopNombre').html(top.nombre);
                        $('#categoriaTopTotal').html(formatoMoneda(top.monto) + ' (' + top.porcentaje + '%)');
                    } else {
                        $('#categoriaTopNombre').html('Sin datos');
                        $('#categoriaTopTotal').html('$ 0,00');
                    }
                }

                renderCentros(data.centros, data.ventas_centros);
                
                if (data.tareas) {
                    renderTareasDashboard(data.tareas);
                }

                renderCheques(data.cheques);
                renderVencimientos(data.vencimientos);
                renderFacturasCobrar(data.facturas_cobrar);
            },
            error: function (xhr) {
                console.error("Error al cargar dashboard:", xhr.responseText);
            }
        });
    }

    // ======================================================
    // CAMBIAR PERÍODO EN CENTROS DE COSTO
    // ======================================================

    window.cambiarPeriodoCentros = function(periodo) {
        periodoCentrosActual = periodo;

        // Actualizar estado activo de los botones
        $('#btnGroupPeriodoCentros button').removeClass('active');
        if (periodo === 'actual') {
            $('#btnGroupPeriodoCentros button:nth-child(1)').addClass('active');
        } else if (periodo === 'pasado') {
            $('#btnGroupPeriodoCentros button:nth-child(2)').addClass('active');
        } else if (periodo === 'anual') {
            $('#btnGroupPeriodoCentros button:nth-child(3)').addClass('active');
        }

        // Recargar únicamente el bloque de centros de costo
        $.ajax({
            url: '/contable/ajax/dashboard.php',
            type: 'GET',
            data: { periodo_centros: periodoCentrosActual },
            dataType: 'json',
            success: function(data) {
                renderCentros(data.centros, data.ventas_centros);
            },
            error: function(xhr) {
                console.error("Error al recalcular centros:", xhr.responseText);
            }
        });
    };

    // ======================================================
    // RENDERIZAR RUBROS EN DOS COLUMNAS
    // ======================================================

    function renderRubros(tipo) {
        let listado = [];
        if (tipo === 'anual') {
            listado = datosRubrosGlobal.anual;
        } else if (tipo === 'mes_anterior') {
            listado = datosRubrosGlobal.mes_anterior;
        } else {
            listado = datosRubrosGlobal.mes;
        }

        let $contenedor = $('#contenedorBarrasRubros');
        if ($contenedor.length === 0) return;

        if (!listado || listado.length === 0) {
            $contenedor.html('<div class="col-12 text-muted small py-2 ms-2">No se registraron gastos en este período.</div>');
            return;
        }

        let col1 = listado.slice(0, 4);
        let col2 = listado.slice(4, 8);
        let colores = ['bg-danger', 'bg-warning', 'bg-info', 'bg-secondary', 'bg-dark', 'bg-primary', 'bg-success'];

        function generarColumna(items, offsetIndex) {
            let html = '';
            items.forEach((item, index) => {
                let colorClase = colores[(index + offsetIndex) % colores.length];
                html += `
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                            <span class="fw-bold text-dark text-truncate" style="max-width: 200px;" title="${item.nombre}">${item.nombre}</span>
                            <span class="fw-bold text-dark">${formatoMoneda(item.monto)} <small class="text-muted fw-normal">(${item.porcentaje}%)</small></span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar ${colorClase}" role="progressbar" style="width: ${item.porcentaje}%"></div>
                        </div>
                    </div>
                `;
            });
            return html;
        }

        let htmlFinal = `
            <div class="col-12 col-md-6 border-end pe-md-3">
                ${generarColumna(col1, 0)}
            </div>
            <div class="col-12 col-md-6 ps-md-3">
                ${col2.length > 0 ? generarColumna(col2, 4) : '<div class="text-muted small py-1">No hay más rubros en este rango.</div>'}
            </div>
        `;

        $contenedor.html(htmlFinal);
    }

    window.cambiarVistaRubros = function(tipo) {
        $('#btnGroupRubros button').removeClass('active');
        
        if (tipo === 'mes') {
            $('#btnGroupRubros button:nth-child(1)').addClass('active');
        } else if (tipo === 'mes_anterior') {
            $('#btnGroupRubros button:nth-child(2)').addClass('active');
        } else if (tipo === 'anual') {
            $('#btnGroupRubros button:nth-child(3)').addClass('active');
        }
        
        renderRubros(tipo);
    };

    // ======================================================
    // RENDERIZAR TAREAS
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
            let fechaCreacion = t.created_at ? new Date(t.created_at) : new Date();
            let ahora = new Date();
            let horasDiferencia = (ahora - fechaCreacion) / (1000 * 60 * 60);
            
            let esNueva = horasDiferencia <= 48;
            let borderClase = esNueva ? 'border-start border-4 border-info shadow' : 'border-0 shadow-sm';
            let badgeNueva = esNueva ? '<span class="badge bg-primary text-white badge-nueva mb-1"><i class="bi bi-bell-fill me-1"></i>NUEVA</span>' : '';

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
            if (!data || !Array.isArray(data) || data.length === 0) {
                if ($('#tablaCajasAdmin tbody').length) {
                    $('#tablaCajasAdmin tbody').html('<tr><td colspan="3" class="text-center text-muted small py-2">No hay cajas activas.</td></tr>');
                }
                if ($('#cardsCajas').length) {
                    $('#cardsCajas').html('<div class="col-12 text-muted small py-2">No hay cajas activas disponibles.</div>');
                }
                return;
            }

            datosCajasGlobal = data;

            if (typeof window.cajasInicializadas === 'undefined') {
                datosCajasGlobal.forEach(c => cajasOcultas.add(Number(c.id)));
                window.cajasInicializadas = true;
            }

            renderTarjetasCajas();

        }, 'json').fail(function (xhr) {
            console.error("Error AJAX Cajas:", xhr.responseText);
            if ($('#tablaCajasAdmin tbody').length) {
                $('#tablaCajasAdmin tbody').html('<tr><td colspan="3" class="text-center text-danger small py-2">Error al obtener saldos.</td></tr>');
            }
        });
    }

    // ======================================================
    // RENDERIZAR TABLA Y TARJETAS DE CAJA (SOPORTE DUAL)
    // ======================================================

    function renderTarjetasCajas() {
        if (!datosCajasGlobal || datosCajasGlobal.length === 0) return;

        // 1. RENDER PARA TABLA DE CAJAS (#tablaCajasAdmin)
        let tbody = $('#tablaCajasAdmin tbody');
        if (tbody.length > 0) {
            let htmlTabla = '';
            datosCajasGlobal.forEach(caja => {
                let idNum = Number(caja.id);
                let valorSaldo = Number(caja.saldo);
                let estaOculta = cajasOcultas.has(idNum);

                let iconoOjo = estaOculta ? 'bi-eye-slash-fill text-muted' : 'bi-eye-fill text-primary';
                let saldoTexto = estaOculta ? '$ ••••••••' : formatoMoneda(valorSaldo);
                let colorClase = (!estaOculta && valorSaldo < 0) ? 'text-danger' : 'text-dark';

                htmlTabla += `
                    <tr>
                        <td class="ps-3 fw-bold text-dark align-middle">${caja.nombre}</td>
                        <td class="text-end fw-bold ${colorClase} align-middle">${saldoTexto}</td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-link p-0 text-decoration-none" onclick="toggleOcultarCaja(event, ${idNum})" title="Ocultar/Mostrar saldo">
                                <i class="bi ${iconoOjo}" style="font-size: 15px;"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.html(htmlTabla);
        }

        // 2. RENDER PARA TARJETAS CARDS (#cardsCajas)
        let divCards = $('#cardsCajas');
        if (divCards.length > 0) {
            let htmlCards = '';
            datosCajasGlobal.forEach(caja => {
                let idNum = Number(caja.id);
                let valorSaldo = Number(caja.saldo);
                let estaOculta = cajasOcultas.has(idNum);

                let iconoOjo = estaOculta ? 'bi-eye-slash-fill text-muted' : 'bi-eye-fill text-primary';
                let tituloOjo = estaOculta ? 'Mostrar saldo de esta caja' : 'Ocultar saldo de esta caja';
                
                let saldoTexto = estaOculta ? '$ ••••••••' : formatoMoneda(valorSaldo);
                let colorClase = (!estaOculta && valorSaldo < 0) ? 'text-danger' : 'text-dark';

                htmlCards += `
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
                                <button type="button" class="btn btn-link p-0 ms-2 text-decoration-none" onclick="toggleOcultarCaja(event, ${idNum})" title="${tituloOjo}">
                                    <i class="bi ${iconoOjo}" style="font-size: 16px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            divCards.html(htmlCards);
        }
    }

    // ======================================================
    // TOGGLE OCULTAR / MOSTRAR CAJA
    // ======================================================

    window.toggleOcultarCaja = function(event, idCaja) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        let id = Number(idCaja);
        
        if (cajasOcultas.has(id)) {
            cajasOcultas.delete(id);
        } else {
            cajasOcultas.add(id);
        }
        
        renderTarjetasCajas();
    };

    // ======================================================
    // CENTROS DE COSTO (COMPATIBLE CONTADOR Y ADMIN)
    // ======================================================

    let chartGastosInstance = null;
    let chartVentasInstance = null;

    function renderCentros(centrosGastos, centrosVentas) {
        centrosGastos = Array.isArray(centrosGastos) ? centrosGastos : [];
        centrosVentas = Array.isArray(centrosVentas) ? centrosVentas : [];

        // 1. SI EXISTE CONTENEDOR CONTADOR (#centrosCostosDashboard)
        let $contenedorSimple = $('#centrosCostosDashboard');
        if ($contenedorSimple.length > 0) {
            if (centrosGastos.length === 0) {
                $contenedorSimple.html('<div class="text-muted small">Sin datos de gastos por centro este mes.</div>');
            } else {
                let html = '<div class="row g-2">';
                centrosGastos.forEach(c => {
                    html += `
                        <div class="col-12 col-md-4 col-xl-3">
                            <div class="p-2 border rounded bg-light">
                                <div class="small text-muted text-truncate" title="${c.nombre}">${c.nombre}</div>
                                <div class="fw-bold text-dark">${formatoMoneda(c.total)}</div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                $contenedorSimple.html(html);
            }
        }

        // 2. SI EXISTEN ELEMENTOS DE ADMIN (Gráficos y Tabla de Rentabilidad)
        if (typeof Chart !== 'undefined') {
            let $tbody = $('#tablaRentabilidadCentro tbody');
            if ($tbody.length > 0) {
                $tbody.empty();
                if (centrosGastos.length === 0 && centrosVentas.length === 0) {
                    $tbody.append('<tr><td colspan="2" class="text-center text-muted py-2">Sin datos disponibles</td></tr>');
                } else {
                    let mapaRentabilidad = {};
                    centrosVentas.forEach(v => { mapaRentabilidad[v.nombre] = (mapaRentabilidad[v.nombre] || 0) + Number(v.total); });
                    centrosGastos.forEach(g => { mapaRentabilidad[g.nombre] = (mapaRentabilidad[g.nombre] || 0) - Number(g.total); });

                    Object.keys(mapaRentabilidad).forEach(nombre => {
                        let totalRentab = mapaRentabilidad[nombre];
                        let colorTexto = totalRentab >= 0 ? 'text-success' : 'text-danger';
                        $tbody.append(`
                            <tr>
                                <td class="text-truncate" style="max-width: 100px;" title="${nombre}">${nombre}</td>
                                <td class="text-end fw-bold ${colorTexto}">${formatoMoneda(totalRentab)}</td>
                            </tr>
                        `);
                    });
                }
            }

            const paletaGastos = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#f8f9fc'];
            const paletaVentas = ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#60616f', '#4e73df', '#eaecf4'];

            const opcionesBase = {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '40%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { usePointStyle: true, pointStyle: 'circle', padding: 12, font: { size: 11 }, color: '#495057' }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label || ''}: ${formatoMoneda(context.raw || 0)}`;
                            }
                        }
                    }
                }
            };

            let ctxGastos = document.getElementById('chartGastosCentro');
            if (ctxGastos) {
                if (chartGastosInstance) chartGastosInstance.destroy();
                chartGastosInstance = new Chart(ctxGastos, {
                    type: 'doughnut',
                    data: {
                        labels: centrosGastos.map(c => c.nombre),
                        datasets: [{ data: centrosGastos.map(c => c.total), backgroundColor: paletaGastos.slice(0, centrosGastos.length), borderWidth: 1, borderColor: '#ffffff' }]
                    },
                    options: opcionesBase
                });
            }

            let ctxVentas = document.getElementById('chartVentasCentro');
            if (ctxVentas) {
                if (chartVentasInstance) chartVentasInstance.destroy();
                chartVentasInstance = new Chart(ctxVentas, {
                    type: 'doughnut',
                    data: {
                        labels: centrosVentas.map(c => c.nombre),
                        datasets: [{ data: centrosVentas.map(c => c.total), backgroundColor: paletaVentas.slice(0, centrosVentas.length), borderWidth: 1, borderColor: '#ffffff' }]
                    },
                    options: opcionesBase
                });
            }
        }
    }

    // ======================================================
    // TABLAS DE GESTIÓN CONTADOR Y ADMIN
    // ======================================================

    function renderCheques(cheques) {
        let tbody = $('#tablaCheques tbody');
        if (tbody.length === 0) return;

        if (!cheques || cheques.length === 0) {
            tbody.html('<tr><td colspan="3" class="text-center text-muted small py-2">Sin cheques próximos a vencer.</td></tr>');
            return;
        }
        let html = '';
        cheques.forEach(c => {
            html += `
                <tr>
                    <td>${c.fecha_vencimiento}</td>
                    <td class="text-truncate" style="max-width: 120px;" title="${c.banco} #${c.numero}">${c.banco} (#${c.numero})</td>
                    <td class="fw-bold text-end">${formatoMoneda(c.importe)}</td>
                </tr>
            `;
        });
        tbody.html(html);
    }

    function renderVencimientos(vencimientos) {
        let tbody = $('#tablaVencimientos tbody');
        if (tbody.length === 0) return;

        if (!vencimientos || vencimientos.length === 0) {
            tbody.html('<tr><td colspan="3" class="text-center text-muted small py-2">Sin vencimientos pendientes o vencidos este mes.</td></tr>');
            return;
        }
        let html = '';
        vencimientos.forEach(v => {
            let esVencido = (v.estado === 'VENCIDO');
            let badgeEstado = esVencido 
                ? '<span class="badge bg-danger ms-1" style="font-size: 9px;">VENCIDO</span>' 
                : '';

            html += `
                <tr>
                    <td>${v.fecha_vencimiento}</td>
                    <td class="text-truncate" style="max-width: 130px;" title="${v.servicio}">
                        ${v.servicio} ${badgeEstado}
                    </td>
                    <td class="fw-bold text-end text-danger">${formatoMoneda(v.importe)}</td>
                </tr>
            `;
        });
        tbody.html(html);
    }

    function renderFacturasCobrar(facturas) {
        let tbody = $('#tablaFacturasCobrar tbody');
        if (tbody.length === 0) return;

        if (!facturas || facturas.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center text-muted small py-2">No hay facturas pendientes de cobro.</td></tr>');
            return;
        }
        let html = '';
        facturas.forEach(f => {
            let nroComprobante = f.numero ? `#${f.numero}` : 'S/N';
            html += `
                <tr>
                    <td>${f.fecha_vencimiento}</td>
                    <td><span class="badge bg-light text-dark border fw-normal">${nroComprobante}</span></td>
                    <td class="text-truncate" style="max-width: 130px;" title="${f.cliente}">${f.cliente}</td>
                    <td class="fw-bold text-end text-success">${formatoMoneda(f.total)}</td>
                </tr>
            `;
        });
        tbody.html(html);
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
}