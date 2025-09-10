<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/mail.php';

require_admin();
$pdo = db();

$id     = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;
if ($nuevoEstado === 'cerrado') {
  require_once __DIR__ . '/../../app/notify.php';
  $stmt = $pdo->prepare("SELECT user_id, title FROM tickets WHERE id=?");
  $stmt->execute([$ticketId]);
  if ($tk = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $msg = 'Tu ticket fue cerrado: ' . ($tk['title'] ?? ('#'.$ticketId));
    notify_user($pdo, (int)$tk['user_id'], 'ticket_closed', $ticketId, $msg);
  }
}

if ($nuevoEstado === 'abierto') {
  require_once __DIR__ . '/../../app/notify.php';
  $stmt = $pdo->prepare("SELECT user_id, title FROM tickets WHERE id=?");
  $stmt->execute([$ticketId]);
  if ($tk = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $msg = 'Tu ticket fue reabierto: ' . ($tk['title'] ?? ('#'.$ticketId));
    notify_user($pdo, (int)$tk['user_id'], 'ticket_reopened', $ticketId, $msg);
  }
}


if ($id && in_array($status, ['abierto','cerrado'])) {
  // Actualizar estado del ticket
  $stmt = $pdo->prepare("UPDATE tickets SET status=? WHERE id=?");
  $stmt->execute([$status, $id]);

  // Obtener info del ticket y su dueño
  $q = $pdo->prepare("
    SELECT t.title, u.name, u.email
    FROM tickets t
    JOIN users u ON u.id = t.user_id
    WHERE t.id = ?
    LIMIT 1
  ");
  $q->execute([$id]);
  $ticket = $q->fetch(PDO::FETCH_ASSOC);

  if ($ticket) {
    // Armar notificación
    $subject = "Tu ticket #{$id} ha sido " . ($status === 'cerrado' ? 'cerrado' : 'reabierto');
    $body = "
      <p><strong>Ticket:</strong> " . htmlspecialchars($ticket['title']) . "</p>
      <p><strong>Nuevo estado:</strong> " . ucfirst($status) . "</p>
    ";
    // Enviar correo al cliente
    send_mail($ticket['email'], $ticket['name'], $subject, $body);
  }
}

header("Location: tickets.php");
exit;
