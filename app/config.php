<?php
// app/config.php
// Aquí configuramos la base de datos y ajustes generales

// Nombre del servidor de la base de datos (con XAMPP siempre es localhost)
define('DB_HOST', 'localhost');

// Nombre de la base de datos
define('DB_NAME', 'soporte_local');

// Usuario de MySQL
define('DB_USER', 'root');

// Contraseña del usuario MySQL (en XAMPP, root normalmente no tiene contraseña, por eso está vacío '')
define('DB_PASS', '');

// Dominio institucional (sirve para restringir logins)
define('INSTITUTION_DOMAIN', 'gmail.com');

// Nombre que se mostrará en la aplicación
define('APP_NAME', 'Mesa de Ayuda - DPR');

// Modo depuración (para mostrar errores mientras desarrollamos)
define('APP_DEBUG', true);

// 📧 Correo del administrador
define('ADMIN_EMAIL', 'sopportdpr@gmail.com');
define('ADMIN_NAME', 'Administrador Soporte DPR');

// 📤 Configuración SMTP (usando Gmail)
define('SMTP_HOST',    'smtp.gmail.com');
define('SMTP_PORT',    587);                 // 465 (SSL) o 587 (TLS)
define('SMTP_SECURE',  'tls');               // 'ssl' o 'tls'
define('SMTP_USER',    'sopportdpr@gmail.com');   // tu correo Gmail
define('SMTP_PASS',    'nbar vfsl fzfk krcn');   // usa una contraseña de aplicación de Gmail
define('SMTP_FROM',    'sopportdpr@gmail.com');   // remitente
define('SMTP_FROMNAME','Soporte DPR');
