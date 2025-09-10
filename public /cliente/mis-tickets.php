<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php'; // 👈 añade sanitize()

require_login();
$pdo = db();

// Si usas el portero de contraseñas temporales:
require_once __DIR__ . '/../../app/require_password_change.php';
require_password_change_if_needed($pdo);

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
  SELECT id, title, description, status, created_at
  FROM tickets
  WHERE user_id = ?
  ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mis tickets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-body-tertiary">
  <div class="container-fluid">
    <span class="navbar-brand">Mis tickets</span>
    <div class="d-flex gap-2">
      <a class="btn btn-primary btn-sm" href="nuevo.php">Nuevo</a>
      <a class="btn btn-outline-secondary btn-sm" href="/soporte/public/logout.php">Salir</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="table-responsive shadow-sm">
    <table class="table table-bordered table-sm bg-white mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Título</th>
          <th>Descripción</th>
          <th>Estado</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($tickets): ?>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td><?= (int)$t['id'] ?></td>
            <td>
              <a class="text-decoration-none"
                 href="ticket.php?id=<?= (int)$t['id'] ?>">
                <?= sanitize($t['title']) ?>
              </a>
            </td>
            <td><?= sanitize($t['description']) ?></td>
            <td><?= sanitize($t['status']) ?></td>
            <td><?= sanitize($t['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center text-muted">Aún no tienes tickets.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
