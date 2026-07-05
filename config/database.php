<?php
$conn = new mysqli("localhost","root","","contable");
if ($conn->connect_error) die("Error DB");
session_start();
?>
