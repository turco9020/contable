/* ==========================================================================
   DOCUMENTACIÓN MÚLTIPLE (Clientes / Proveedores)
   --------------------------------------------------------------------------
   Maneja la sección "Documentación" del modal: muestra los archivos ya
   cargados (Ver / Eliminar) y limpia el selector de archivos nuevos, que se
   suben junto con el formulario al tocar Guardar.

   HTML esperado en el modal:
     <input type="file" name="doc_archivos[]" id="doc_archivos" multiple>
     <ul id="doc_lista"></ul>

   Uso:
     Documentacion.init({ endpoint: '/contable/ajax/clientes.php', onCambio: () => tabla.ajax.reload() });
     Documentacion.limpiar();              // alta nueva
     Documentacion.cargar(id, soloLectura); // editar / ver
   ========================================================================== */
window.Documentacion = (function () {
    let cfg = { endpoint: '', onCambio: null };
    let actual = { id: 0, soloLectura: false };
    let secuencia = 0; // descarta respuestas viejas si se abre otro registro rápido

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function icono(nombre) {
        const ext = String(nombre).split('.').pop().toLowerCase();
        if (ext === 'pdf') return 'bi-file-earmark-pdf text-danger';
        if (['jpg', 'jpeg', 'png'].includes(ext)) return 'bi-file-earmark-image text-primary';
        if (['doc', 'docx'].includes(ext)) return 'bi-file-earmark-word text-primary';
        if (['xls', 'xlsx'].includes(ext)) return 'bi-file-earmark-excel text-success';
        return 'bi-file-earmark';
    }

    function mensaje(texto) {
        $('#doc_lista').html(`<li class="list-group-item text-muted text-center py-2">${texto}</li>`);
    }

    function render(archivos) {
        if (!archivos.length) {
            mensaje('No hay archivos cargados.');
            return;
        }
        const filas = archivos.map(a => {
            const fecha = a.fecha_subida ? String(a.fecha_subida).substring(0, 10).split('-').reverse().join('/') : '';
            const btnEliminar = actual.soloLectura ? '' : `
                <button type="button" class="btn btn-sm btn-outline-danger" data-doc-eliminar="${a.id}" data-doc-nombre="${esc(a.nombre_original)}" title="Eliminar archivo">
                    <i class="bi bi-trash3"></i>
                </button>`;
            return `
                <li class="list-group-item d-flex align-items-center gap-2 py-2">
                    <i class="bi ${icono(a.archivo)} fs-5"></i>
                    <div class="flex-grow-1 text-truncate" style="min-width:0;">
                        <div class="text-truncate fw-semibold">${esc(a.nombre_original)}</div>
                        <small class="text-muted">${esc(fecha)}</small>
                    </div>
                    <a href="${esc(a.url)}" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver archivo"><i class="bi bi-eye"></i></a>
                    ${btnEliminar}
                </li>`;
        });
        $('#doc_lista').html(filas.join(''));
    }

    function cargar(id, soloLectura) {
        actual = { id: id, soloLectura: !!soloLectura };
        const mia = ++secuencia;
        $('#doc_archivos').val('');
        mensaje('Cargando archivos...');

        $.getJSON(cfg.endpoint + '?accion=listar_archivos&id=' + encodeURIComponent(id))
            .done(r => { if (mia === secuencia) render((r && r.archivos) || []); })
            .fail(() => { if (mia === secuencia) mensaje('No se pudieron cargar los archivos.'); });
    }

    $(document).on('click', '[data-doc-eliminar]', function () {
        const archivoId = $(this).data('doc-eliminar');
        const nombre = $(this).data('doc-nombre');

        Swal.fire({
            title: '¿Eliminar archivo?',
            text: `"${nombre}" se borrará permanentemente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#212529',
            confirmButtonText: 'Eliminar archivo',
            cancelButtonText: 'Cancelar'
        }).then(res => {
            if (!res.isConfirmed) return;
            $.post(cfg.endpoint + '?accion=eliminar_archivo', { id: archivoId }, function () {
                cargar(actual.id, actual.soloLectura);
                if (cfg.onCambio) cfg.onCambio();
            });
        });
    });

    return {
        init: function (c) { cfg = Object.assign(cfg, c); },

        limpiar: function () {
            secuencia++;
            actual = { id: 0, soloLectura: false };
            $('#doc_archivos').val('');
            mensaje('Aún no hay archivos. Elegilos arriba y se suben al guardar.');
        },

        cargar: cargar,

        // Avisa si algún archivo no se pudo subir (por ejemplo, por superar el tamaño máximo)
        avisarNoSubidos: function (r) {
            if (r && r.no_subidos && r.no_subidos.length) {
                Swal.fire('Atención', 'Se guardaron los datos, pero no se pudieron subir estos archivos: '
                    + r.no_subidos.join(', ') + '. Revisá que no superen el tamaño máximo permitido.', 'warning');
                return true;
            }
            return false;
        },

        // Botón del listado: clip con la cantidad de archivos (vacío si no tiene)
        botonListado: function (cantidad, onclick) {
            const n = parseInt(cantidad, 10) || 0;
            if (!n) return '';
            return `<button class="btn btn-sm btn-outline-dark" title="Documentación (${n})" onclick='${onclick}'>
                        <i class="bi bi-paperclip"></i>${n}
                    </button>`;
        }
    };
})();
