<?php
// ============================================================
//  notifications.php — Notifications Center
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Notification.php';

requireLogin();

$pageTitle   = 'Notifications';
$activePage  = '';
$notifModel  = new Notification();
$notifModel->markAllRead(currentUserId()); // mark all read on page visit
$notifications = $notifModel->getForUser(currentUserId(), 50);

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h1 class="mb-0" style="font-size:1.6rem">
        <i class="bi bi-bell text-danger me-2"></i>Notifications
      </h1>
      <button id="markAllRead" class="btn btn-outline-secondary btn-sm rounded-pill">
        <i class="bi bi-check2-all me-1"></i>Tout marquer lu
      </button>
    </div>

    <!-- Notification list -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-0">

        <?php if (empty($notifications)): ?>
          <div class="empty-state py-5">
            <i class="bi bi-bell-slash d-block"></i>
            <p>Vous n'avez aucune notification.</p>
          </div>

        <?php else: ?>
          <?php foreach ($notifications as $notif):
            $icons = [
              'like'    => '<i class="bi bi-heart-fill text-danger"></i>',
              'comment' => '<i class="bi bi-chat-fill text-primary"></i>',
              'event'   => '<i class="bi bi-calendar-event-fill text-warning"></i>',
            ];
            $icon = $icons[$notif['type']] ?? '<i class="bi bi-bell-fill"></i>';
          ?>
            <div class="notif-item <?= !$notif['is_read'] ? 'unread' : '' ?>">

              <!-- Avatar with type icon -->
              <div class="position-relative">
                <img src="<?= avatarUrlFromFilename($notif['from_avatar'] ?? null) ?>"
                     alt="<?= h($notif['from_username']) ?>"
                     class="notif-avatar">
                <span class="position-absolute bottom-0 end-0 rounded-circle bg-white p-1 lh-1"
                      style="font-size:.7rem">
                  <?= $icon ?>
                </span>
              </div>

              <!-- Content -->
              <div class="notif-text">
                <div>
                  <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$notif['from_user_id'] ?>"
                     class="fw-bold text-dark text-decoration-none">
                    <?= h($notif['from_username']) ?>
                  </a>
                  <?= h(str_replace($notif['from_username'], '', $notif['message'])) ?>
                </div>
                <div class="notif-time mt-1">
                  <i class="bi bi-clock me-1"></i><?= timeAgo($notif['created_at']) ?>
                </div>
              </div>

              <!-- Link to post if available -->
              <?php if ($notif['post_id']): ?>
                <a href="<?= SITE_URL ?>/post_detail.php?id=<?= (int)$notif['post_id'] ?>"
                   class="btn btn-light btn-sm rounded-pill flex-shrink-0">
                  <i class="bi bi-arrow-right"></i>
                </a>
              <?php endif; ?>

              <!-- Unread dot -->
              <?php if (!$notif['is_read']): ?>
                <span class="notif-dot"></span>
              <?php endif; ?>

            </div>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
