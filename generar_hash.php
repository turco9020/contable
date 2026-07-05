<?php

if($_POST){
    $pass = $_POST['password'];
    $hash = password_hash($pass, PASSWORD_DEFAULT);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Generador de Hash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="col-md-6 mx-auto">

        <div class="card shadow">
            <div class="card-body">

                <h4 class="mb-3">Generador de Hash</h4>

                <form method="POST">
                    <input type="text" name="password" class="form-control mb-3" placeholder="Contraseña" required>
                    <button class="btn btn-dark w-100">Generar</button>
                </form>

                <?php if(isset($hash)): ?>
                    <hr>
                    <label>Hash generado:</label>
                    <textarea class="form-control"><?= $hash ?></textarea>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

</body>
</html>