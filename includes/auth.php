<?php
if(!isset($_SESSION['id'])){
    header("Location: /contable/auth/login.php");
    exit;
}
function esAdmin(){
    return $_SESSION['rol']=='admin';
}
?>
