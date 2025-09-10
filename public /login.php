<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php'; // solo esto

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  $res = attempt_login($email, $pass);
  if ($res['ok']) {
    if ($res['role'] === 'admin') {
     header('Location: /soporte/public/cliente/mis-tickets.php');
    } else {
      header('Location: /soporte/public/cliente/dashboard.php');
    }
    exit;
  } else {
    $error = $res['msg'] ?? 'Credenciales inválidas';
  }
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sopporte DPR - Iniciar sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{
      min-height:100vh;
      background:#f3f4f6 url('/soporte/public/assets/img/bg-parque.jpg') center/cover no-repeat fixed;
    }
    .bg-overlay{
      position:fixed; inset:0;
      background:rgba(255,255,255,.55);
      backdrop-filter:blur(2px);
      -webkit-backdrop-filter:blur(2px);
    }
    .login-card{
      max-width:520px; width:100%;
      border:0; border-radius:16px;
      box-shadow:0 10px 30px rgba(0,0,0,.10);
    }
    .brand-logo{
      height:56px; object-fit:contain;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center">
  <div class="bg-overlay"></div>

  <div class="container position-relative py-5 d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card login-card p-4 p-md-5 bg-white">
      <!-- LOGO ARRIBA DEL CUADRO -->
      <div class="text-center mb-3">
        <img src="/soporte/public/assets/img/logo-dpr.png" alt="Logo institución" class="brand-logo">
      </div>

      <!-- TÍTULO Y SUBTÍTULO -->
      <h1 class="h4 text-center mb-1">Soporte DPR</h1>
      <p class="text-center text-muted mb-4">Bienvenido ingresa sesión</p>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?php echo sanitize($error); ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off" autocapitalize="none" spellcheck="false">
        <!-- campos "trampa" para pelear con el autofill del navegador -->
        <input type="text" style="display:none" autocomplete="username">
        <input type="password" style="display:none" autocomplete="new-password">

      <div class="mb-3">
  <label class="form-label">Usuario</label>
  <!-- el backend sigue recibiendo 'email' -->
 <input
  type="text"
  name="email"
  class="form-control"
  required
  placeholder="Usuario (sin @) o correo @gmail.com"
  autocomplete="username"
>

</div>


        <div class="mb-2">
          <label class="form-label">Contraseña</label>
          <input
            type="password"
            name="password"
            class="form-control"
            required
            placeholder="Ingrese Contraseña<?php ?>"
            autocomplete="current-password"
            value=""
          >
        </div>
        <!-- ENLACE SOLICITAR CAMBIO DE CONTRASEÑA -->
        <div class="mb-4">
          <small><a href="/soporte/public/solicitar-cambio.php">Solicitar cambio de contraseña</a></small>
        </div>

        <!-- BOTÓN -->
        <button type="submit" class="btn btn-primary w-100 btn-lg">Iniciar sesión</button>
      </form>

        <!-- ENLACE SOLICITAR CAMBIO DE CONTRASEÑA -->
        <div class="mb-4">
          <small><a href="
