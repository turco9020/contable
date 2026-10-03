let catalogoData = { MATERIAL: [], EQUIPO: [], MO: [] };
let modalBs = null;
let modalObsBs = null;
let filaObsActual = null;
let tablaTareas = null;
let tomInstances = [];
let esModoVer = false;

const AJAX_URL = 'ajax/tareas_acciones.php';

function numberFormat(val) {
    let num = parseFloat(val);
    if (isNaN(num)) return '0,00';
    return num.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function renderMoneda(val) {
    return '$ ' + numberFormat(val);
}

document.addEventListener('DOMContentLoaded', () => {
    modalBs = new bootstrap.Modal(document.getElementById('modalTarea'));
    modalObsBs = new bootstrap.Modal(document.getElementById('modalObsFila'));
    
    // Reglas CSS dinámicas
    if (!document.getElementById('style-task-truncate')) {
        const style = document.createElement('style');
        style.id = 'style-task-truncate';
        style.innerHTML = `
            .text-truncate-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;  
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: normal;
                word-break: break-word;
                line-height: 1.3;
            }
            .btn-obs-fila {
                min-width: 36px;
                height: 31px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
        `;
        document.head.appendChild(style);
    }

    tablaTareas = $('#tablaTareas').DataTable({
        ajax: {
            url: `${AJAX_URL}?action=listar`,
            dataSrc: 'data'
        },
        order: [[0, 'desc']],
        responsive: false,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
        buttons: [
            { extend: 'excelHtml5', text: ' Excel', className: 'btn btn-success btn-sm' },
            { extend: 'print', text: ' Imprimir', className: 'btn btn-secondary btn-sm' }
        ],
    columns: [
           { 
                data: 'codigo', 
                className: 'text-center align-middle',
                render: function(d) {
                    if (!d) return '<span class="text-muted small">-</span>';
                    return `<span class="text-secondary" style="font-size: 0.75rem;">${d}</span>`;
                }
            }, 
            { 
                data: 'nombre', 
                render: function(data) {
                    if (!data) return '-';
                    const safeTitle = data.replace(/"/g, '&quot;');
                    return `<div class="fw-semibold text-dark text-truncate-2" title="${safeTitle}">${data}</div>`;
                }
            },
            { data: 'unidad', className: 'text-center', render: d => `<span class="badge bg-light text-dark border fw-normal">${d || 'GL'}</span>` },
            { data: 'costo_unitario_total', className: 'text-end fw-bold text-success', render: d => renderMoneda(d) },
            { 
                // Contempla ambas variantes de la propiedad (usuario_nombre o usuario)
                data: null, 
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    let usr = row.usuario_nombre || row.usuario || 'Sistema';
                    return `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person me-1"></i>${usr}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    return `
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-sm btn-outline-danger" onclick="abrirModalImpresion(${row.id})" title="Imprimir APU con Coeficiente K">
                                <i class="bi bi-file-pdf"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" title="Ver" onclick="verTarea(${row.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editarTarea(${row.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarTarea(${row.id})">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    // BÚSQUEDA Y FILTROS
    $('#filter_codigo').on('keyup change', function() { tablaTareas.column(0).search(this.value).draw(); });
    $('#filter_nombre').on('keyup change', function() { tablaTareas.column(1).search(this.value).draw(); });
    $('#filter_usuario').on('keyup change', function() { tablaTareas.column(4).search(this.value).draw(); });

    cargarCatalogo();
});

function cargarCatalogo() {
    fetch(`${AJAX_URL}?action=obtener_catalogo`)
        .then(r => r.json())
        .then(res => {
            if (res.success) catalogoData = res.catalogo;
        });
}

function destruirTomSelects() {
    tomInstances.forEach(ts => ts.destroy());
    tomInstances = [];
}

function limpiarTablaDesglose() {
    destruirTomSelects();
    ['MATERIAL', 'MO', 'EQUIPO', 'SUBCONTRATO'].forEach(rubro => {
        const sec = document.getElementById(`sec_${rubro}`);
        while (sec.children.length > 1) {
            sec.removeChild(sec.lastChild);
        }
    });
}

function setEstadoFormularioReadOnly(isReadOnly) {
    esModoVer = isReadOnly;
    const inputs = document.querySelectorAll('#modalTarea input, #modalTarea select, #modalTarea textarea');
    inputs.forEach(el => {
        if (el.id !== 'tarea_id') {
            el.disabled = isReadOnly;
        }
    });

    tomInstances.forEach(ts => {
        if (isReadOnly) ts.disable();
        else ts.enable();
    });

    document.querySelectorAll('.btn-add-row, .btn-remove-row').forEach(btn => {
        btn.style.display = isReadOnly ? 'none' : '';
    });

    document.getElementById('btnGuardarAPU').style.display = isReadOnly ? 'none' : '';
    document.getElementById('modalTareaTitle').innerHTML = isReadOnly 
        ? '<i class="bi bi-eye me-2"></i>Ver Análisis de Precio Unitario (APU)' 
        : '<i class="bi bi-tools me-2"></i>Análisis de Precio Unitario (APU)';
}

function nuevaTarea() {
    esModoVer = false;
    document.getElementById('tarea_id').value = '0';
    document.getElementById('tarea_codigo').value = '';
    document.getElementById('tarea_nombre').value = '';
    document.getElementById('tarea_unidad').value = 'M3';
    document.getElementById('tarea_rendimiento').value = '1.00';
    document.getElementById('tarea_observaciones').value = '';
    
    limpiarTablaDesglose();

    agregarFila('MATERIAL');
    agregarFila('MO');
    agregarFila('EQUIPO');

    recalcularTotales();
    setEstadoFormularioReadOnly(false);
    modalBs.show();
}

function agregarFila(tipo, item = null) {
    const sec = document.getElementById(`sec_${tipo}`);
    const tr = document.createElement('tr');
    tr.className = 'fila-item';
    tr.dataset.tipo = tipo;

    let columnaRecursoHtml = '';
    let fechaActualizacionInsumo = '-';
    const obsTexto = item ? (item.observaciones || '') : '';

    if (tipo === 'SUBCONTRATO') {
        columnaRecursoHtml = `
            <input type="text" class="form-control form-control-sm desc-recurso" 
                   value="${item ? item.descripcion : ''}" 
                   placeholder="Escriba la descripción del subcontrato..." 
                   oninput="calcularFila(this)">
            <input type="hidden" class="recurso-id" value="">
        `;
    } else {
        const list = catalogoData[tipo] || [];
        let optionsHtml = '<option value="">-- Buscar recurso o escribir nuevo --</option>';
        list.forEach(opt => {
            optionsHtml += `<option value="${opt.id}">${opt.nombre}</option>`;
        });

        columnaRecursoHtml = `
            <select class="form-select form-select-sm selector-recurso">${optionsHtml}</select>
            <input type="hidden" class="desc-recurso" value="${item ? item.descripcion : ''}">
            <input type="hidden" class="recurso-id" value="${item ? (item.recurso_id || '') : ''}">
        `;

        if (item && item.recurso_id) {
            const optMatch = list.find(o => o.id == item.recurso_id);
            if (optMatch && optMatch.fecha_actualizacion) {
                fechaActualizacionInsumo = optMatch.fecha_actualizacion;
            }
        }
    }

    // Configurar HTML del botón Obs. según si contiene nota guardada
    const tieneNota = obsTexto.trim().length > 0;
    const btnClass = tieneNota ? "btn btn-sm btn-primary btn-obs-fila position-relative" : "btn btn-sm btn-outline-secondary btn-obs-fila";
    const btnIcon = tieneNota 
        ? `<i class="bi bi-file-earmark-text-fill"></i><span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle"></span>` 
        : `<i class="bi bi-plus-lg"></i>`;

    tr.innerHTML = `
        <td>${columnaRecursoHtml}</td>
        <td class="text-center align-middle"><span class="badge bg-light text-muted border fw-normal col-fecha-act">${fechaActualizacionInsumo}</span></td>
        <td class="text-center"><input type="text" class="form-control form-control-sm text-center unidad-recurso" value="${item ? item.unidad : (tipo === 'SUBCONTRATO' ? 'GL' : 'UN')}"></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm text-end precio-recurso" value="${item ? item.precio_unitario : '0.00'}" oninput="calcularFila(this)"></td>
        <td><input type="number" step="0.0001" class="form-control form-control-sm text-end cantidad-recurso" value="${item ? item.cantidad : '1.00'}" oninput="calcularFila(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-end bg-light subtotal-recurso" readonly value="${item ? renderMoneda(item.subtotal) : '$ 0,00'}"></td>
        <td class="text-center align-middle">
            <input type="hidden" class="obs-recurso" value="${obsTexto}">
            <button type="button" class="${btnClass}" onclick="abrirModalObsFila(this)" title="Observación / Nota del Recurso">
                ${btnIcon}
            </button>
        </td>
        <td class="text-center align-middle">
            <button class="btn btn-sm btn-link text-danger p-0 btn-remove-row" title="Quitar fila" onclick="eliminarFila(this)"><i class="bi bi-x-circle-fill fs-6"></i></button>
        </td>
    `;

    sec.appendChild(tr);

    if (tipo !== 'SUBCONTRATO') {
        const selectElem = tr.querySelector('.selector-recurso');
        const list = catalogoData[tipo] || [];

        const ts = new TomSelect(selectElem, {
            create: true,
            persist: false,
            createOnBlur: true,
            placeholder: "Buscar recurso...",
            onChange: function(val) {
                const badgeFecha = tr.querySelector('.col-fecha-act');
                if (!val) {
                    if (badgeFecha) badgeFecha.textContent = '-';
                    return;
                }
                const optSelected = list.find(o => o.id == val);
                if (optSelected) {
                    tr.querySelector('.desc-recurso').value = optSelected.nombre;
                    tr.querySelector('.recurso-id').value = optSelected.id;
                    tr.querySelector('.unidad-recurso').value = optSelected.unidad;
                    tr.querySelector('.precio-recurso').value = parseFloat(optSelected.precio).toFixed(2);
                    if (badgeFecha) badgeFecha.textContent = optSelected.fecha_actualizacion || '-';
                } else {
                    tr.querySelector('.desc-recurso').value = val;
                    tr.querySelector('.recurso-id').value = '';
                    if (badgeFecha) badgeFecha.textContent = '-';
                }
                calcularFila(selectElem);
            }
        });

        tomInstances.push(ts);

        if (item) {
            if (item.recurso_id && list.some(o => o.id == item.recurso_id)) {
                ts.setValue(item.recurso_id, true);
            } else if (item.descripcion) {
                ts.addOption({ value: item.descripcion, text: item.descripcion });
                ts.setValue(item.descripcion, true);
            }
        }
    }

    if (esModoVer) {
        tr.querySelectorAll('input, select').forEach(i => i.disabled = true);
        const btnRem = tr.querySelector('.btn-remove-row');
        if (btnRem) btnRem.style.display = 'none';
    }

    if (!item) recalcularTotales();
}

/* LÓGICA MODAL OBSERVACIONES POR FILA */
function abrirModalObsFila(btn) {
    filaObsActual = btn.closest('tr');
    
    let nombreRecurso = filaObsActual.querySelector('.desc-recurso').value.trim();
    if (!nombreRecurso) {
        nombreRecurso = "Nuevo Insumo / Recurso";
    }

    document.getElementById('modalObsRecursoNombre').innerText = 'Recurso: ' + nombreRecurso;
    
    const textoGuardado = filaObsActual.querySelector('.obs-recurso').value;
    const txtArea = document.getElementById('modalObsTexto');
    txtArea.value = textoGuardado;
    
    txtArea.disabled = esModoVer;
    modalObsBs.show();
}

function confirmarObsFila() {
    if (!filaObsActual) return;

    const texto = document.getElementById('modalObsTexto').value.trim();
    filaObsActual.querySelector('.obs-recurso').value = texto;

    const btn = filaObsActual.querySelector('.btn-obs-fila');

    if (texto.length > 0) {
        btn.className = "btn btn-sm btn-primary btn-obs-fila position-relative";
        btn.innerHTML = `<i class="bi bi-file-earmark-text-fill"></i><span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle"></span>`;
    } else {
        btn.className = "btn btn-sm btn-outline-secondary btn-obs-fila";
        btn.innerHTML = `<i class="bi bi-plus-lg"></i>`;
    }

    modalObsBs.hide();
}

function calcularFila(elem) {
    const tr = elem.closest('tr');
    const precio = parseFloat(tr.querySelector('.precio-recurso').value) || 0;
    const cantidad = parseFloat(tr.querySelector('.cantidad-recurso').value) || 0;
    const subtotal = precio * cantidad;
    tr.querySelector('.subtotal-recurso').value = renderMoneda(subtotal);
    recalcularTotales();
}

function eliminarFila(btn) {
    const tr = btn.closest('tr');
    const selectElem = tr.querySelector('.selector-recurso');
    if (selectElem && selectElem.tomselect) {
        const idx = tomInstances.indexOf(selectElem.tomselect);
        if (idx > -1) tomInstances.splice(idx, 1);
        selectElem.tomselect.destroy();
    }
    tr.remove();
    recalcularTotales();
}

function recalcularTotales() {
    let subtotales = { MATERIAL: 0, MO: 0, EQUIPO: 0, SUBCONTRATO: 0 };

    document.querySelectorAll('.fila-item').forEach(tr => {
        const tipo = tr.dataset.tipo;
        const precio = parseFloat(tr.querySelector('.precio-recurso').value) || 0;
        const cantidad = parseFloat(tr.querySelector('.cantidad-recurso').value) || 0;
        const subtotal = precio * cantidad;

        if (subtotales[tipo] !== undefined) {
            subtotales[tipo] += subtotal;
        }
    });

    document.getElementById('subtotal_MATERIAL').innerText = renderMoneda(subtotales.MATERIAL);
    document.getElementById('subtotal_MO').innerText = renderMoneda(subtotales.MO);
    document.getElementById('subtotal_EQUIPO').innerText = renderMoneda(subtotales.EQUIPO);
    document.getElementById('subtotal_SUBCONTRATO').innerText = renderMoneda(subtotales.SUBCONTRATO);

    const totalGeneral = subtotales.MATERIAL + subtotales.MO + subtotales.EQUIPO + subtotales.SUBCONTRATO;
    document.getElementById('costo_total_tarea').innerText = renderMoneda(totalGeneral);
}

function guardarTarea() {
    if (esModoVer) return;

    const id = document.getElementById('tarea_id').value;
    const nombre = document.getElementById('tarea_nombre').value.trim();
    if (!nombre) {
        Swal.fire('Atención', 'Por favor ingrese el nombre de la tarea.', 'warning');
        return;
    }

    const detalles = [];
    let subtotales = { MATERIAL: 0, MO: 0, EQUIPO: 0, SUBCONTRATO: 0 };

    document.querySelectorAll('.fila-item').forEach(tr => {
        const tipo = tr.dataset.tipo;
        const desc = tr.querySelector('.desc-recurso').value.trim();
        const unidad = tr.querySelector('.unidad-recurso').value.trim();
        const precio = parseFloat(tr.querySelector('.precio-recurso').value) || 0;
        const cantidad = parseFloat(tr.querySelector('.cantidad-recurso').value) || 0;
        const subtotal = precio * cantidad;
        const obs = tr.querySelector('.obs-recurso').value.trim();
        const recurso_id = tr.querySelector('.recurso-id') ? tr.querySelector('.recurso-id').value : '';

        if (desc !== '') {
            detalles.push({
                tipo: tipo,
                recurso_id: recurso_id,
                descripcion: desc,
                unidad: unidad,
                precio_unitario: precio,
                cantidad: cantidad,
                subtotal: subtotal,
                observaciones: obs
            });

            if (subtotales[tipo] !== undefined) subtotales[tipo] += subtotal;
        }
    });

    const payload = {
        id: id,
        codigo: document.getElementById('tarea_codigo').value,
        nombre: nombre,
        unidad: document.getElementById('tarea_unidad').value,
        rendimiento_unidad: document.getElementById('tarea_rendimiento').value,
        observaciones: document.getElementById('tarea_observaciones').value,
        costo_materiales: subtotales.MATERIAL,
        costo_mo: subtotales.MO,
        costo_equipos: subtotales.EQUIPO,
        costo_subcontratos: subtotales.SUBCONTRATO,
        detalles: detalles
    };

    fetch(`${AJAX_URL}?action=guardar_tarea`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            modalBs.hide();
            tablaTareas.ajax.reload(null, false);
            Swal.fire({
                title: '¡Guardado!',
                text: res.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Error', res.message || 'Error al guardar la tarea.', 'error');
        }
    });
}

function verTarea(id) {
    cargarDatosTarea(id, true);
}

function editarTarea(id) {
    cargarDatosTarea(id, false);
}

function cargarDatosTarea(id, isReadOnly) {
    fetch(`${AJAX_URL}?action=obtener_tarea&id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const t = res.tarea;
                document.getElementById('tarea_id').value = t.id;
                document.getElementById('tarea_codigo').value = t.codigo || '';
                document.getElementById('tarea_nombre').value = t.nombre;
                document.getElementById('tarea_unidad').value = t.unidad;
                document.getElementById('tarea_rendimiento').value = t.rendimiento_unidad;
                document.getElementById('tarea_observaciones').value = t.observaciones || '';

                limpiarTablaDesglose();

                if (t.detalles && t.detalles.length > 0) {
                    t.detalles.forEach(det => {
                        agregarFila(det.tipo, det);
                    });
                }
                recalcularTotales();
                setEstadoFormularioReadOnly(isReadOnly);
                modalBs.show();
            }
        });
}

function eliminarTarea(id) {
    Swal.fire({
        title: '¿Eliminar Tarea / APU?',
        text: 'Se eliminará la tarea y todos sus desgloses.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('id', id);
            fetch(`${AJAX_URL}?action=eliminar_tarea`, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        tablaTareas.ajax.reload(null, false);
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'La tarea fue eliminada correctamente.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', 'No se pudo eliminar la tarea.', 'error');
                    }
                });
        }
    });
}