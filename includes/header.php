<?php
include __DIR__ . '/../config/database.php';
include __DIR__ . '/auth.php';
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="<?= (($_SESSION['dark'] ?? '') === 'dark') ? 'dark' : 'light' ?>">
<head>
    <meta charset="UTF-8">
    <title>Sistema Contable</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <!-- DataTables botones-->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">


    <style>

        body {
            background-color: #f4f6f9;
        }

        body.dark {
            background-color: #181a1f;
            color: #ddd;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            position: fixed;
            background: #2c2f33;
            color: #fff;
        }

        .sidebar a {
            color: #ccc;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #40444b;
            color: #fff;
        }
        /* MENÚ GASTOS */

.menu-section > a {
    display: block;
    padding: 10px 15px;
    color: #ddd;
    text-decoration: none;
    font-weight: 500;
    transition: 0.2s;
}

.menu-section > a:hover {
    background: #3a3d42;
}

/* SUBMENÚ */
.submenu {
    display: none;
    flex-direction: column;
    padding-left: 10px;
}

.submenu a {
    display: block;
    padding: 6px 15px;
    font-size: 13px;
    color: #bbb;
    text-decoration: none;
    border-left: 2px solid transparent;
}

.submenu a:hover {
    background: #2f3236;
    border-left: 2px solid #888;
    color: #fff;
}

/* abierto */
.submenu.show {
    display: block;
}

        .content {
            margin-left: 240px;
            padding: 20px;
        }

        .topbar {
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #ddd;
            margin-left: 240px;
            padding: 10px 20px;
        }

        body.dark .topbar {
            background: #2c2f33;
            border-color: #444;
        }

        .card {
            border: none;
            border-radius: 10px;
        }

        table.dataTable td {
           white-space: nowrap;
        }

.card-caja {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.card-caja:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
}

/* =====================================================================
   MODO OSCURO
   Bootstrap 5.3 adapta solo cards, modales, inputs y tablas con data-bs-theme="dark".
   Estas reglas corrigen las clases y colores claros fijos que usan los módulos.
   ===================================================================== */
[data-bs-theme="dark"] .bg-white {
    background-color: var(--bs-body-bg) !important;
}
[data-bs-theme="dark"] .bg-light {
    background-color: var(--bs-tertiary-bg) !important;
}
[data-bs-theme="dark"] .text-dark:not(.bg-warning):not(.bg-info) {
    color: var(--bs-body-color) !important;
}
[data-bs-theme="dark"] .table-light {
    --bs-table-bg: var(--bs-tertiary-bg);
    --bs-table-color: var(--bs-body-color);
    --bs-table-border-color: var(--bs-border-color);
    --bs-table-striped-bg: var(--bs-secondary-bg);
    --bs-table-hover-bg: var(--bs-secondary-bg);
    color: var(--bs-body-color);
    border-color: var(--bs-border-color);
}
[data-bs-theme="dark"] .btn-light {
    --bs-btn-bg: var(--bs-tertiary-bg);
    --bs-btn-color: var(--bs-body-color);
    --bs-btn-border-color: var(--bs-border-color);
    --bs-btn-hover-bg: var(--bs-secondary-bg);
    --bs-btn-hover-color: var(--bs-body-color);
    --bs-btn-hover-border-color: var(--bs-border-color);
    --bs-btn-active-bg: var(--bs-secondary-bg);
    --bs-btn-active-color: var(--bs-body-color);
}
[data-bs-theme="dark"] .btn-outline-dark {
    --bs-btn-color: #dee2e6;
    --bs-btn-border-color: #6c757d;
    --bs-btn-hover-bg: #495057;
    --bs-btn-hover-color: #fff;
    --bs-btn-hover-border-color: #6c757d;
    --bs-btn-active-bg: #495057;
    --bs-btn-active-color: #fff;
}
[data-bs-theme="dark"] .border-dark {
    border-color: var(--bs-border-color) !important;
}

/* Fondos claros escritos en línea (style="background-color: #...").
   rgb(255, 255, 255) cubre los casos en que JS reescribe el style (ej: efectos al pasar el mouse) */
[data-bs-theme="dark"] [style*="background-color: rgb(255, 255, 255)"],
[data-bs-theme="dark"] [style*="background: rgb(255, 255, 255)"],
[data-bs-theme="dark"] [style*="background-color: #fff"],
[data-bs-theme="dark"] [style*="background: #fff"],
[data-bs-theme="dark"] [style*="background-color: #f8f9fa"],
[data-bs-theme="dark"] [style*="background-color: #f9f9f9"],
[data-bs-theme="dark"] [style*="background-color: #f4f6f"],
[data-bs-theme="dark"] [style*="background-color: #e9ecef"],
[data-bs-theme="dark"] [style*="background-color: #e2e3e5"] {
    background-color: var(--bs-tertiary-bg) !important;
}
[data-bs-theme="dark"] [style*="background-color: #fef9e7"],
[data-bs-theme="dark"] [style*="background-color: #fffbeb"],
[data-bs-theme="dark"] [style*="background-color: #ffeef0"],
[data-bs-theme="dark"] [style*="background-color: #f0fdf4"],
[data-bs-theme="dark"] [style*="background-color: #f0f7ff"] {
    background-color: var(--bs-secondary-bg) !important;
}

/* Tom Select (trae fondo blanco fijo) */
[data-bs-theme="dark"] .ts-control,
[data-bs-theme="dark"] .ts-wrapper.single.input-active .ts-control,
[data-bs-theme="dark"] .ts-dropdown {
    background-color: var(--bs-body-bg) !important;
    color: var(--bs-body-color) !important;
    border-color: var(--bs-border-color) !important;
}
[data-bs-theme="dark"] .ts-control input {
    color: var(--bs-body-color);
}
[data-bs-theme="dark"] .ts-dropdown .active,
[data-bs-theme="dark"] .ts-dropdown .option:hover {
    background-color: var(--bs-secondary-bg);
    color: var(--bs-body-color);
}

/* DataTables: filas alternadas visibles sobre fondo oscuro */
[data-bs-theme="dark"] table.dataTable.table-striped > tbody > tr.odd > * {
    box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.04);
}

/* Editor Quill (Condiciones Generales): bordes e íconos claros fijos */
[data-bs-theme="dark"] .ql-toolbar.ql-snow,
[data-bs-theme="dark"] .ql-container.ql-snow {
    border-color: var(--bs-border-color);
}
[data-bs-theme="dark"] .ql-toolbar.ql-snow {
    background-color: var(--bs-tertiary-bg);
}
[data-bs-theme="dark"] .ql-snow .ql-stroke {
    stroke: var(--bs-body-color);
}
[data-bs-theme="dark"] .ql-snow .ql-fill {
    fill: var(--bs-body-color);
}
[data-bs-theme="dark"] .ql-snow.ql-toolbar button:hover .ql-stroke,
[data-bs-theme="dark"] .ql-snow.ql-toolbar button.ql-active .ql-stroke {
    stroke: #6ea8fe;
}
[data-bs-theme="dark"] .ql-snow.ql-toolbar button:hover .ql-fill,
[data-bs-theme="dark"] .ql-snow.ql-toolbar button.ql-active .ql-fill {
    fill: #6ea8fe;
}
[data-bs-theme="dark"] .ql-editor.ql-blank::before {
    color: var(--bs-secondary-color);
}

    </style>
    
    <!-- SweetAlert2 para confirmaciones elegantes -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Agregar en index.php, preferentemente abajo de los includes del header -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    
</head>

<body class="<?php echo $_SESSION['dark'] ?? '' ?>">