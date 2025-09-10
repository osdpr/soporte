<?php
// app/auth.php
require_once __DIR__ . '/db.php';   // 👈 importa la conexión a BD

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function attempt_login(string $login, string $password): array {
  $pdo = db();   // 👈 conexión (ya no será null)

  $login = trim($login);

  // 1) Normalizamos: si no tiene @, lo tratamos como usuario
  if (strpos($login, '@') === false) {
    // Usuario → convertir a email Gmail
    $email = strtolower($login) . '@gmail.com';
  } else {
    $email = strtolower($login);
  }

  // 2) Solo aceptar gmail.com (puedes quitar esto si quieres varios dominios)
  $domain = substr(strrchr($email, '@'), 1);
  if ($domain !== 'gmail.com') {
    return [
      'ok' => false,
      'msg' => 'Solo correos @gmail.com o ingresa tu usuario sin @.'
    ];
  }

  // 3) Buscar usuario en BD
  $stmt = $pdo->prepare('
    SELECT id, email, name, role, password, must_change_password, temp_password_set_at
    FROM users
    WHERE email = ?
    LIMIT 1
  ');
  $stmt->execute([$email]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);

  // 4) Validar credenciales
  if (!$u || !password_verify($password, $u['password'])) {
    return ['ok' => false, 'msg' => 'Usuario o contraseña inválidos'];
  }

  // 5) Expiración de contraseña temporal (24h)
  if ((int)$u['must_change_password'] === 1 && !empty($u['temp_password_set_at'])) {
    $ts = strtotime($u['temp_password_set_at']);
    if ($ts !== false && $ts < time() - 86400) {
      return [
        'ok' => false,
        'msg' => 'Tu contraseña temporal expiró. Solicita una nueva.'
      ];
    }
  }

  // 6) Login correcto → guardar en sesión
  $_SESSION['user'] = [
    'id'    => $u['id'],
    'email' => $u['email'],
    'name'  => $u['name'],
    'role'  => $u['role'],
  ];

  return ['ok' => true, 'role' => $u['role']];
}

/* ✅ Funciones de portero */
function require_login(): void {
  if (empty($_SESSION['user'])) {
    header('Location: /soporte/public/login.php');
    exit;
  }
}

function require_admin(): void {
  require_login();
  if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Acceso restringido.';
    exit;
  }
}

function logout(): void {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params['path'], $params['domain'],
      $params['secure'], $params['httponly']
    );
  }
  session_destroy();
}
