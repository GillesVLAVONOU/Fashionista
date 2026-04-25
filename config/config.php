<?php
// ============================================================
//  config/config.php — Global Application Configuration
// ============================================================

// ── Site identity ──────────────────────────────────────────
define('SITE_NAME',    'Fashionista University');
define('SITE_TAGLINE', 'Exprimez votre créativité');
define('SITE_URL',     'https://fashionista.wuaze.com'); // no trailing slash
date_default_timezone_set('Africa/Lagos');

// ── Upload directories ─────────────────────────────────────
define('UPLOAD_ROOT',    __DIR__ . '/../uploads/');
define('UPLOAD_POSTS',   UPLOAD_ROOT . 'posts/');
define('UPLOAD_AVATARS', UPLOAD_ROOT . 'avatars/');

define('UPLOAD_URL_POSTS',   SITE_URL . '/uploads/posts/');
define('UPLOAD_URL_AVATARS', SITE_URL . '/uploads/avatars/');

// ── Upload limits ──────────────────────────────────────────
define('MAX_FILE_SIZE',  5 * 1024 * 1024); // 5 MB
define('ALLOWED_TYPES',  ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_EXT',    ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// ── Pagination ─────────────────────────────────────────────
define('POSTS_PER_PAGE', 12);

// ── Session ────────────────────────────────────────────────
define('SESSION_LIFETIME', 3600 * 24); // 24h

// ── Security ───────────────────────────────────────────────
define('BCRYPT_COST', 12);

// ── Include database ───────────────────────────────────────
// â”€â”€ Email / Password reset â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('MAIL_TRANSPORT', 'smtp'); // mail or smtp
define('MAIL_FROM_EMAIL', 'vmgilles853@gmail.com');
define('MAIL_FROM_NAME', SITE_NAME);

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // tls, ssl, none
define('SMTP_USERNAME', 'vmgilles853@gmail.com');
define('SMTP_PASSWORD', 'yoyc sbks weyn wqdd'); // Use app password for Gmail
define('SMTP_TIMEOUT', 15);

require_once __DIR__ . '/database.php';
