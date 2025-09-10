<?php
// /public/admin/ticket.php

require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';   // para sanitize()
require_once __DIR__ . '/../../app/db.php';        // db() -> PDO
require_once __DIR__ . '/../../app/mail.php';
require_once __DIR__ . '/../../app/notify.php';

require_admin();

// 1) Conexión
$pdo = db();

// 2) ID del ticket
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header("Location: tickets.php");
  exit;
}

// 3) Cargar ticket + dueño (owner)
$stmt = $pdo->prepare("
  SELECT t.*, u.id AS owner_id, u.name, u.email
  FROM tickets t
  JOIN users u ON u.id = t.user_id
  WHERE t.id = ?
  LIMIT 1
");
$stmt->execute([$id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ticket) {
  http_response_code(404);
  exit("Ticket no encontrado.");
}
$ownerId = (int)$ticket['owner_id'];

// 4) Si el admin envía comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $comentario = trim($_POST['comentario'] ?? '');

  if ($comentario !== '') {
    // 4.1 Guardar comentario del admin
    $stmt = $pdo->prepare("
      INSERT INTO ticket_comentarios (ticket_id, user_id, comentario, created_at)
      VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$id, $_SESSION['user']['id'], $comentario]);

    // 4.2 Notificación in-app al dueño del ticket
    // notify_user(PDO $pdo, int $userId, string $type, ?int $ticketId, string $message)
    $notifMsg = 'Nuevo comentario en tu ticket: ' . ($ticket['title'] ?? ('#'.$id));
    notify_user($pdo, $ownerId, 'ticket_commented', $id, $notifMsg);

    // 4.3 Correo al cliente
    $subject = "Nuevo comentario en tu ticket #{$id}";
    $body = "
      <p><strong>Ticket:</strong> " . htmlspecialchars($ticket['title']) . "</p>
      <p><strong>Comentario del administrador:</strong><br>" . nl2br(htmlspecialchars($comentario)) . "</p>
    ";
    send_mail($ticket['email'], $ticket['name'], $subject, $body);
  }

  // Evitar reenvío del formulario
  header("Location: ticket.php?id=" . $id);
  exit;
}

// 5) Obtener comentarios
$stmt = $pdo->prepare("
  SELECT c.*, u.name
  FROM ticket_comentarios c
  JOIN users u ON u.id = c.user_id
  WHERE c.ticket_id = ?
  ORDER BY c.created_at ASC
");
$stmt->execute([$id]);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detalle Ticket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="tickets.php" class="btn btn-secondary btn-sm mb-3">&larr; Volver</a>
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title"><?php echo sanitize($ticket['title']); ?></h5>
      <p class="card-text"><?php echo nl2br(sanitize($ticket['description'])); ?></p>
      <p class="small text-muted">
        Estado: <strong>
