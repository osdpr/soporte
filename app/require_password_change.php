<?php
// /soporte/app/require_password_change.php
function require_password_change_if_needed(PDO $pdo) {
  if (!isset($_SESSION['user']['id'])) return;

  $stmt = $pdo->prepare('SELECT must_change_password FROM users WHERE id = ?');
  $stmt->execute([$_SESSION['user']['id']]);
  $flag = (int)$stmt->fetchColumn();

  if ($flag === 1) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (strpos($path, '/public/cambiar-password.php') === false) {
      header('Location: /soporte/public/cambiar-password.php');
      exit;
    }
  }
}
