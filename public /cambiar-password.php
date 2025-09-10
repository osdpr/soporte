<?php
// /soporte/public/cambiar-password.php

require_once __DIR__ . '/../app/auth.php';  // aquí se inicia la sesión si hace falta
require_once __DIR__ . '/../app/db.php';

require_login();
$pdo = db();

$ok = $err = '';
$nextUrl = (($_SESSION['user']['role'] ?? '') === 'admin')
  ? '/soporte/public/admin/tickets.php'
  : '/soporte/public/cliente/mis-tickets.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $actual  = $_POST['actual']  ?? '';
  $nueva   = $_POST['nueva']   ?? '';
  $confirm = $_POST['confirm'] ?? '';

  if ($nueva !== $confirm) {
    $err = 'La nueva contraseña y su confirmación no coinciden.';
  } elseif (strlen($nueva) < 8) {
    $err = 'La nueva contraseña debe tener al menos 8 caracteres.';
  } else {
    // Traer hash actual
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user']['id']]);
    $hashActual = $stmt->fetchColumn();

    if (!$hashActual || !password_verify($actual, $hashActual)) {
      $err = 'La contraseña actual es incorrecta.';
    } else {
      $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);
      $upd = $pdo->prepare('UPDATE users
                              SET password = ?, must_change_password = 0, temp_password_set_at = NULL
                            WHERE id = ?');
      $upd->execute([$nuevoHash, $_SESSION['user']['id']]);

      $ok = 'Contraseña actualizada correctamente. Te redirigiremos a tu panel…';
      // Si prefieres redirigir inmediato, descomenta:
      // header("Location: $nextUrl"); exit;
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cambiar contraseña</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f6f7fb; min-height:100vh; }
    .card { max-width:560px; width:100%; border:0; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.08); }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center">
  <div class="card p-4 p-md-5">
    <div class="d-flex align-items-center mb-2">
      <a href="<?php echo htmlspecialchars($nextUrl); ?>" class="text-decoration-none me-auto">← Volver</a>
    </div>

    <h1 class="h5 mb-1 text-center">Cambiar contraseña</h1>
    <p class="text-muted text-center mb-3">
      Has iniciado sesión con una contraseña temporal. Debes cambiarla.
    </p>

    <?php if ($ok): ?>
      <div class="alert alert-success py-2"><?php echo htmlspecialchars($ok); ?></div>
      <div class="d-flex gap-2 mb-3">
        <a class="btn btn-primary w-100" href="<?php echo htmlspecialchars($nextUrl); ?>">Ir al panel</a>
        <a class="btn btn-outline-secondary w-100" href="/soporte/public/logout.php">Cerrar sesión</a>
      </div>
      <script>
        setTimeout(function(){ window.location.href = <?php echo json_encode($nextUrl); ?>; }, 3000);
      </script>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert alert-danger py-2"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off" autocapitalize="none" spellcheck="false">
      <div class="mb-3">
        <label class="form-label">Contraseña actual</label>
        <input type="password" name="actual" class="form-control" required autocomplete="current-password">
      </div>

      <div class="mb-3">
        <label class="form-label">Nueva contraseña</label>
        <input type="password" name="nueva" class="form-control" required minlength="8" autocomplete="new-password" placeholder="Mínimo 8 caracteres">
      </div>

      <div class="mb-4">
        <label class="form-label">Confirmar nueva contraseña</label>
        <input type="password" name="confirm" class="form-control" required minlength="8" autocomplete="new-password">
      </div>

      <button class="btn btn-primary w-100">Guardar nueva contraseña</button>
    </form>
  </div>
</body>
</html>
