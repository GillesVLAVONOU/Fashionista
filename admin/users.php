<?php
// ============================================================
//  admin/users.php — User Management
// ============================================================

require_once __DIR__ . '/admin_guard.php';

$pageTitle  = 'Utilisateurs';
$activePage = 'users';
$breadcrumb = 'Utilisateurs';

$db = getDB();

// ── Handle role change or ban via query string ────────────────
if (isset($_GET['action'], $_GET['user_id']) && verifyCsrf($_GET['csrf'] ?? '')) {
    $targetId = (int)$_GET['user_id'];
    $action   = $_GET['action'];

    if ($targetId === currentUserId()) {
        setFlash('error', 'Vous ne pouvez pas modifier votre propre compte ici.');
    } else {
        if ($action === 'make_admin') {
            $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$targetId]);
            setFlash('success', 'Utilisateur promu administrateur.');
        } elseif ($action === 'make_student') {
            $db->prepare("UPDATE users SET role = 'student' WHERE id = ?")->execute([$targetId]);
            setFlash('success', 'Rôle remis à étudiant.');
        } elseif ($action === 'delete') {
            // Fetch avatar & post images first then cascade delete
            $u = $db->prepare('SELECT avatar FROM users WHERE id = ?');
            $u->execute([$targetId]);
            $userData = $u->fetch();
            if ($userData) deleteUpload($userData['avatar'] ?? '', UPLOAD_AVATARS);
            $db->prepare('DELETE FROM users WHERE id = ?')->execute([$targetId]);
            setFlash('success', 'Utilisateur supprimé définitivement.');
        }
    }
    redirect(SITE_URL . '/admin/users.php');
}

// ── Filters ──────────────────────────────────────────────────
$roleFilter  = $_GET['role']   ?? '';
$search      = trim($_GET['q'] ?? '');

$where  = 'WHERE 1=1';
$params = [];

if ($roleFilter) {
    $where   .= ' AND role = ?';
    $params[] = $roleFilter;
}
if ($search) {
    $where   .= ' AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = array_merge($params, [$like, $like, $like]);
}

// Counts
$totalAll     = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAdmins  = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$totalStudents = $totalAll - $totalAdmins;

// Paginate
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$total   = (int)$db->prepare("SELECT COUNT(*) FROM users $where")->execute($params) ? 0 : 0;
$stmtC   = $db->prepare("SELECT COUNT(*) FROM users $where");
$stmtC->execute($params);
$total   = (int)$stmtC->fetchColumn();
$pages   = (int)ceil($total / $perPage);

