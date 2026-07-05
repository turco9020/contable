<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Contable</title>
    </head>
<body>

<div class="topbar d-flex justify-content-between align-items-center footer-lite">

    <div class="text-muted small">
        Sistema Contable © <?= date('Y') ?>
    </div>

    <div class="text-muted small">
        <?= $_SESSION['rol'] ?> · 
        <a href="/contable/auth/logout.php" class="text-decoration-none text-muted">Salir</a>
    </div>

</div>

<style>
.footer-lite {
    height: 40px;
    background: transparent;
    border-top: 1px solid #e5e5e5;
    margin-top: 10px;
    font-size: 12px;
}

/* hover suave */
.footer-lite a:hover {
    color: #000;
}

/* DARK MODE */
body.dark .footer-lite {
    border-color: #444;
    color: #aaa;
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

</body>
</html>
```