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

// Tu función esAdmin mejorada (strcasecmp compara sin importar mayúsculas/minúsculas)
function esAdmin() {
    return isset($_SESSION['rol']) && strcasecmp($_SESSION['rol'], 'admin') === 0;
}

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
    
    // CORREGIDO: in_array con guion bajo
    return in_array($rolUsuario, $rolesPermitidosMin);
}
?>