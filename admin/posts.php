<?php
// ============================================================
//  admin/posts.php — Post Moderation
// ============================================================

require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../models/Post.php';

$pageTitle  = 'Publications';
$activePage = 'posts';
$breadcrumb = 'Publications';

$db = getDB();

// ── Delete a post ─────────────────────────────────────────────
if (isset($_GET['action'], $_GET['post_id']) && $_GET['action'] === 'delete'
    && verifyCsrf($_GET['csrf'] ?? '')) {

    $postId    = (int)$_GET['post_id'];
    $row       = $db->prepare('SELECT image FROM posts WHERE id = ?');
    $row->execute([$postId]);
    $imgData   = $row->fetch();
    if ($imgData) deleteUpload($imgData['image'], UPLOAD_POSTS);
    $db->prepare('DELETE FROM posts WHERE id = ?')->execute([$postId]);
    setFlash('success', 'Publication supprimée.');
    redirect(SITE_URL . '/admin/posts.php');
}

// ── Filters ──────────────────────────────────────────────────
$catFilter = $_GET['cat']  ?? '';
$search    = trim($_GET['q'] ?? '');
$sortBy    = $_GET['sort']  ?? 'newest';

$where  = 'WHERE 1=1';
$params = [];

if ($catFilter) {
    $where   .= ' AND p.category = ?';
    $params[] = $catFilter;
}
if ($search) {
    $where   .= ' AND (p.title LIKE ? OR u.username LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = array_merge($params, [$like, $like]);
}

$order = match($sortBy) {
    'likes'    => 'likes DESC',
    'comments' => 'comment_count DESC',
    'oldest'   => 'p.created_at ASC',
    default    => 'p.created_at DESC',
};

// Pagination
$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$stmtC = $db->prepare(
    "SELECT COUNT(*) FROM posts p JOIN users u ON p.user_id = u.id $where"
);
$stmtC->execute($params);
$total = (int)$stmtC->fetchColumn();
$pages = (int)ceil($total / $perPage);

