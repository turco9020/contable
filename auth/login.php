<?php
// Si la sesión no arrancó, la iniciamos para evitar fallos de cabecera
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config/database.php';

$error = '';

if ($_POST) {
    // Forzamos el usuario a mayúsculas en el backend por consistencia
    $u = strtoupper(trim($_POST['user']));
    $p = $_POST['pass'];

    // Usamos sentencias preparadas para blindar el login contra Inyección SQL
    $stmt = $conn->prepare("SELECT u.*, r.nombre as rol 
                            FROM usuarios u 
                            JOIN roles r ON u.rol_id = r.id 
                            WHERE UPPER(u.usuario) = ?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $r = $stmt->get_result();
    $d = $r->fetch_assoc();

    if ($d && password_verify($p, $d['password'])) {
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Etiqueta imprescindible para adaptación Mobile / Responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Contable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container min-vh-100 d-flex justify-content-center align-items-center py-4">
    <div class="row justify-content-center w-100">
        <!-- Adaptación a distintos tamaños de pantalla -->
        <div class="col-12 col-sm-8 col-md-5 col-lg-4">
            <div class="card shadow border-0 rounded-3">
                <div class="card-body p-4">
                    
                    <!-- CONTENEDOR DEL LOGO -->
                    <div class="text-center mb-4">
                        <img src="/contable/assets/img/logo.png" alt="Logo" class="img-fluid mb-3" style="max-height: 80px; width: auto; object-fit: contain;">
                        <h5 class="fw-bold text-dark m-0">Sistema Contable</h5>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger py-2 text-center" style="font-size: 0.9rem;"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary">Usuario</label>
                            <input type="text" name="user" id="user" class="form-control form-control-lg fs-6" placeholder="EJ: JPEREZ" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary">Contraseña</label>
                            <input type="password" name="pass" class="form-control form-control-lg fs-6" placeholder="••••••••" required>
                        </div>

                        <button class="btn btn-dark w-100 py-2 fw-semibold mt-3 fs-6">Ingresar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Mantenemos la regla de negocio de pasar el input de texto a mayúsculas
    $(document).on('input', '#user', function(){
        this.value = this.value.toUpperCase();
    });
</script>
</body>
</html>