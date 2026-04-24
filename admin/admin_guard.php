<?php
// ============================================================
//  admin/admin_guard.php — Admin Access Control
//  Include at the top of every admin page BEFORE any output.
// ============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Must be logged in
if (!isLoggedIn()) {
    setFlash('error', 'Veuillez vous connecter pour accéder à cette page.');
    redirect(SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Must be admin
if (!isAdmin()) {
    setFlash('error', 'Accès réservé aux administrateurs.');
    redirect(SITE_URL . '/dashboard.php');
}
