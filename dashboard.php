<?php
// ============================================================
//  dashboard.php — User Dashboard
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Post.php';
require_once __DIR__ . '/models/Event.php';
require_once __DIR__ . '/models/Notification.php';

requireLogin();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

$userId    = currentUserId();
$userModel = new User();
$user      = $userModel->findById($userId);

// Stats
$postCount    = $userModel->postCount($userId);
$likesTotal   = $userModel->totalLikesReceived($userId);
$eventCount   = $userModel->eventCount($userId);
$notifCount   = countUnreadNotifications();

// Recent posts
$postModel    = new Post();
$myPosts      = array_slice($postModel->getByUser($userId), 0, 6);

// Upcoming events
$eventModel   = new Event();
$upEvents     = $eventModel->getUpcoming(4);

// Recent notifications
$notifModel   = new Notification();
$notifications = $notifModel->getForUser($userId, 5);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page header -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
  <div>
    <h1 class="mb-0" style="font-size:1.7rem">
      Bonjour, <span style="color:var(--clr-primary)"><?= h($user['full_name']) ?></span> 👋
    </h1>
    <p class="text-muted mb-0 small">Voici un aperçu de votre activité sur Fashionista</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= SITE_URL ?>/create_post.php" class="btn btn-fashion btn-sm">
      <i class="bi bi-plus-circle me-1"></i>Nouvelle création
    </a>
    <a href="<?= SITE_URL ?>/profile.php?id=<?= $userId ?>" class="btn btn-outline-fashion btn-sm">
      <i class="bi bi-person me-1"></i>Mon profil
    </a>
  </div>
</div>

<!-- ── Stats row ──────────────────────────────────────────── -->
<div class="row g-3 mb-4">

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-pink">
        <i class="bi bi-images"></i>
      </div>
      <div>
        <div class="stat-value"><?= $postCount ?></div>
        <div class="stat-label">Créations publiées</div>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-purple">
        <i class="bi bi-heart-fill"></i>
      </div>
      <div>
        <div class="stat-value"><?= $likesTotal ?></div>
        <div class="stat-label">Likes reçus</div>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-blue">
        <i class="bi bi-calendar-check"></i>
      </div>
      <div>
        <div class="stat-value"><?= $eventCount ?></div>
        <div class="stat-label">Événements rejoints</div>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-gold">
        <i class="bi bi-bell-fill"></i>
      </div>
      <div>
        <div class="stat-value"><?= $notifCount ?></div>
        <div class="stat-label">Notifications non lues</div>
      </div>
    </div>
  </div>

</div><!-- /.stats -->

<div class="row g-4">

  <!-- ── My recent posts ──────────────────────────────────── -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0">
            <i class="bi bi-images text-danger me-2"></i>Mes créations récentes
          </h5>
          <a href="<?= SITE_URL ?>/profile.php?id=<?= $userId ?>"
             class="btn btn-sm btn-outline-secondary rounded-pill">Voir tout</a>
        </div>

        <?php if (empty($myPosts)): ?>
          <div class="empty-state py-4">
            <i class="bi bi-camera d-block"></i>
            <p class="mb-2">Vous n'avez pas encore publié de création.</p>
            <a href="<?= SITE_URL ?>/create_post.php" class="btn btn-fashion btn-sm">
              <i class="bi bi-plus me-1"></i>Publier maintenant
            </a>
          </div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($myPosts as $post): ?>
              <div class="col-6 col-md-4">
                <div class="position-relative rounded-3 overflow-hidden"
                     style="aspect-ratio:1/1; background:#f0f0f0;">
                  <img src="<?= postImageUrl($post) ?>"
                       alt="<?= h($post['title']) ?>"
                       style="width:100%;height:100%;object-fit:cover;"
                       loading="lazy">
                  <!-- Overlay -->
                  <div class="position-absolute inset-0 d-flex flex-column justify-content-end p-2"
                       style="background:linear-gradient(to top,rgba(0,0,0,.6),transparent)">
                    <div class="text-white" style="font-size:.78rem; font-weight:600">
                      <?= h(truncate($post['title'], 25)) ?>
                    </div>
                    <div class="d-flex gap-2 text-white" style="font-size:.72rem">
                      <span><i class="bi bi-heart-fill text-danger me-1"></i><?= (int)$post['like_count'] ?></span>
                      <span><i class="bi bi-chat-fill me-1"></i><?= (int)$post['comment_count'] ?></span>
                    </div>
                  </div>
                  <a href="<?= SITE_URL ?>/post_detail.php?id=<?= (int)$post['id'] ?>"
                     class="stretched-link"></a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <!-- ── Right column ─────────────────────────────────────── -->
  <div class="col-lg-4">

    <!-- Notifications -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0">
            <i class="bi bi-bell text-danger me-2"></i>Notifications
          </h5>
          <a href="<?= SITE_URL ?>/notifications.php"
             class="btn btn-sm btn-outline-secondary rounded-pill">Tout voir</a>
        </div>

        <?php if (empty($notifications)): ?>
          <p class="text-muted small text-center py-2">Aucune notification.</p>
        <?php else: ?>
          <?php foreach ($notifications as $notif): ?>
            <div class="d-flex gap-2 align-items-start py-2 border-bottom">
              <img src="<?= avatarUrlFromFilename($notif['from_avatar'] ?? null) ?>"
                   style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0"
                   alt="">
              <div>
                <div style="font-size:.83rem"><?= h($notif['message']) ?></div>
                <div class="text-muted" style="font-size:.72rem"><?= timeAgo($notif['created_at']) ?></div>
              </div>
              <?php if (!$notif['is_read']): ?>
                <span class="ms-auto" style="width:8px;height:8px;background:#c9184a;border-radius:50%;flex-shrink:0;margin-top:6px"></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Upcoming events -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0">
            <i class="bi bi-calendar-event text-danger me-2"></i>Événements
          </h5>
          <a href="<?= SITE_URL ?>/events.php"
             class="btn btn-sm btn-outline-secondary rounded-pill">Tous</a>
        </div>
        <?php foreach ($upEvents as $ev): ?>
          <a href="<?= SITE_URL ?>/event_detail.php?id=<?= (int)$ev['id'] ?>"
             class="d-flex gap-3 align-items-center text-decoration-none text-dark mb-3 pb-3 border-bottom">
            <div class="text-center rounded-3 p-2 flex-shrink-0"
                 style="background:#fce4ec; min-width:44px;">
              <div style="font-size:1.1rem; font-weight:800; color:#c9184a; line-height:1">
                <?= date('d', strtotime($ev['event_date'])) ?>
              </div>
              <div style="font-size:.62rem; color:#c9184a; text-transform:uppercase; font-weight:700">
                <?= date('M', strtotime($ev['event_date'])) ?>
              </div>
            </div>
            <div style="font-size:.85rem">
              <div class="fw-semibold"><?= h(truncate($ev['title'], 35)) ?></div>
              <div class="text-muted" style="font-size:.75rem">
                <i class="bi bi-people me-1"></i><?= (int)$ev['participant_count'] ?> participants
              </div>
            </div>
          </a>
        <?php endforeach; ?>
        <?php if (empty($upEvents)): ?>
          <p class="text-muted small text-center">Aucun événement à venir.</p>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /.col-lg-4 -->

</div><!-- /.row -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
