<?php
require_once __DIR__ . '/../../app/auth.php';
require_admin();
?>
<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin - <?php echo sanitize(APP_NAME); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<nav class="navbar bg-body-tertiary"><div class="container-fluid">
  <span class="navbar-brand">Panel Admin</span>
  <a class="btn btn-outline-secondary btn-sm" href="/soporte/public/logout.php">Salir</a>
</div></nav>
<div class="container py-4">
  <h1 class="h5">Bienvenido, <?php echo sanitize($_SESSION['user']['name']); ?></h1>
  <p class="text-muted">Aquí gestionaremos los tickets (próximo paso).</p>
  <a class="btn btn-primary btn-sm" href="tickets.php">Ver tickets</a>

</div>
</body></html>
