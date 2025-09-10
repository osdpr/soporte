<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/notify.php';
require_once __DIR__ . '/../../app/helpers.php';

require_login();
header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$userId = (int)$_SESSION['user']['id'];
$list = notifications_recent($pdo, $userId, 10);

echo json_encode(['items' => $list]);
