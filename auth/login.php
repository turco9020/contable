<?php
include '../config/database.php';

$error = '';

if($_POST){
    $u = $_POST['user'];
    $p = $_POST['pass'];

    $r = $conn->query("SELECT u.*, r.nombre as rol 
                       FROM usuarios u 
                       JOIN roles r ON u.rol_id = r.id 
                       WHERE usuario='$u'");
    $d = $r->fetch_assoc();

    if($d && password_verify($p, $d['password'])){
        $_SESSION['id'] = $d['id'];
        $_SESSION['rol'] = $d['rol'];
        header("Location: /contable/index.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema Contable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center" style="height:100vh;">
        <div class="col-md-4">

            <div class="card shadow">
                <div class="card-body">

                    <h4 class="text-center mb-4">Sistema Contable</h4>

                    <?php if($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Usuario</label>
                            <input type="text" name="user" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Contraseña</label>
                            <input type="password" name="pass" class="form-control" required>
                        </div>

                        <button class="btn btn-dark w-100">Ingresar</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>