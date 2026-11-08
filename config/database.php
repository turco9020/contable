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

// Solo el path, sin query string: si no, "?x=/ajax/" en cualquier URL saltearía el control
$uri_actual = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

// 1. Excepciones: Rutas públicas que NUNCA deben bloquearse (path exacto)
$rutas_publicas = ['/contable/auth/login.php', '/contable/auth/logout.php'];
$es_publico = in_array($uri_actual, $rutas_publicas, true);

// Endpoints AJAX: carpetas /ajax/ y archivos modules/*/ajax_*.php
$es_carpeta_ajax = strpos($uri_actual, '/ajax/') !== false;
$es_endpoint_ajax = $es_carpeta_ajax || strpos(basename($uri_actual), 'ajax_') === 0;

if (!$es_publico) {

    // VERIFICACIÓN 1: El usuario debe estar logueado
    if (!isset($_SESSION['id'])) {
        if ($es_endpoint_ajax) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Sesión expirada. Volvé a iniciar sesión.']);
            exit();
        }
        header("Location: /contable/auth/login.php");
        exit();
    }

    // VERIFICACIÓN 2: Permisos de acceso según el sidebar
    // (los AJAX de carpetas /ajax/ siguen sin chequeo de rol por módulo, como antes)
    if (!esAdmin() && !$es_carpeta_ajax) {
        
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