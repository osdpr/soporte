<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_login();
$pdo = db();

$userId = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
  $file = $_FILES['avatar'];
  if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: /soporte/public/cliente/dashboard.php?avatar=err');
    exit;
  }

  // Validar tipo y tamaño
  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);

  if (!isset($allowed[$mime])) {
    header('Location: /soporte/public/cliente/dashboard.php?avatar=type');
    exit;
  }
  if ($file['size'] > 2 * 1024 * 1024) { // 2MB
    header('Location: /soporte/public/cliente/dashboard.php?avatar=size');
    exit;
  }

  // Carpeta destino (pública)
  $dir = __DIR__ . '/../uploads/avatars';
  if (!is_dir($dir)) mkdir($dir, 0775, true);

  $ext = $allowed[$mime];
  $name = 'u'.$userId.'_'.time().'.'.$ext;
  $pathFs = $dir . '/' . $name;
  $pathWeb = '/soporte/public/uploads/avatars/' . $name;

  // Opcional: eliminar el anterior
  $old = $pdo->prepare("SELECT avatar_path FROM users WHERE id=?");
  $old->execute([$userId]);
  $oldPath = $old->fetchColumn();
  if ($oldPath && str_starts_with($oldPath, '/soporte/public/uploads/avatars/')) {
    $oldFs = __DIR__ . '/..' . str_replace('/soporte/public', '', $oldPath);
    if (is_file($oldFs)) @unlink($oldFs);
  }

  // Guardar
  if (move_uploaded_file($file['tmp_name'], $pathFs)) {
    $upd = $pdo->prepare("UPDATE users SET avatar_path=? WHERE id=?");
    $upd->execute([$pathWeb, $userId]);
    // reflect en sesión (opcional)
    $_SESSION['user']['avatar_path'] = $pathWeb;
    header('Location: /soporte/public/cliente/dashboard.php?avatar=ok');
    exit;
  }

  header('Location: /soporte/public/cliente/dashboard.php?avatar=err');
  exit;
}

header('Location: /soporte/public/cliente/dashboard.php');