$stmt = $db->prepare(
    "SELECT u.*,
            (SELECT COUNT(*) FROM posts    WHERE user_id = u.id) AS post_count,
            (SELECT COUNT(*) FROM likes l JOIN posts p ON l.post_id = p.id WHERE p.user_id = u.id) AS likes_total
     FROM users u $where
     ORDER BY u.created_at DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/admin_header.php';
?>

<!-- Page header -->
<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">
      <i class="bi bi-people text-danger me-2"></i>Utilisateurs
    </h1>
    <p class="admin-page-subtitle"><?= $total ?> résultats · <?= $totalStudents ?> étudiants · <?= $totalAdmins ?> admins</p>
  </div>
</div>

<!-- Quick filter tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
  <?php
  $tabs = [
    ['label' => 'Tous',       'val' => '',        'count' => $totalAll],
    ['label' => 'Étudiants',  'val' => 'student', 'count' => $totalStudents],
    ['label' => 'Admins',     'val' => 'admin',   'count' => $totalAdmins],
  ];
  foreach ($tabs as $tab):
  ?>
    <a href="?role=<?= $tab['val'] ?>&q=<?= urlencode($search) ?>"
       class="btn btn-sm <?= $roleFilter === $tab['val'] ? 'btn-danger' : 'btn-outline-secondary' ?> rounded-pill">
      <?= $tab['label'] ?>
      <span class="badge <?= $roleFilter === $tab['val'] ? 'bg-white text-danger' : 'bg-secondary' ?> ms-1">
        <?= $tab['count'] ?>
      </span>
    </a>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <!-- Toolbar -->
  <div class="admin-card-header">
    <h6 class="admin-card-title">
      <i class="bi bi-list-ul text-danger"></i> Liste des utilisateurs
    </h6>
    <div class="admin-toolbar">
      <form method="GET" class="admin-search">
        <i class="bi bi-search"></i>
        <input type="text" name="q" id="tableSearch"
               value="<?= h($search) ?>"
               placeholder="Nom, pseudo, email…"
               class="form-control form-control-sm">
        <input type="hidden" name="role" value="<?= h($roleFilter) ?>">
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Utilisateur</th>
          <th>Email</th>
          <th>Rôle</th>
          <th>Publications</th>
          <th>Likes reçus</th>
          <th>Membre depuis</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr><td colspan="8" class="text-center text-muted py-5">Aucun utilisateur trouvé.</td></tr>
        <?php endif; ?>
        <?php foreach ($users as $u): ?>
          <tr>
            <td class="text-muted small"><?= (int)$u['id'] ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= avatarUrlFromFilename($u['avatar'] ?? null) ?>"
                     class="tbl-avatar" alt="">
                <div>
                  <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$u['id'] ?>"
                     target="_blank"
                     class="fw-semibold text-dark text-decoration-none">
                    <?= h($u['full_name']) ?>
                  </a>
                  <div class="text-muted small">@<?= h($u['username']) ?></div>
                </div>
              </div>
            </td>
            <td class="text-muted small"><?= h($u['email']) ?></td>
            <td>
              <span class="badge badge-status-<?= $u['role'] === 'admin' ? 'admin' : 'student' ?>">
                <?= $u['role'] === 'admin' ? 'Admin' : 'Étudiant' ?>
              </span>
            </td>
            <td class="fw-semibold"><?= (int)$u['post_count'] ?></td>
            <td>
              <span class="text-danger fw-semibold">
                <i class="bi bi-heart-fill me-1" style="font-size:.7rem"></i>
                <?= (int)$u['likes_total'] ?>
              </span>
            </td>
            <td class="text-muted small"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td>
              <div class="d-flex gap-1">
                <!-- View profile -->
                <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$u['id'] ?>"
                   class="btn btn-sm btn-light rounded-pill"
                   target="_blank" title="Voir le profil">
                  <i class="bi bi-eye"></i>
                </a>

                <?php if ($u['id'] !== currentUserId()): ?>
                  <!-- Toggle role -->
                  <?php if ($u['role'] === 'student'): ?>
                    <a href="?action=make_admin&user_id=<?= (int)$u['id'] ?>&csrf=<?= csrfToken() ?>"
                       class="btn btn-sm btn-outline-warning rounded-pill"
                       title="Promouvoir admin"
                       data-confirm="Promouvoir <?= h($u['username']) ?> en administrateur ?">
                      <i class="bi bi-shield-plus"></i>
                    </a>
                  <?php else: ?>
                    <a href="?action=make_student&user_id=<?= (int)$u['id'] ?>&csrf=<?= csrfToken() ?>"
                       class="btn btn-sm btn-outline-secondary rounded-pill"
                       title="Rétrograder en étudiant"
                       data-confirm="Rétrograder <?= h($u['username']) ?> en étudiant ?">
                      <i class="bi bi-shield-minus"></i>
                    </a>
                  <?php endif; ?>

                  <!-- Delete user -->
                  <a href="?action=delete&user_id=<?= (int)$u['id'] ?>&csrf=<?= csrfToken() ?>"
                     class="btn btn-sm btn-outline-danger rounded-pill"
                     title="Supprimer l'utilisateur"
                     data-confirm="⚠️ Supprimer définitivement <?= h($u['username']) ?> et toutes ses données ?">
                    <i class="bi bi-trash"></i>
                  </a>
                <?php endif; ?>
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
            <a class="page-link" href="?page=<?= $page-1 ?>&role=<?= h($roleFilter) ?>&q=<?= urlencode($search) ?>">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>
          <?php for ($p = 1; $p <= $pages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $p ?>&role=<?= h($roleFilter) ?>&q=<?= urlencode($search) ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page+1 ?>&role=<?= h($roleFilter) ?>&q=<?= urlencode($search) ?>">
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
