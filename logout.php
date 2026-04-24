<?php
// ============================================================
//  logout.php — Session Destruction
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logoutUser();
setFlash('success', 'Vous avez été déconnecté(e). À bientôt !');
redirect(SITE_URL . '/login.php');
