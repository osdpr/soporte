<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/mail.php';

require_login();
$pdo = db();
$userId = $_SESSION['user']['id'];

$ok = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');

  if ($title === '' || $description === '') {
    $err = 'Completa título y descripción.';
  } else {
    // Insertar ticket
    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, title, description) VALUES (?,?,?)");
    $stmt->execute([$userId, $title, $description]);

    // ID del ticket recién creado
    $ticketId = $pdo->lastInsertId();

    // Armar y enviar correo al administrador
    $subject = "Nuevo ticket #{$ticketId} creado";
    $body = "
      <p><strong>Título:</strong> " . htmlspecialchars($title) . "</p>
      <p><strong>Descripción:</strong><br>" . nl2br(htmlspecialchars($description)) . "</p>
      <p>Creado por: " . htmlspecialchars($_SESSION['user']['name']) . " (" . htmlspecialchars($_SESSION['user']['email']) . ")</p>
    ";

    // Usa las constantes definidas en config.php
    send_mail(ADMIN_EMAIL, ADMIN_NAME, $subject, $body);

    $ok = 'Ticket creado con éxito.';
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nuevo ticket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="mis-tickets.php">Mis tickets</a>
    <a class="btn btn-outline-secondary btn-sm" href="/soporte/public/logout.php">Salir</a>
  </div>
</nav>

<div class="container py-4" style="max-width:720px;">
  <h1 class="h5 mb-3">Crear nuevo ticket</h1>

  <?php if ($ok): ?>
    <div class="alert alert-success"><?php echo $ok; ?></div>
  <?php endif; ?>

  <?php if ($err): ?>
    <div class="alert alert-danger"><?php echo sanitize($err); ?></div>
  <?php endif; ?>

  <form method="post" class="card p-3 shadow-sm" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">Título</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Descripción</label>
      <textarea name="description" rows="5" class="form-control" required></textarea>
    </div>
    <button class="btn btn-primary">Guardar</button>
  </form>
</div>
</body>
</html>
