<?php

// =====================================
// CONEXIÓN A LA BASE DE DATOS
// =====================================

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "contable"
);

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Charset UTF-8
$conn->set_charset("utf8mb4");

// =====================================
// SESIÓN
// =====================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función auxiliar para chequear si es Administrador sin importar mayúsculas/minúsculas
if (!function_exists('esAdmin')) {
    function esAdmin() {
        return isset($_SESSION['rol']) && strcasecmp($_SESSION['rol'], 'admin') === 0;
    }
}

// =====================================
// SEGURIDAD GLOBAL: CONTROL DE ACCESO
// =====================================

$uri_actual = $_SERVER['REQUEST_URI'];

// 1. Excepciones: Rutas públicas que NUNCA deben bloquearse
$rutas_publicas = ['/auth/login.php', '/auth/logout.php', '/ajax/'];
$es_publico = false;

foreach ($rutas_publicas as $publica) {
    if (strpos($uri_actual, $publica) !== false) {
        $es_publico = true;
        break;
    }
}

if (!$es_publico) {
    
    // VERIFICACIÓN 1: El usuario debe estar logueado
    if (!isset($_SESSION['id'])) {
        header("Location: /contable/auth/login.php");
        exit();
    }

    // VERIFICACIÓN 2: Permisos de acceso según el sidebar
    if (!esAdmin()) {
        
        $rol_actual = strtolower($_SESSION['rol'] ?? '');

        // Mapeo estricto de carpetas según el menú
        $modulos_permisos = [
            // Módulos principales
            '/modules/facturacion/'   => ['admin', 'contador', 'arquitecto'],
            '/modules/reportes/'      => ['admin'],
            '/modules/vencimientos/'  => ['admin', 'contador'],
            '/modules/presupuestos/'  => ['admin', 'contador', 'arquitecto', 'operador'],

            // Submenú Operaciones
            '/modules/clientes/'      => ['admin', 'contador', 'arquitecto'],
            '/modules/cheques/'       => ['admin', 'contador', 'arquitecto'],

            // Submenú Configuraciones
            '/modules/config/obras/'             => ['admin', 'contador', 'arquitecto'],
            '/modules/config/categorias/'        => ['admin'],
            '/modules/config/subcategorias/'     => ['admin'],
            '/modules/config/centros/'           => ['admin'],
            '/modules/config/medios_pago/'       => ['admin'],
            '/modules/config/tipos_comprobante/' => ['admin'],
            '/modules/config/retenciones/'       => ['admin'],
            '/modules/config/cajas/'             => ['admin'],
            '/modules/usuarios/'                 => ['admin'],
            '/modules/roles/'                    => ['admin'],
        ];

        foreach ($modulos_permisos as $ruta_modulo => $roles_permitidos) {
            if (strpos($uri_actual, $ruta_modulo) !== false) {
                
                // Si el rol del usuario NO está en la lista de los permitidos
                if (!in_array($rol_actual, $roles_permitidos)) {
                    
                    // Respuesta para peticiones AJAX
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('HTTP/1.1 403 Forbidden');
                        echo json_encode(['success' => false, 'message' => 'Acceso restringido.']);
                        exit();
                    }

                    // Redirección si intentan ingresar por URL
                    header("Location: /contable/index.php?error=acceso_denegado");
                    exit();
                }
            }
        }
    }
}

?>