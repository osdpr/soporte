<?php
// /soporte/public/admin/tickets.php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php'; // 👈 NECESARIO para sanitize()

require_admin();
$pdo = db();

// (Opcional) Portero de contraseñas temporales
$portero = __DIR__ . '/../../app/require_password_change.php';
if (file_exists($portero)) {
  require_once $portero;
  require_password_change_if_needed($pdo);
}

// Carga de tickets
$stmt = $pdo->query("
  SELECT t.id, t.title, t.description, t.status, t.created_at,
         u.name, u.email
  FROM tickets t
  JOIN users u ON u.id = t.user_id
  ORDER BY t.created_at DESC
");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tickets - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Panel Admin</a>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary btn-sm" href="/soporte/public/logout.php">Salir</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Lista de Tickets</h1>
    <a class="btn btn-primary btn-sm" href="/soporte/public/nuevo-ticket.php">Nuevo ticket</a>
  </div>

  <div class="table-responsive shadow-sm">
    <table class="table table-bordered table-sm bg-white mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Título</th>
          <th>Descripción</th>
          <th>Usuario</th>
          <th>Email</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($tickets): ?>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td><?= (int)$t['id']; ?></td>
            <td><?= sanitize($t['title']); ?></td>
            <td><?= sanitize($t['description']); ?></td>
            <td><?= sanitize($t['name']); ?></td>
            <td><?= sanitize($t['email']); ?></td>
            <td>
              <?php if ($t['status'] === 'abierto'): ?>
                <span class="badge bg-success">Abierto</span>
              <?php else: ?>
                <span class="badge bg-secondary">Cerrado</span>
              <?php endif; ?>
            </td>
            <td><?= sanitize($t['created_at']); ?></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-info"
                 href="ticket.php?id=<?= (int)$t['id']; ?>">Ver</a>

              <?php if ($t['status'] === 'abierto'): ?>
                <a class="btn btn-sm btn-outline-danger"
                   href="update-ticket.php?id=<?= (int)$t['id']; ?>&status=cerrado">Cerrar</a>
              <?php else: ?>
                <a class="btn btn-sm btn-outline-success"
                   href="update-ticket.php?id=<?= (int)$t['id']; ?>&status=abierto">Reabrir</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" class="text-center text-muted">No hay tickets aún.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
