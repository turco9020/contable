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

?>