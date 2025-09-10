<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/mail.php';

require_login();
$pdo = db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user']['id'];

// Traer ticket y asegurar propiedad
$stmt = $pdo->prepare("
  SELECT t.*, u.name, u.email
  FROM tickets t
  JOIN users u ON u.id = t.user_id
  WHERE t.id = ? AND t.user_id = ?
  LIMIT 1
");
$stmt->execute([$id, $userId]);
$ticket = $stmt->fetch();

if (!$ticket) {
  http_response_code(404);
  echo "Ticket no encontrado";
  exit;
}

// Insertar comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $comentario = trim($_POST['comentario'] ?? '');
  if ($comentario !== '') {
    $ins = $pdo->prepare("
      INSERT INTO ticket_comentarios (ticket_id, user_id, comentario)
      VALUES (?,?,?)
    ");
    $ins->execute([$id, $userId, $comentario]);

    // ---------- Notificación por correo al administrador ----------
    $subject = "Nuevo comentario del cliente en ticket #{$id}";
    $body = "
      <p><strong>Ticket:</strong> " . htmlspecialchars($ticket['title']) . "</p>
      <p><strong>Comentario:</strong><br>" . nl2br(htmlspecialchars($comentario)) . "</p>
      <p>Cliente: " . htmlspecialchars($_SESSION['user']['name']) . " (" . htmlspecialchars($_SESSION['user']['email']) . ")</p>
    ";
    // Usa las constantes definidas en app/config.php
    send_mail(ADMIN_EMAIL, ADMIN_NAME, $subject, $body);
    // --------------------------------------------------------------

    header('Location: ticket.php?id=' . $id);
    exit;
  }
}

// Traer comentarios
$cstmt = $pdo->prepare("
  SELECT c.*, u.name
  FROM ticket_comentarios c
  JOIN users u ON u.id = c.user_id
  WHERE c.ticket_id = ?
  ORDER BY c.created_at ASC
");
$cstmt->execute([$id]);
$comentarios = $cstmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ticket #<?php echo $ticket['id']; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-body-tertiary">
  <div class="container-fluid">
    <a class="btn btn-secondary btn-sm" href="mis-tickets.php">&larr; Volver</a>
    <a class="btn btn-outline-secondary btn-sm" href="/soporte/public/logout.php">Salir</a>
  </div>
</nav>

<div class="container py-4">
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title mb-2"><?php echo htmlspecialchars($ticket['title']); ?></h5>
      <p class="card-text"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
      <p class="small text-muted mb-0">
        Estado: <strong><?php echo $ticket['status']; ?></strong> |
        Fecha: <?php echo $ticket['created_at']; ?>
      </p>
    </div>
  </div>

  <h6 class="mb-3">Comentarios</h6>
  <?php if ($comentarios): ?>
    <?php foreach ($comentarios as $c): ?>
      <div class="border rounded p-2 mb-2 bg-white">
        <strong><?php echo htmlspecialchars($c['name']); ?>:</strong>
        <p class="mb-1"><?php echo nl2br(htmlspecialchars($c['comentario'])); ?></p>
        <small class="text-muted"><?php echo $c['created_at']; ?></small>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="text-muted mb-3">Aún no hay comentarios.</div>
  <?php endif; ?>

  <form method="post" class="card card-body">
    <textarea name="comentario" class="form-control mb-2" rows="3" placeholder="Escribe un comentario..." required></textarea>
    <button class="btn btn-primary btn-sm">Enviar</button>
  </form>
</div>
</body>
</html>
