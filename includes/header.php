<?php
include __DIR__ . '/../config/database.php';
include __DIR__ . '/auth.php';
?>

<!DOCTYPE html>
<html>
<head>
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
            background-color: #1e1e2f;
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

    </style>
    
    <!-- SweetAlert2 para confirmaciones elegantes -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Agregar en index.php, preferentemente abajo de los includes del header -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    
</head>

<body class="<?php echo $_SESSION['dark'] ?? '' ?>">