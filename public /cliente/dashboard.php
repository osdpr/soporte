<?php
// /soporte/public/cliente/dashboard.php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php'; // para sanitize()
require_login();

$pdo = db();

// Portero de contraseñas temporales (si lo estás usando)
if (file_exists(__DIR__ . '/../../app/require_password_change.php')) {
  require_once __DIR__ . '/../../app/require_password_change.php';
  require_password_change_if_needed($pdo);
}

$user = $_SESSION['user'];

// === CARGA nombre y avatar_path del usuario ===
$stmtU = $pdo->prepare("SELECT name, email, avatar_path FROM users WHERE id=? LIMIT 1");
$stmtU->execute([$user['id']]);
$rowU   = $stmtU->fetch(PDO::FETCH_ASSOC) ?: [];
$name   = ($rowU['name'] ?? '') ?: explode('@', ($rowU['email'] ?? $user['email']))[0];
$avatar = $rowU['avatar_path'] ?? '';

// Contadores rápidos
$openCount = 0; $closedCount = 0;
try {
  $stmt = $pdo->prepare("SELECT status, COUNT(*) c FROM tickets WHERE user_id = ? GROUP BY status");
  $stmt->execute([$user['id']]);
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    if ($r['status'] === 'abierto') $openCount = (int)$r['c'];
    if ($r['status'] === 'cerrado') $closedCount = (int)$r['c'];
  }
} catch (Throwable $e) {
  // silencioso; si no hay tabla o columnas, simplemente no mostramos contadores
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel del cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body{background:#f6f8fb;}
    .navbar{backdrop-filter:saturate(120%) blur(6px)}
    .brand-logo{height:40px;object-fit:contain}

    /* === Estilos de avatar (añadidos) === */
    .avatar-wrap{ width:122px;height:122px; }
    .avatar-img{
      width:122px;height:122px;object-fit:cover;border-radius:50%;
      box-shadow:0 8px 24px rgba(37,99,235,.25)
    }
    .avatar{
      width:122px;height:122px;border-radius:50%;
      background:linear-gradient(135deg,#22d3ee,#2563eb);
      display:grid;place-items:center;color:#fff;font-size:44px;
      box-shadow:0 8px 24px rgba(37,99,235,.25)
    }
    .avatar-uploader label{
      width:34px;height:34px;display:grid;place-items:center;
    }

    .tile{
      border:0;border-radius:16px;padding:22px; height:100%;
      background:linear-gradient(180deg,#0ea5e9 0%, #0b66c3 70%, #09386b 100%);
      color:#fff; position:relative; overflow:hidden;
      box-shadow:0 10px 28px rgba(2,132,199,.22);
      transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }
    .tile:hover{ transform: translateY(-3px); filter:brightness(1.05);
      box-shadow:0 14px 38px rgba(2,132,199,.32); }
    .tile .bi{ font-size:28px }
    .tile small{ opacity:.9 }
    .stat{
      border-radius:12px; background:#fff; color:#0b66c3;
      box-shadow:0 6px 18px rgba(2,132,199,.12);
    }
    .kicker{ letter-spacing:.06em; text-transform:uppercase; font-weight:700; font-size:.75rem; opacity:.85 }
    .link-plain{ text-decoration:none; color:inherit }
  </style>
</head>
<body>

<nav class="navbar navbar-light bg-white sticky-top shadow-sm">
  <div class="container">
    <div class="d-flex align-items-center gap-2">
      <img class="brand-logo" src="/soporte/public/assets/img/logo-dpr.png" alt="Logo">
    </div>
    <div class="d-flex align-items-center gap-2">
     <!-- ============ NOTIFICACIONES (DROPDOWN + BADGE) ============ -->
<div class="dropdown">
  <button class="btn btn-light position-relative" id="notifBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
    <i class="bi bi-bell"></i>
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notifBadge">0</span>
  </button>
  <div class="dropdown-menu dropdown-menu-end p-0 shadow" style="width:360px; max-height:420px; overflow:auto;" id="notifMenu">
    <div class="list-group list-group-flush small" id="notifList">
      <div class="p-3 text-muted" id="notifEmpty">Sin notificaciones</div>
    </div>
    <div class="border-top p-2 text-center">
      <button class="btn btn-link btn-sm" id="markAllBtn">Marcar todas como leídas</button>
    </div>
  </div>
</div>
<!-- =========================================================== -->

      <a class="btn btn-outline-secondary" href="/soporte/public/logout.php">
        <i class="bi bi-box-arrow-right me-1"></i> Salir
      </a>
    </div>
  </div>
</nav>

<main class="container py-4 py-md-5">

  <!-- Cabecera con avatar y nombre -->
  <div class="row g-4 align-items-center mb-5">
    <div class="col-auto">
      <!-- === Bloque de avatar reemplazado (imagen + botón cámara) === -->
      <div class="avatar-wrap position-relative">
        <?php if ($avatar): ?>
          <img src="<?= sanitize($avatar) ?>" class="avatar-img" alt="Foto de perfil">
        <?php else: ?>
          <div class="avatar"><i class="bi bi-person-fill"></i></div>
        <?php endif; ?>

        <form class="avatar-uploader" action="/soporte/public/cliente/avatar-upload.php" method="post" enctype="multipart/form-data">
          <input type="file" name="avatar" id="avatarInput" accept="image/png,image/jpeg,image/webp" class="d-none" onchange="this.form.submit()">
          <label for="avatarInput"
                 class="btn btn-sm btn-light rounded-circle shadow position-absolute bottom-0 end-0"
                 title="Cambiar foto">
            <i class="bi bi-camera-fill"></i>
          </label>
        </form>
      </div>
    </div>

    <div class="col">
      <h1 class="h4 mb-1"><?= sanitize($name) ?></h1>
      <div class="text-muted">Bienvenido(a) a tu panel</div>
    </div>

    <div class="col-12 col-md-auto d-flex gap-3">
      <div class="px-3 py-2 stat text-center">
        <div class="kicker">Abiertos</div>
        <div class="fw-bold fs-5"><?= $openCount ?></div>
      </div>
      <div class="px-3 py-2 stat text-center">
        <div class="kicker">Cerrados</div>
        <div class="fw-bold fs-5"><?= $closedCount ?></div>
      </div>
    </div>
  </div>

  <!-- Grid de accesos -->
  <div class="row g-4">
    <div class="col-12 col-sm-6 col-lg-4">
      <a class="link-plain" href="/soporte/public/cliente/mis-tickets.php">
        <div class="tile">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="kicker">Soporte</span>
            <i class="bi bi-life-preserver"></i>
          </div>
          <h2 class="h5 mb-1">Tickets</h2>
          <small>Crear, ver y comentar tus tickets.</small>
        </div>
      </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-4">
      <a class="link-plain" href="/soporte/public/cliente/documentos.php">
        <div class="tile">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="kicker">Documentos</span>
            <i class="bi bi-folder2-open"></i>
          </div>
          <h2 class="h5 mb-1">Archivos vinculados</h2>
          <small>Inventario/archivos relacionados contigo.</small>
        </div>
      </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-4">
      <a class="link-plain" href="/soporte/public/cliente/perfil.php">
        <div class="tile">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="kicker">Cuenta</span>
            <i class="bi bi-person-gear"></i>
          </div>
          <h2 class="h5 mb-1">Información de usuario</h2>
          <small>Datos personales y contraseña.</small>
        </div>
      </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-4">
      <a class="link-plain" href="/soporte/public/cliente/mis-tickets.php?solo=cerrados">
        <div class="tile">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="kicker">Historial</span>
            <i class="bi bi-clock-history"></i>
          </div>
          <h2 class="h5 mb-1">Tickets cerrados</h2>
          <small>Consulta soluciones pasadas.</small>
        </div>
      </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-4">
      <a class="link-plain" href="/soporte/public/cliente/propuestas.php">
        <div class="tile">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="kicker">Mejoras</span>
            <i class="bi bi-lightbulb"></i>
          </div>
          <h2 class="h5 mb-1">Propuestas</h2>
          <small>Envíanos ideas para mejorar.</small>
        </div>
      </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-4">
      <a class="link-plain" href="/soporte/public/cliente/ayuda.php">
        <div class="tile">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="kicker">Ayuda</span>
            <i class="bi bi-question-circle"></i>
          </div>
          <h2 class="h5 mb-1">FAQ / Guías</h2>
          <small>Cómo usar la mesa de ayuda.</small>
        </div>
      </a>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const BADGE   = document.getElementById('notifBadge');
  const LIST    = document.getElementById('notifList');
  const EMPTY   = document.getElementById('notifEmpty');
  const BTNALL  = document.getElementById('markAllBtn');
  const MENU    = document.getElementById('notifMenu');
  const BTN     = document.getElementById('notifBtn');

  // Endpoints (ajústalos si los guardaste con otro nombre)
  const URL_UNREAD   = '/soporte/public/cliente/notifs-unread.php';
  const URL_MARKREAD = '/soporte/public/cliente/notifs-mark-read.php';

  let loadedOnce = false;
  let pollTimer  = null;

  function setBadge(n) {
    if (!BADGE) return;
    if (n > 0) {
      BADGE.textContent = n;
      BADGE.classList.remove('d-none');
    } else {
      BADGE.classList.add('d-none');
      BADGE.textContent = '0';
    }
  }

  function renderList(items) {
    LIST.innerHTML = '';
    if (!items || !items.length) {
      EMPTY.classList.remove('d-none');
      return;
    }
    EMPTY.classList.add('d-none');

    for (const it of items) {
      const a = document.createElement('a');
      a.href = it.url || '#';
      a.className = 'list-group-item list-group-item-action d-flex gap-2 align-items-start';
      a.dataset.id = it.id;
      a.innerHTML = `
        <div class="flex-grow-1">
          <div class="fw-semibold">${escapeHtml(it.title || 'Notificación')}</div>
          <div class="text-muted">${escapeHtml(it.body || '')}</div>
        </div>
        <small class="text-nowrap text-muted">${escapeHtml(it.when || '')}</small>
      `;

      // Al hacer click: marca leída y navega
      a.addEventListener('click', async (e) => {
        // Si la notificación no tiene URL, evita navegación
        if (!it.url) e.preventDefault();
        try {
          await markRead(it.id);
        } catch (err) {
          // silencioso
        }
      });

      LIST.appendChild(a);
    }
  }

  async function fetchJSON(url, opts = {}) {
    const r = await fetch(url, {credentials: 'same-origin', ...opts});
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
  }

  async function loadNotifs() {
    try {
      const data  = await fetchJSON(URL_UNREAD);
      const items = Array.isArray(data.items) ? data.items : [];
      renderList(items);
      setBadge(items.length);
    } catch (err) {
      // Si falla, no rompemos la UI
      console.error('Notifs load error:', err);
    }
  }

  async function markRead(id) {
    try {
      const body = new URLSearchParams();
      if (id) body.set('id', id); else body.set('all', '1');
      const res = await fetchJSON(URL_MARKREAD, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body
      });
      await loadNotifs();
      return res;
    } catch (err) {
      console.error('markRead error:', err);
    }
  }

  // Utilidad mínima para evitar XSS en contenido
  function escapeHtml(s) {
    return String(s ?? '')
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }

  // Carga diferida: la primera vez que se abre el dropdown
  MENU.addEventListener('show.bs.dropdown', async () => {
    if (!loadedOnce) {
      loadedOnce = true;
      await loadNotifs();
      // polling cada 30s
      pollTimer && clearInterval(pollTimer);
      pollTimer = setInterval(loadNotifs, 30000);
    }
  });

  // Botón "Marcar todas"
  BTNALL.addEventListener('click', async (e) => {
    e.preventDefault();
    await markRead(null); // all=1
  });

  // También cargamos una vez al entrar a la página (sin abrir el menú)
  loadNotifs();
})();
</script>

</body>
</html>
