<?php
// nuevo-ticket.php
require_once __DIR__ . '/../app/auth.php';   // sesión + helpers
require_once __DIR__ . '/../app/db.php';     // función db()

require_login();                              // exige usuario logueado (admin o cliente)
$pdo = db();                                  // << crea la conexión PDO

$ok = false;
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $userId = $_SESSION['user']['id'] ?? null;

    if (!$userId) {
        $err = 'Usuario no autenticado.';
    } elseif ($title === '' || $description === '') {
        $err = 'Completa título y descripción.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO tickets (user_id, title, description) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $title, $description]);
        $ok = true;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nuevo ticket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Tickets</a>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary btn-sm" href="/soporte/public/logout.php">Salir</a>
    </div>
  </div>
</nav>

<div class="container py-4" style="max-width:720px;">
  <h1 class="h5 mb-3">Crear nuevo ticket</h1>

  <?php if ($ok): ?>
    <div class="alert alert-success">✅ Ticket creado con éxito.</div>
  <?php elseif ($err): ?>
    <div class="alert alert-danger">❌ <?php echo sanitize($err); ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off" class="card p-3 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Título</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Descripción</label>
      <textarea name="description" rows="5" class="form-control" required></textarea>
    </div>
    <button class="btn btn-primary">Guardar ticket</button>
  </form>
</div>
</body>
</html>
