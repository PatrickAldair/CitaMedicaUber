<?php
session_start(); 
require 'db.php';

$mensajeExito = "";
if (isset($_SESSION['exito'])) {
    $mensajeExito = $_SESSION['exito'];
    unset($_SESSION['exito']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email']; 
    $pass  = $_POST['password'];

    $u = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $u->execute([$email]); 
    $u = $u->fetch(PDO::FETCH_ASSOC);

    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['usuario'] = $u;
        header("Location: {$u['tipo']}/dashboard.php"); 
        exit;
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #7eb6f7;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-card {
      background-color: white;
      border-radius: 20px;
      padding: 2rem;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }
  </style>
</head>
<body>

<div class="login-card">
  <h2 class="text-center mb-4 text-primary">Iniciar Sesión</h2>

  <?php if (!empty($mensajeExito)): ?>
    <div class='alert alert-success text-center'><?= $mensajeExito ?></div>
  <?php endif; ?>

  <?= isset($error) ? "<div class='alert alert-danger text-center'>$error</div>" : ""; ?>

  <form method="post">
    <div class="mb-3">
      <input name="email" type="email" class="form-control" placeholder="Correo electrónico" required>
    </div>
    <div class="mb-3">
      <input name="password" type="password" class="form-control" placeholder="Contraseña" required>
    </div>
    <div class="d-grid">
      <button type="submit" class="btn btn-primary">Ingresar</button>
    </div>
  </form>

  <p class="text-center mt-3">
    ¿No tienes cuenta?
    <a href="register.php" class="text-success">Regístrate</a>
  </p>
</div>

</body>
</html>


