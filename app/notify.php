<?php
// /soporte/app/notify.php
// Nota: Este archivo NO debe crear $pdo. Recibe un PDO por parámetro desde bootstrap.
// require_once __DIR__ . '/db.php'; // <- QUITADO para evitar dobles conexiones y nulls.

/**
 * Crea una notificación para un usuario.
 *
 * @return bool true si se insertó; false si falló.
 */
function notify_user(PDO $pdo, int $userId, string $type, ?int $ticketId, string $message): bool {
  $sql = "
    INSERT INTO notifications (user_id, ticket_id, type, message, is_read, created_at)
    VALUES (:user_id, :ticket_id, :type, :message, 0, NOW())
  ";
  $stmt = $pdo->prepare($sql);
  return $stmt->execute([
    ':user_id'  => $userId,
    ':ticket_id'=> $ticketId,   // puede ser null
    ':type'     => $type,
    ':message'  => $message,
  ]);
}

/**
 * Devuelve el número de notificaciones no leídas de un usuario.
 */
function notifications_unread_count(PDO $pdo, int $userId): int {
  $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
  $stmt->execute();
  return (int) $stmt->fetchColumn();
}

/**
 * Devuelve las últimas notificaciones (por defecto 10).
 */
function notifications_recent(PDO $pdo, int $userId, int $limit = 10): array {
  $sql = "
    SELECT id, ticket_id, type, message, is_read, created_at
    FROM notifications
    WHERE user_id = :uid
    ORDER BY created_at DESC
    LIMIT :lim
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
  $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Marca como leídas todas o una notificación específica del usuario.
 *
 * @return int Número de filas afectadas.
 */
function notifications_mark_read(PDO $pdo, int $userId, ?int $notifId = null): int {
  if ($notifId !== null) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $notifId, PDO::PARAM_INT);
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
  } else {
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
  }
  $stmt->execute();
  return $stmt->rowCount();
}
