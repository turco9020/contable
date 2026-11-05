<div class="d-flex justify-content-between align-items-center footer-lite">
    <div class="text-muted small">
        Sistema Contable © Recursos Globales® <?= date('Y') ?>
    </div>

    <div class="text-muted small">
        <?= $_SESSION['rol'] ?> · 
        <a href="/contable/auth/logout.php" class="text-decoration-none text-muted">Salir</a>
    </div>
</div>

<style>
/* FOOTER FIJO AL PIE DE LA VENTANA (el contenido se desplaza por detrás) */
.footer-lite {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1020; /* debajo del sidebar (1040) y de los modales (1050+) */
    height: 40px;
    padding: 0 1rem;
    background-color: #f4f6f9;
    border-top: 1px solid #e5e5e5;
    font-size: 12px;
    box-sizing: border-box;
}

/* Espacio al final de la página para que el footer no tape el contenido */
body {
    padding-bottom: 50px;
}

/* En escritorio arranca después del sidebar (260px) */
@media (min-width: 992px) {
    .footer-lite {
        left: 260px;
    }
}

/* hover suave */
.footer-lite a:hover {
    color: #000;
}

/* DARK MODE */
body.dark .footer-lite {
    background-color: #181a1f;
    border-color: #444;
    color: #aaa;
}
body.dark .footer-lite a:hover {
    color: #fff;
}

/* MODALES MOVIBLES: en escritorio se arrastran desde el título */
@media (min-width: 992px) {
    .modal-header {
        cursor: move;
    }
    .modal-header .btn-close {
        cursor: pointer;
    }
}

/* MAYUSCULAS GLOBAL */
input, textarea {
    text-transform: uppercase;
}

/* link activo */
.menu-link.active {
    background: #2f3236;
    color: #fff;
    border-left: 3px solid #0d6efd;
}
</style>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<!-- CDN de Chart.js -->
<!-- Opción A: CDN con bundle completo -->
<script src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables BASE -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Responsive (DESPUÉS de DataTables) -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

<!-- Dependencias -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- Botones -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

<script>
// MAYÚSCULAS GLOBAL (inputs y textarea)
document.addEventListener('input', function(e){
    let tag = e.target.tagName;

    if(tag === 'INPUT' || tag === 'TEXTAREA'){
        if(e.target.type !== 'password' && e.target.type !== 'email'){
            e.target.value = e.target.value.toUpperCase();
        }
    }
});
</script>

<script>
// VISTA DE 25 FILAS POR DEFECTO EN DATATABLES
$.extend(true, $.fn.dataTable.defaults, {
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100]
});
</script>

<script>
// TRADUCCIÓN DE DATATABLES AL ESPAÑOL
$.extend(true, $.fn.dataTable.defaults, {
    language: {
    "decimal": ",",
    "thousands": ".",
    "lengthMenu": "Mostrar _MENU_ registros",
    "zeroRecords": "No se encontraron resultados",
    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
    "infoFiltered": "(filtrado de _MAX_ registros totales)",
    "search": "Buscar:",
    "paginate": {
        "first": "Primero",
        "last": "Último",
        "next": "Siguiente",
        "previous": "Anterior"
    }
    }
});
</script>

<script>
// ==========================================
// CONFIGURACIÓN GLOBAL DE MODALES (TECLA ESC)
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.Default.keyboard = true; 
    }

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            let modalAbierto = document.querySelector('.modal.show');
            if (modalAbierto) {
                let instanciaModal = bootstrap.Modal.getInstance(modalAbierto);
                if (instanciaModal) {
                    instanciaModal.hide();
                }
            }
        }
    });
});
</script>

<script>
// ==========================================
// MODALES MOVIBLES (SOLO ESCRITORIO)
// Se arrastran tomándolos del título; al cerrarlos vuelven a su lugar
// ==========================================
$(document).on('shown.bs.modal', '.modal', function () {
    if (!window.matchMedia('(min-width: 992px)').matches || !$.fn.draggable) return;

    let $dialog = $(this).find('.modal-dialog');
    if (!$dialog.data('ui-draggable')) {
        $dialog.draggable({
            handle: '.modal-header',
            cancel: 'button, a, input, select, textarea'
        });
    }
});

$(document).on('hidden.bs.modal', '.modal', function () {
    $(this).find('.modal-dialog').css({ top: '', left: '' });
});
</script>

</body>
</html>