<?php
// ============================================================
//  includes/header.php — Global HTML Header & Navbar
//  Variables expected from the calling page:
//    $pageTitle (string)  — <title> suffix
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

$unread      = isLoggedIn() ? countUnreadNotifications() : 0;
$currentUser = currentUser();
$activePage  = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= h($pageTitle ?? 'Accueil') ?> — <?= SITE_NAME ?></title>

  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Custom styles -->
  <link rel="preload" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>" as="style">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>

<!-- ── Top Navbar ── -->
<nav class="navbar navbar-expand-lg navbar-fashion sticky-top">
  <div class="container">

    <!-- Brand -->
    <a class="navbar-brand" href="<?= SITE_URL ?>/index.php">
      <i class="bi bi-scissors me-1"></i><?= SITE_NAME ?>
    </a>

    <div class="navbar-mobile-actions d-lg-none">
      <?php if (isLoggedIn() && $currentUser): ?>
        <a class="nav-link position-relative navbar-mobile-bell" href="<?= SITE_URL ?>/notifications.php" aria-label="Notifications">
          <i class="bi bi-bell fs-5"></i>
          <?php if ($unread > 0): ?>
            <span class="badge-notif"><?= $unread > 9 ? '9+' : $unread ?></span>
          <?php endif; ?>
        </a>
      <?php endif; ?>

      <!-- Mobile toggle -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Ouvrir le menu">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="mainNav">

      <!-- Left links -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= $activePage === 'feed' ? 'active' : '' ?>"
             href="<?= SITE_URL ?>/index.php">
            <i class="bi bi-grid-3x3-gap me-1"></i>Feed
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $activePage === 'events' ? 'active' : '' ?>"
             href="<?= SITE_URL ?>/events.php">
            <i class="bi bi-calendar-event me-1"></i>Événements
          </a>
        </li>
        <?php if (isLoggedIn()): ?>
        <li class="nav-item">
          <a class="nav-link <?= $activePage === 'create' ? 'active' : '' ?>"
             href="<?= SITE_URL ?>/create_post.php">
            <i class="bi bi-plus-circle me-1"></i>Publier
          </a>
        </li>
        <?php endif; ?>
      </ul>

      <!-- Search bar -->
      <form class="d-flex me-3 search-form" action="<?= SITE_URL ?>/search.php" method="GET" role="search">
        <div class="input-group input-group-sm">
          <input class="form-control search-input" type="search" name="q"
                 placeholder="Rechercher créations, étudiants…"
                 value="<?= h($_GET['q'] ?? '') ?>">
          <button class="btn btn-search" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>

      <!-- Right user area -->
      <ul class="navbar-nav align-items-center">
        <?php if (isLoggedIn() && $currentUser): ?>

          <!-- Notifications bell -->
          <li class="nav-item me-2 d-none d-lg-block">
            <a class="nav-link position-relative" href="<?= SITE_URL ?>/notifications.php">
              <i class="bi bi-bell fs-5"></i>
              <?php if ($unread > 0): ?>
                <span class="badge-notif"><?= $unread > 9 ? '9+' : $unread ?></span>
              <?php endif; ?>
            </a>
          </li>

          <!-- User dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 user-menu"
               href="#" data-bs-toggle="dropdown">
              <img src="<?= avatarUrl($currentUser) ?>"
                   alt="Avatar" class="nav-avatar">
              <span class="d-none d-lg-inline"><?= h($currentUser['username']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-fashion">
              <li>
                <a class="dropdown-item" href="<?= SITE_URL ?>/profile.php?id=<?= $currentUser['id'] ?>">
                  <i class="bi bi-person me-2"></i>Mon profil
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= SITE_URL ?>/dashboard.php">
                  <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= SITE_URL ?>/edit_profile.php">
                  <i class="bi bi-gear me-2"></i>Paramètres
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php">
                  <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                </a>
              </li>
            </ul>
          </li>

        <?php else: ?>
          <li class="nav-item me-2">
            <a class="btn btn-outline-fashion btn-sm" href="<?= SITE_URL ?>/login.php">
              Connexion
            </a>
          </li>
          <li class="nav-item">
            <a class="btn btn-fashion btn-sm" href="<?= SITE_URL ?>/register.php">
              S'inscrire
            </a>
          </li>
        <?php endif; ?>
      </ul>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container -->
</nav>

<!-- ── Main content wrapper ── -->
<main class="main-content">
  <div class="container py-4">
    <?php renderFlash(); ?>
