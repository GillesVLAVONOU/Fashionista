<?php
// ============================================================
//  index.php — Homepage & Main Feed
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Post.php';
require_once __DIR__ . '/models/Event.php';

$pageTitle  = 'Accueil';
$activePage = 'feed';

// Pagination
$page     = max(1, (int)($_GET['page'] ?? 1));
$postModel = new Post();
$total    = $postModel->countAll();
$posts    = $postModel->getFeed($page, POSTS_PER_PAGE);
$pages    = (int)ceil($total / POSTS_PER_PAGE);

// Category filter (client-side, but we pass active filter)
$catFilter = $_GET['cat'] ?? '';

// Upcoming events (sidebar)
$eventModel    = new Event();
$upcomingEvents = $eventModel->getUpcoming(3);

require_once __DIR__ . '/includes/header.php';
?>

<?php if (!isLoggedIn()): ?>
<!-- ── Hero (guests only) ─────────────────────────────────── -->
<div class="hero-section" style="margin:-1.5rem -12px 2rem; padding: 5rem 2rem;">
  <h1 class="hero-title">La mode universitaire,<br><span>réinventée</span></h1>
  <p class="hero-subtitle">Publiez vos créations, découvrez les talents de votre campus et participez aux événements.</p>
  <div class="d-flex gap-3 justify-content-center flex-wrap">
    <a href="<?= SITE_URL ?>/register.php" class="btn btn-fashion btn-lg">
      <i class="bi bi-stars me-2"></i>Rejoindre la communauté
    </a>
    <a href="<?= SITE_URL ?>/events.php" class="btn btn-outline-light btn-lg border-2">
      <i class="bi bi-calendar-event me-2"></i>Voir les événements
    </a>
  </div>
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- ── Main feed ─────────────────────────────────────────── -->
  <div class="col-lg-8">

    <!-- Feed header & filters -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
      <h2 class="section-title mb-0">
        <i class="bi bi-grid-3x3-gap text-danger me-2"></i>Créations
      </h2>
      <!-- Category pills -->
      <div class="d-flex gap-2 flex-wrap">
        <a href="<?= SITE_URL ?>/index.php"
           class="badge rounded-pill <?= $catFilter === '' ? 'bg-danger' : 'bg-light text-dark border' ?> text-decoration-none px-3 py-2">
          Tout
        </a>
        <?php foreach (['robe','streetwear','accessoire','haute_couture','costume','autre'] as $cat): ?>
          <a href="<?= SITE_URL ?>/index.php?cat=<?= $cat ?>"
             class="badge rounded-pill <?= $catFilter === $cat ? 'bg-danger' : 'bg-light text-dark border' ?> text-decoration-none px-3 py-2">
            <?= ucfirst(str_replace('_', ' ', $cat)) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (empty($posts)): ?>
      <div class="empty-state">
        <i class="bi bi-images d-block"></i>
        <p>Aucune création publiée pour le moment.</p>
        <?php if (isLoggedIn()): ?>
          <a href="<?= SITE_URL ?>/create_post.php" class="btn btn-fashion mt-2">
            <i class="bi bi-plus-circle me-2"></i>Publier la première
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-2 g-4" id="postGrid">
        <?php foreach ($posts as $post):
          // Client-side filter: hide non-matching (JS handles it too, but PHP pre-filters)
          if ($catFilter && $post['category'] !== $catFilter) continue;
        ?>
          <div class="col">
            <?php require __DIR__ . '/views/post_card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pages > 1): ?>
        <nav class="mt-5 d-flex justify-content-center">
          <ul class="pagination">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page - 1 ?>&cat=<?= h($catFilter) ?>">
                <i class="bi bi-chevron-left"></i>
              </a>
            </li>
            <?php for ($p = 1; $p <= $pages; $p++): ?>
              <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&cat=<?= h($catFilter) ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page + 1 ?>&cat=<?= h($catFilter) ?>">
                <i class="bi bi-chevron-right"></i>
              </a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    <?php endif; ?>

  </div><!-- /.col-lg-8 -->

  <!-- ── Sidebar ───────────────────────────────────────────── -->
  <div class="col-lg-4">

    <!-- CTA card for guests -->
    <?php if (!isLoggedIn()): ?>
      <div class="card border-0 shadow-sm rounded-4 mb-4"
           style="background: linear-gradient(135deg,#1a1a2e,#16213e); color:#fff;">
        <div class="card-body p-4 text-center">
          <i class="bi bi-person-plus fs-1 mb-2 d-block" style="color:#ff6b9d"></i>
          <h5 class="fw-bold">Rejoignez Fashionista</h5>
          <p class="small opacity-75 mb-3">Publiez vos créations et connectez-vous avec d'autres étudiants créateurs.</p>
          <a href="<?= SITE_URL ?>/register.php" class="btn btn-fashion w-100 mb-2">S'inscrire</a>
          <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-light w-100">Se connecter</a>
        </div>
      </div>
    <?php else: ?>
      <!-- Quick publish CTA -->
      <div class="card border-0 shadow-sm rounded-4 mb-4"
           style="background: linear-gradient(135deg,#c9184a,#ff6b9d); color:#fff;">
        <div class="card-body p-4 text-center">
          <i class="bi bi-camera fs-1 mb-2 d-block"></i>
          <h5 class="fw-bold">Partagez votre création</h5>
          <p class="small opacity-80 mb-3">Montrez votre talent à toute la communauté.</p>
          <a href="<?= SITE_URL ?>/create_post.php" class="btn btn-light fw-bold w-100">
            <i class="bi bi-plus-circle me-2"></i>Publier une création
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Upcoming events -->
    <?php if (!empty($upcomingEvents)): ?>
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
          <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-calendar-event text-danger"></i>
            Événements à venir
          </h6>
          <?php foreach ($upcomingEvents as $ev): ?>
            <a href="<?= SITE_URL ?>/event_detail.php?id=<?= (int)$ev['id'] ?>"
               class="d-flex gap-3 align-items-start p-2 rounded-3 hover-bg text-decoration-none text-dark mb-2"
               style="transition:.2s">
              <!-- Date block -->
              <div class="text-center rounded-3 p-2 flex-shrink-0"
                   style="background:#fce4ec; min-width:48px;">
                <div style="font-size:1.2rem; font-weight:800; color:#c9184a; line-height:1">
                  <?= date('d', strtotime($ev['event_date'])) ?>
                </div>
                <div style="font-size:.65rem; font-weight:700; color:#c9184a; text-transform:uppercase">
                  <?= date('M', strtotime($ev['event_date'])) ?>
                </div>
              </div>
              <div>
                <div class="fw-semibold" style="font-size:.88rem"><?= h(truncate($ev['title'], 40)) ?></div>
                <div class="text-muted" style="font-size:.76rem">
                  <i class="bi bi-geo-alt me-1"></i><?= h(truncate($ev['location'] ?? '', 30)) ?>
                </div>
                <div style="font-size:.72rem">
                  <span class="badge badge-event-<?= h($ev['type']) ?> mt-1">
                    <?= h($ev['type']) ?>
                  </span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
          <a href="<?= SITE_URL ?>/events.php"
             class="btn btn-outline-danger btn-sm w-100 mt-2 rounded-pill">
            Tous les événements →
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Stats card -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-3">
        <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart text-danger me-2"></i>La plateforme</h6>
        <div class="row g-2 text-center">
          <?php
            $db        = getDB();
            $nPosts    = $db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
            $nUsers    = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
            $nEvents   = $db->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW()")->fetchColumn();
          ?>
          <div class="col-4">
            <div class="fw-bold fs-5 text-danger"><?= $nPosts ?></div>
            <div class="text-muted" style="font-size:.72rem">Créations</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-5 text-danger"><?= $nUsers ?></div>
            <div class="text-muted" style="font-size:.72rem">Étudiants</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-5 text-danger"><?= $nEvents ?></div>
            <div class="text-muted" style="font-size:.72rem">Événements</div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.col-lg-4 -->
</div><!-- /.row -->

<!-- CSRF meta tag for JS -->
<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/includes/footer.php'; ?>
