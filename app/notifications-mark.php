<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/notify.php';

require_login();
header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$userId = (int)$_SESSION['user']['id'];

$notifId = isset($_POST['id']) ? (int)$_POST['id'] : null;
notifications_mark_read($pdo, $userId, $notifId);

echo json_encode(['ok' => true]);
