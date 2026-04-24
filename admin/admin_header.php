<?php
// ============================================================
//  admin/admin_header.php — Admin Layout: Head + Sidebar
//  Variables expected: $pageTitle, $activePage
// ============================================================

$unread      = countUnreadNotifications();
$currentUser = currentUser();
$activePage  = $activePage ?? '';

// Quick global stats for sidebar
$db          = getDB();
$totalUsers  = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalPosts  = (int)$db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
$totalEvents = (int)$db->query('SELECT COUNT(*) FROM events')->fetchColumn();
$pendingNotifs = (int)$db->query('SELECT COUNT(*) FROM notifications WHERE is_read = 0')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — <?= h($pageTitle ?? 'Panel') ?> · <?= SITE_NAME ?></title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Main styles -->
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">

<!-- ── Top Admin Navbar ────────────────────────────────────── -->
<nav class="admin-topbar">
  <div class="d-flex align-items-center gap-3">
    <!-- Sidebar toggle (mobile) -->
    <button class="btn btn-sm btn-icon" id="sidebarToggle">
      <i class="bi bi-list fs-5"></i>
    </button>
    <div class="admin-brand">
      <i class="bi bi-scissors me-2"></i><?= SITE_NAME ?>
      <span class="admin-badge">ADMIN</span>
    </div>
  </div>

  <div class="d-flex align-items-center gap-3">
    <!-- Notifications -->
    <a href="<?= SITE_URL ?>/notifications.php" class="position-relative text-muted">
      <i class="bi bi-bell fs-5"></i>
      <?php if ($unread > 0): ?>
        <span class="badge-notif"><?= $unread > 9 ? '9+' : $unread ?></span>
      <?php endif; ?>
    </a>
    <!-- Visit site -->
    <a href="<?= SITE_URL ?>/index.php" target="_blank"
       class="btn btn-sm btn-outline-secondary rounded-pill d-none d-md-flex align-items-center gap-1">
      <i class="bi bi-box-arrow-up-right"></i> Voir le site
    </a>
    <!-- User -->
    <div class="dropdown">
      <button class="btn d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown">
        <img src="<?= avatarUrl($currentUser) ?>" class="nav-avatar" alt="">
        <span class="d-none d-md-inline fw-semibold small"><?= h($currentUser['username']) ?></span>
        <i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end dropdown-fashion">
        <li>
          <a class="dropdown-item" href="<?= SITE_URL ?>/profile.php?id=<?= $currentUser['id'] ?>">
            <i class="bi bi-person me-2"></i>Mon profil
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ── Sidebar ─────────────────────────────────────────────── -->
<div class="admin-wrapper">
<aside class="admin-sidebar" id="adminSidebar">

  <!-- Profile mini -->
  <div class="sidebar-profile">
    <img src="<?= avatarUrl($currentUser) ?>" alt="" class="sidebar-avatar">
    <div>
      <div class="fw-semibold small text-white"><?= h($currentUser['full_name']) ?></div>
      <div class="sidebar-role">Administrateur</div>
    </div>
  </div>

  <nav class="sidebar-nav">

    <div class="sidebar-section-title">Navigation</div>

    <a href="<?= SITE_URL ?>/admin/index.php"
       class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="sidebar-section-title mt-3">Contenu</div>

    <a href="<?= SITE_URL ?>/admin/users.php"
       class="sidebar-link <?= $activePage === 'users' ? 'active' : '' ?>">
      <i class="bi bi-people"></i> Utilisateurs
      <span class="sidebar-count"><?= $totalUsers ?></span>
    </a>

    <a href="<?= SITE_URL ?>/admin/posts.php"
       class="sidebar-link <?= $activePage === 'posts' ? 'active' : '' ?>">
      <i class="bi bi-images"></i> Publications
      <span class="sidebar-count"><?= $totalPosts ?></span>
    </a>

    <a href="<?= SITE_URL ?>/admin/events.php"
       class="sidebar-link <?= $activePage === 'events' ? 'active' : '' ?>">
      <i class="bi bi-calendar-event"></i> Événements
      <span class="sidebar-count"><?= $totalEvents ?></span>
    </a>

    <div class="sidebar-section-title mt-3">Outils</div>

    <a href="<?= SITE_URL ?>/admin/create_event.php"
       class="sidebar-link <?= $activePage === 'create_event' ? 'active' : '' ?>">
      <i class="bi bi-plus-circle"></i> Créer un événement
    </a>

    <a href="<?= SITE_URL ?>/admin/stats.php"
       class="sidebar-link <?= $activePage === 'stats' ? 'active' : '' ?>">
      <i class="bi bi-bar-chart-line"></i> Statistiques
    </a>

    <div class="sidebar-section-title mt-3">Général</div>

    <a href="<?= SITE_URL ?>/index.php" class="sidebar-link">
      <i class="bi bi-house"></i> Voir le site
    </a>
    <a href="<?= SITE_URL ?>/logout.php" class="sidebar-link text-danger-soft">
      <i class="bi bi-box-arrow-right"></i> Déconnexion
    </a>

  </nav>
</aside>

<!-- ── Main content ────────────────────────────────────────── -->
<main class="admin-main">
  <!-- Breadcrumb -->
  <div class="admin-breadcrumb">
    <a href="<?= SITE_URL ?>/admin/index.php">
      <i class="bi bi-house-fill me-1"></i>Admin
    </a>
    <?php if (isset($breadcrumb)): ?>
      <span class="mx-2 text-muted">/</span>
      <span><?= h($breadcrumb) ?></span>
    <?php endif; ?>
  </div>

  <!-- Flash messages -->
  <?php renderFlash(); ?>
