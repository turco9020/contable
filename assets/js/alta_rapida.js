/* ==========================================================================
   ALTA RÁPIDA DE CLIENTES Y PROVEEDORES
   --------------------------------------------------------------------------
   Un botón "+" al lado de un desplegable abre Clientes o Proveedores en una
   pestaña nueva con el formulario de alta abierto. Al guardar, esa pestaña
   avisa a las demás, se cierra sola, y el desplegable de origen agrega y
   selecciona el registro nuevo (el formulario a medias no se pierde).

   Uso en el HTML:
     <div class="alta-rapida-wrap">
         <select ...></select>
         <button type="button" class="btn btn-outline-secondary"
                 data-alta-rapida="cliente" title="Nuevo cliente">
             <i class="bi bi-plus-lg"></i>
         </button>
     </div>

   Opcionales en el botón:
     data-valor="nombre"   el select guarda el nombre en lugar del id
     data-mostrar-cuit="1" el texto de la opción es "Nombre (CUIT)"
   ========================================================================== */
(function () {
    const URLS = {
        cliente:   '/contable/modules/clientes/index.php',
        proveedor: '/contable/modules/proveedores/index.php'
    };
    const CANAL = 'contable_alta_rapida';

    // Botón que abrió la última alta: su desplegable es el que se selecciona
    let botonPendiente = null;

    function selectDelBoton(btn) {
        const wrap = btn.closest('.alta-rapida-wrap');
        return wrap ? wrap.querySelector('select') : null;
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-alta-rapida]');
        if (!btn) return;
        e.preventDefault();
        const tipo = btn.dataset.altaRapida;
        if (!URLS[tipo]) return;
        botonPendiente = btn;
        window.open(URLS[tipo] + '?alta_rapida=1', '_blank');
    });

    function recibir(d) {
        if (!d || !URLS[d.tipo] || !d.id) return;

        document.querySelectorAll('[data-alta-rapida="' + d.tipo + '"]').forEach(function (btn) {
            const sel = selectDelBoton(btn);
            if (!sel) return;

            const valor = btn.dataset.valor === 'nombre' ? String(d.nombre) : String(d.id);
            const texto = d.nombre + (btn.dataset.mostrarCuit && d.cuit ? ' (' + d.cuit + ')' : '');
            const elegir = btn === botonPendiente;

            if (sel.tomselect) {
                sel.tomselect.addOption({ value: valor, text: texto });
                if (elegir) sel.tomselect.setValue(valor);
            } else {
                const anterior = sel.value;
                const existe = Array.from(sel.options).some(o => o.value === valor);
                if (!existe) sel.add(new Option(texto, valor));
                if (!elegir) {
                    // Los demás desplegables solo suman la opción, sin cambiar lo elegido
                    sel.value = anterior;
                } else {
                    sel.value = valor;
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });

        botonPendiente = null;
    }

    // Canal entre pestañas (BroadcastChannel o, si no existe, localStorage)
    const canal = ('BroadcastChannel' in window) ? new BroadcastChannel(CANAL) : null;
    if (canal) {
        canal.onmessage = function (e) { recibir(e.data); };
    } else {
        window.addEventListener('storage', function (e) {
            if (e.key !== CANAL || !e.newValue) return;
            try { recibir(JSON.parse(e.newValue)); } catch (_) {}
        });
    }

    window.AltaRapida = {
        // true si esta pestaña se abrió desde un botón "+"
        activa: new URLSearchParams(window.location.search).has('alta_rapida'),

        // Llamar desde Clientes/Proveedores después de guardar un registro nuevo
        avisar: function (tipo, r) {
            const msg = { tipo: tipo, id: r.id, nombre: r.nombre || '', cuit: r.cuit || '' };
            if (canal) {
                canal.postMessage(msg);
            } else {
                try { localStorage.setItem(CANAL, JSON.stringify(Object.assign({ t: Date.now() }, msg))); } catch (_) {}
            }

            // Se intenta cerrar la pestaña; si el navegador no lo permite, se avisa
            window.close();
            setTimeout(function () {
                if (window.Swal) {
                    Swal.fire('Listo', 'Ya quedó cargado. Podés cerrar esta pestaña y volver al formulario.', 'success');
                }
            }, 300);
        }
    };
})();
