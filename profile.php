<?php
// ============================================================
//  profile.php — Public User Profile
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Post.php';

$userId    = (int)($_GET['id'] ?? 0);
$userModel = new User();
$profile   = $userModel->findById($userId);

if (!$profile) {
    setFlash('error', 'Utilisateur introuvable.');
    redirect(SITE_URL . '/index.php');
}

$postModel   = new Post();
$posts       = $postModel->getByUser($userId);
$postCount   = count($posts);
$likesTotal  = $userModel->totalLikesReceived($userId);
$eventCount  = $userModel->eventCount($userId);

$isOwner     = isLoggedIn() && currentUserId() === $userId;
$pageTitle   = h($profile['full_name']) . ' — Profil';
$activePage  = '';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Profile Header ─────────────────────────────────────── -->
<div class="profile-header mb-4">
  <div class="row align-items-center g-4 profile-header-row">

    <!-- Avatar -->
    <div class="col-12 col-md-auto profile-avatar-col">
      <div class="profile-avatar-wrap">
        <img src="<?= avatarUrl($profile) ?>"
             alt="<?= h($profile['full_name']) ?>"
             class="profile-avatar">
      </div>
    </div>

    <!-- Info -->
    <div class="col-12 col-md profile-info-col">
      <h1 class="profile-name"><?= h($profile['full_name']) ?></h1>
      <div class="profile-username">@<?= h($profile['username']) ?></div>
      <?php if (!empty($profile['bio'])): ?>
        <p class="profile-bio"><?= nl2br(h($profile['bio'])) ?></p>
      <?php endif; ?>

      <!-- Member since -->
      <div class="text-white-50 small mt-2">
        <i class="bi bi-calendar3 me-1"></i>
        Membre depuis <?= date('F Y', strtotime($profile['created_at'])) ?>
      </div>

      <!-- Actions -->
      <?php if ($isOwner): ?>
        <a href="<?= SITE_URL ?>/edit_profile.php"
           class="btn btn-sm btn-outline-light rounded-pill mt-3 me-2">
          <i class="bi bi-pencil me-1"></i>Modifier le profil
        </a>
        <a href="<?= SITE_URL ?>/create_post.php"
           class="btn btn-sm btn-fashion mt-3">
          <i class="bi bi-plus-circle me-1"></i>Nouvelle création
        </a>
      <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="col-12 profile-stats-col">
      <div class="d-flex gap-1 flex-nowrap justify-content-center profile-stats-row mt-2">
        <div class="profile-stat">
          <div class="profile-stat-value"><?= $postCount ?></div>
          <div class="profile-stat-label">Créations</div>
        </div>
        <div class="profile-stat">
          <div class="profile-stat-value"><?= $likesTotal ?></div>
          <div class="profile-stat-label">Likes</div>
        </div>
        <div class="profile-stat">
          <div class="profile-stat-value"><?= $eventCount ?></div>
          <div class="profile-stat-label">Événements</div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ── Posts Grid ─────────────────────────────────────────── -->
<h2 class="section-title">
  <i class="bi bi-images text-danger me-2"></i>
  <?= $isOwner ? 'Mes créations' : 'Créations' ?>
</h2>

<?php if (empty($posts)): ?>
  <div class="empty-state">
    <i class="bi bi-camera d-block"></i>
    <p>
      <?= $isOwner
          ? 'Vous n\'avez pas encore publié de création.'
          : h($profile['full_name']) . ' n\'a pas encore publié de création.' ?>
    </p>
    <?php if ($isOwner): ?>
      <a href="<?= SITE_URL ?>/create_post.php" class="btn btn-fashion mt-2">
        <i class="bi bi-plus-circle me-2"></i>Publier ma première création
      </a>
    <?php endif; ?>
  </div>

<?php else: ?>
  <!-- Instagram-style grid -->
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2" id="profileGrid">
    <?php foreach ($posts as $post): ?>
      <div class="col">
        <a href="<?= SITE_URL ?>/post_detail.php?id=<?= (int)$post['id'] ?>"
           class="d-block position-relative rounded-3 overflow-hidden"
           style="aspect-ratio:1/1; background:#eee;">
          <img src="<?= postImageUrl($post) ?>"
               alt="<?= h($post['title']) ?>"
               style="width:100%;height:100%;object-fit:cover;transition:.3s"
               loading="lazy">
          <!-- Hover overlay -->
          <div class="position-absolute inset-0 d-flex align-items-center justify-content-center gap-4"
               style="background:rgba(0,0,0,0);transition:.3s;top:0;left:0;right:0;bottom:0"
               onmouseover="this.style.background='rgba(0,0,0,.5)';this.querySelectorAll('.ov').forEach(e=>e.style.opacity=1)"
               onmouseout="this.style.background='rgba(0,0,0,0)';this.querySelectorAll('.ov').forEach(e=>e.style.opacity=0)">
            <div class="ov text-white fw-bold" style="opacity:0;transition:.3s;font-size:.9rem">
              <i class="bi bi-heart-fill text-danger me-1"></i><?= (int)$post['like_count'] ?>
            </div>
            <div class="ov text-white fw-bold" style="opacity:0;transition:.3s;font-size:.9rem">
              <i class="bi bi-chat-fill me-1"></i><?= (int)$post['comment_count'] ?>
            </div>
          </div>
          <!-- Delete button (owner only) -->
          <?php if ($isOwner): ?>
            <form method="POST" action="<?= SITE_URL ?>/delete_post.php"
                  class="position-absolute" style="top:6px;right:6px;z-index:10"
                  onsubmit="return confirm('Supprimer cette création ?')">
              <?= csrfField() ?>
              <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
              <button class="btn btn-sm btn-danger opacity-75 rounded-circle p-1 lh-1"
                      style="width:28px;height:28px;">
                <i class="bi bi-trash" style="font-size:.7rem"></i>
              </button>
            </form>
          <?php endif; ?>
        </a>
        <div class="mt-1 px-1" style="font-size:.78rem; font-weight:600; color:#333">
          <?= h(truncate($post['title'], 30)) ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>

<!-- CSRF meta for JS -->
<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/includes/footer.php'; ?>
