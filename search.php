<?php
// ============================================================
//  search.php — Search Posts & Users
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Post.php';
require_once __DIR__ . '/models/User.php';

$query      = trim($_GET['q'] ?? '');
$tab        = $_GET['tab'] ?? 'posts'; // posts | users
$pageTitle  = 'Recherche : ' . h($query);
$activePage = '';

$posts = [];
$users = [];

if (mb_strlen($query) >= 2) {
    $postModel = new Post();
    $userModel = new User();
    $posts     = $postModel->search($query);
    $users     = $userModel->search($query);
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Search header -->
<div class="mb-4">
  <h1 style="font-size:1.6rem">
    <i class="bi bi-search text-danger me-2"></i>Recherche
  </h1>

  <form action="<?= SITE_URL ?>/search.php" method="GET" class="d-flex gap-2">
    <input type="text"
           name="q"
           class="form-control form-control-lg rounded-pill"
           placeholder="Rechercher créations, étudiants…"
           value="<?= h($query) ?>"
           autofocus>
    <button type="submit" class="btn btn-fashion px-4 rounded-pill">
      <i class="bi bi-search"></i>
    </button>
  </form>
</div>

<?php if (mb_strlen($query) < 2): ?>
  <div class="empty-state">
    <i class="bi bi-search d-block"></i>
    <p>Entrez au moins 2 caractères pour rechercher.</p>
  </div>

<?php else: ?>

  <!-- Tabs -->
  <ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
      <a class="nav-link <?= $tab === 'posts' ? 'active bg-danger' : '' ?>"
         href="?q=<?= urlencode($query) ?>&tab=posts">
        <i class="bi bi-images me-1"></i>Créations
        <span class="badge bg-white text-danger ms-1"><?= count($posts) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $tab === 'users' ? 'active bg-danger' : '' ?>"
         href="?q=<?= urlencode($query) ?>&tab=users">
        <i class="bi bi-people me-1"></i>Étudiants
        <span class="badge bg-white text-danger ms-1"><?= count($users) ?></span>
      </a>
    </li>
  </ul>

  <!-- Posts results -->
  <?php if ($tab === 'posts'): ?>
    <?php if (empty($posts)): ?>
      <div class="empty-state">
        <i class="bi bi-images d-block"></i>
        <p>Aucune création trouvée pour "<?= h($query) ?>".</p>
      </div>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($posts as $post): ?>
          <div class="col">
            <?php require __DIR__ . '/views/post_card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Users results -->
  <?php if ($tab === 'users'): ?>
    <?php if (empty($users)): ?>
      <div class="empty-state">
        <i class="bi bi-person-slash d-block"></i>
        <p>Aucun étudiant trouvé pour "<?= h($query) ?>".</p>
      </div>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
        <?php foreach ($users as $u): ?>
          <div class="col">
            <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$u['id'] ?>"
               class="card border-0 shadow-sm rounded-4 text-decoration-none text-dark h-100">
              <div class="card-body d-flex align-items-center gap-3 p-3">
                <img src="<?= avatarUrlFromFilename($u['avatar'] ?? null) ?>"
                     alt="<?= h($u['username']) ?>"
                     style="width:54px;height:54px;border-radius:50%;object-fit:cover;border:2px solid var(--clr-primary)">
                <div>
                  <div class="fw-bold"><?= h($u['full_name']) ?></div>
                  <div class="text-muted small">@<?= h($u['username']) ?></div>
                  <?php if (!empty($u['bio'])): ?>
                    <div class="text-muted" style="font-size:.78rem"><?= h(truncate($u['bio'], 55)) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

<?php endif; ?>

<!-- CSRF meta for JS -->
<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/includes/footer.php'; ?>
