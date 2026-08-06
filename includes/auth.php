<?php
// Si la sesión no arrancó por alguna configuración del servidor, la iniciamos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bloqueo por defecto: Si no inició sesión, afuera
if (!isset($_SESSION['id'])) {
    header("Location: /contable/auth/login.php");
    exit;
}

// Declaración protegida para la función esAdmin
if (!function_exists('esAdmin')) {
    function esAdmin() {
        return isset($_SESSION['rol']) && strcasecmp($_SESSION['rol'], 'admin') === 0;
    }
}

// Declaración protegida para la función tieneRol
if (!function_exists('tieneRol')) {
    /**
     * Nueva función flexible para validar múltiples roles autorizados en tus módulos
     */
    function tieneRol($rolesPermitidos) {
        if (!isset($_SESSION['rol'])) {
            return false;
        }
        
        if (!is_array($rolesPermitidos)) {
            $rolesPermitidos = [$rolesPermitidos];
        }
        
        // Pasamos todo a minúsculas para que la comparación sea 100% segura
        $rolUsuario = strtolower($_SESSION['rol']);
        $rolesPermitidosMin = array_map('strtolower', $rolesPermitidos);
        
        return in_array($rolUsuario, $rolesPermitidosMin);
    }
}
?>