<?php
// /soporte/public/solicitar-cambio.php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/mail.php';      // 👈 aquí está send_mail()
require_once __DIR__ . '/../app/helpers.php';   // por si usas sanitize()

$ok = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim(strtolower($_POST['email'] ?? ''));

  // Si ingresan solo el usuario (sin @), asumimos gmail
  if ($email !== '' && strpos($email, '@') === false) {
    $email .= '@gmail.com';
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = 'Correo inválido.';
  } else {
    $pdo = db();

    // Buscar usuario
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE LOWER(email) = ? LIMIT 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) {
      $err = 'No encontramos una cuenta con ese correo.';
    } else {
      $userId = $u['id'];
      $name   = $u['name'] ?: 'usuario';

      // Generar contraseña temporal
      $tmp  = bin2hex(random_bytes(4)); // 8 hex chars
      $hash = password_hash($tmp, PASSWORD_DEFAULT);

      // Guardar y forzar cambio
      $upd = $pdo->prepare("
        UPDATE users
           SET password = ?, must_change_password = 1, temp_password_set_at = NOW()
         WHERE id = ?
      ");
      $upd->execute([$hash, $userId]);

      // Correo personalizado
      $subject = 'Contraseña temporal - Soporte DPR';
      $html = '
        <p>Hola, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ':</p>
        <p>Hemos generado una <strong>contraseña temporal</strong> para tu cuenta:</p>
        <p style="font-size:18px"><strong>' . $tmp . '</strong></p>
        <p>Úsala para iniciar sesión y el sistema te pedirá cambiarla inmediatamente.</p>
        <hr>
        <p>Si no solicitaste este cambio, por favor ignora este correo.</p>
      ';

      if (send_mail($email, $subject, $html)) {
        $ok = 'Se envió una contraseña temporal a su correo.';
      } else {
        $err = 'No se pudo enviar el correo. Intente de nuevo.';
      }
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recuperar acceso</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
  <div class="card p-4 shadow" style="max-width:520px;width:100%;">
    <h1 class="h5 text-center mb-3">Recuperar acceso</h1>

    <?php if ($ok): ?><div class="alert alert-info py-2"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
      <div class="mb-3">
        <label class="form-label">Correo</label>
        <input type="text" name="email" class="form-control" placeholder="usuario o correo" required>
     
      </div>
      <button class="btn btn-primary w-100">Enviar contraseña temporal</button>
    </form>

    <div class="mt-3 text-center">
      <a href="/soporte/public/login.php">Volver al inicio de sesión</a>
    </div>
  </div>
</body>
</html>