$stmt = $db->prepare(
    "SELECT p.*,
            u.username, u.full_name, u.avatar AS u_avatar,
            (SELECT COUNT(*) FROM likes    WHERE post_id = p.id) AS likes,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
     FROM posts p
     JOIN users u ON p.user_id = u.id
     $where
     ORDER BY $order
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Category stats
$catStats = $db->query(
    "SELECT category, COUNT(*) AS cnt FROM posts GROUP BY category ORDER BY cnt DESC"
)->fetchAll();

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">
      <i class="bi bi-images text-danger me-2"></i>Publications
    </h1>
    <p class="admin-page-subtitle"><?= $total ?> publications trouvées</p>
  </div>
</div>

<!-- Category quick filters -->
<div class="d-flex gap-2 flex-wrap mb-4">
  <a href="?sort=<?= h($sortBy) ?>&q=<?= urlencode($search) ?>"
     class="btn btn-sm <?= !$catFilter ? 'btn-danger' : 'btn-outline-secondary' ?> rounded-pill">
    Toutes
  </a>
  <?php foreach ($catStats as $cs): ?>
    <a href="?cat=<?= urlencode($cs['category']) ?>&sort=<?= h($sortBy) ?>&q=<?= urlencode($search) ?>"
       class="btn btn-sm <?= $catFilter === $cs['category'] ? 'btn-danger' : 'btn-outline-secondary' ?> rounded-pill">
      <?= h(ucfirst(str_replace('_', ' ', $cs['category']))) ?>
      <span class="badge <?= $catFilter === $cs['category'] ? 'bg-white text-danger' : 'bg-secondary' ?> ms-1">
        <?= (int)$cs['cnt'] ?>
      </span>
    </a>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <!-- Toolbar -->
  <div class="admin-card-header">
    <h6 class="admin-card-title">
      <i class="bi bi-grid text-danger"></i> Modération des créations
    </h6>
    <div class="admin-toolbar">
      <!-- Search -->
      <form method="GET" class="admin-search">
        <i class="bi bi-search"></i>
        <input type="text" name="q" id="tableSearch"
               value="<?= h($search) ?>"
               placeholder="Titre, auteur…"
               class="form-control form-control-sm">
        <input type="hidden" name="cat"  value="<?= h($catFilter) ?>">
        <input type="hidden" name="sort" value="<?= h($sortBy) ?>">
      </form>
      <!-- Sort -->
      <select class="form-select form-select-sm w-auto rounded-pill"
              onchange="window.location.href='?sort='+this.value+'&cat=<?= urlencode($catFilter) ?>&q=<?= urlencode($search) ?>'">
        <?php foreach (['newest' => 'Plus récents','likes' => 'Plus likés','comments' => 'Plus commentés','oldest' => 'Plus anciens'] as $val => $lbl): ?>
          <option value="<?= $val ?>" <?= $sortBy === $val ? 'selected' : '' ?>><?= $lbl ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Table -->
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Titre</th>
          <th>Auteur</th>
          <th>Catégorie</th>
          <th>Likes</th>
          <th>Commentaires</th>
          <th>Publiée le</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($posts)): ?>
          <tr><td colspan="8" class="text-center text-muted py-5">Aucune publication trouvée.</td></tr>
        <?php endif; ?>
        <?php foreach ($posts as $p): ?>
          <tr>
            <td>
              <img src="<?= UPLOAD_URL_POSTS . h($p['image']) ?>"
                   class="tbl-thumb rounded-3" alt="">
            </td>
            <td>
              <a href="<?= SITE_URL ?>/post_detail.php?id=<?= (int)$p['id'] ?>"
                 target="_blank"
                 class="fw-semibold text-dark text-decoration-none">
                <?= h(truncate($p['title'], 45)) ?>
              </a>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= avatarUrlFromFilename($p['u_avatar'] ?? null) ?>"
                     class="tbl-avatar" alt="">
                <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$p['user_id'] ?>"
                   target="_blank" class="text-muted small">
                  @<?= h($p['username']) ?>
                </a>
              </div>
            </td>
            <td><?= categoryBadge($p['category']) ?></td>
            <td>
              <span class="text-danger fw-bold">
                <i class="bi bi-heart-fill me-1" style="font-size:.7rem"></i><?= (int)$p['likes'] ?>
              </span>
            </td>
            <td>
              <span class="text-muted">
                <i class="bi bi-chat me-1" style="font-size:.7rem"></i><?= (int)$p['comment_count'] ?>
              </span>
            </td>
            <td class="text-muted small"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= SITE_URL ?>/post_detail.php?id=<?= (int)$p['id'] ?>"
                   class="btn btn-sm btn-light rounded-pill"
                   target="_blank" title="Voir">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="?action=delete&post_id=<?= (int)$p['id'] ?>&csrf=<?= csrfToken() ?>&cat=<?= urlencode($catFilter) ?>"
                   class="btn btn-sm btn-outline-danger rounded-pill"
                   title="Supprimer"
                   data-confirm="Supprimer la publication « <?= h($p['title']) ?> » ?">
                  <i class="bi bi-trash"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
    <div class="d-flex justify-content-center py-3">
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page-1 ?>&cat=<?= urlencode($catFilter) ?>&sort=<?= h($sortBy) ?>&q=<?= urlencode($search) ?>">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>
          <?php for ($p = 1; $p <= $pages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $p ?>&cat=<?= urlencode($catFilter) ?>&sort=<?= h($sortBy) ?>&q=<?= urlencode($search) ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page+1 ?>&cat=<?= urlencode($catFilter) ?>&sort=<?= h($sortBy) ?>&q=<?= urlencode($search) ?>">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  <?php endif; ?>

</div><!-- /.admin-card -->

<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/admin_footer.php'; ?>
