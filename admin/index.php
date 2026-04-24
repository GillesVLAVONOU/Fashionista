<?php
// ============================================================
//  admin/index.php — Admin Dashboard Overview
// ============================================================

require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Event.php';

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
$breadcrumb = 'Dashboard';

$db = getDB();

// ── Global stats ─────────────────────────────────────────────
$totalUsers    = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalPosts    = (int)$db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
$totalLikes    = (int)$db->query('SELECT COUNT(*) FROM likes')->fetchColumn();
$totalComments = (int)$db->query('SELECT COUNT(*) FROM comments')->fetchColumn();
$totalEvents   = (int)$db->query('SELECT COUNT(*) FROM events')->fetchColumn();
$totalPartic   = (int)$db->query('SELECT COUNT(*) FROM event_participants')->fetchColumn();

// ── This week's new users & posts ───────────────────────────
$newUsersWeek  = (int)$db->query(
    "SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
)->fetchColumn();
$newPostsWeek  = (int)$db->query(
    "SELECT COUNT(*) FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
)->fetchColumn();

// ── Latest registrations (5) ─────────────────────────────────
$latestUsers = $db->query(
    'SELECT id, username, full_name, avatar, email, role, created_at
     FROM users ORDER BY created_at DESC LIMIT 6'
)->fetchAll();

// ── Most liked posts (5) ─────────────────────────────────────
$topPosts = $db->query(
    'SELECT p.id, p.title, p.image, p.category,
            u.username, u.avatar AS u_avatar,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS likes
     FROM posts p
     JOIN users u ON p.user_id = u.id
     ORDER BY likes DESC
     LIMIT 5'
)->fetchAll();

// ── Recent activity ───────────────────────────────────────────
$recentActivity = $db->query(
    "(SELECT 'like' AS type, l.created_at,
             CONCAT(u.username, ' a aimé la création de ', pu.username) AS msg,
             u.avatar
      FROM likes l
      JOIN users u  ON l.user_id = u.id
      JOIN posts p  ON l.post_id = p.id
      JOIN users pu ON p.user_id = pu.id)

     UNION ALL

     (SELECT 'comment' AS type, c.created_at,
             CONCAT(u.username, ' a commenté une création') AS msg,
             u.avatar
      FROM comments c
      JOIN users u ON c.user_id = u.id)

     UNION ALL

     (SELECT 'register' AS type, u.created_at,
             CONCAT(u.username, ' a rejoint la plateforme') AS msg,
             u.avatar
      FROM users u)

     ORDER BY created_at DESC
     LIMIT 12"
)->fetchAll();

require_once __DIR__ . '/admin_header.php';
?>

<!-- ── Page header ─────────────────────────────────────────── -->
<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">
      <i class="bi bi-speedometer2 text-danger me-2"></i>Dashboard
    </h1>
    <p class="admin-page-subtitle">
      Vue d'ensemble de la plateforme — <?= date('l d F Y') ?>
    </p>
  </div>
  <a href="<?= SITE_URL ?>/admin/create_event.php" class="btn btn-fashion rounded-pill">
    <i class="bi bi-plus-circle me-2"></i>Créer un événement
  </a>
</div>

<!-- ── Stats row ──────────────────────────────────────────── -->
<div class="row g-3 mb-4">

  <?php
  $stats = [
    ['label' => 'Étudiants inscrits',   'value' => $totalUsers,    'icon' => 'bi-people-fill',          'accent_start' => '#c9184a', 'accent_end' => '#ff6b9d', 'sub' => '+' . $newUsersWeek . ' cette semaine'],
    ['label' => 'Créations publiées',   'value' => $totalPosts,    'icon' => 'bi-images',               'accent_start' => '#7b1fa2', 'accent_end' => '#ba68c8', 'sub' => '+' . $newPostsWeek . ' cette semaine'],
    ['label' => 'Likes total',          'value' => $totalLikes,    'icon' => 'bi-heart-fill',           'accent_start' => '#e91e63', 'accent_end' => '#f48fb1', 'sub' => 'Sur toutes les créations'],
    ['label' => 'Commentaires',         'value' => $totalComments, 'icon' => 'bi-chat-fill',            'accent_start' => '#1565c0', 'accent_end' => '#42a5f5', 'sub' => 'Sur toutes les créations'],
    ['label' => 'Événements créés',     'value' => $totalEvents,   'icon' => 'bi-calendar-event-fill', 'accent_start' => '#2e7d32', 'accent_end' => '#66bb6a', 'sub' => 'Dont ' . $totalPartic . ' inscriptions'],
  ];
  foreach ($stats as $s):
  ?>
  <div class="col-sm-6 col-lg">
    <div class="admin-stat-card"
         style="--accent-start:<?= $s['accent_start'] ?>;--accent-end:<?= $s['accent_end'] ?>">
      <i class="bi <?= $s['icon'] ?> admin-stat-icon"></i>
      <div class="admin-stat-value" data-target="<?= $s['value'] ?>">
        <?= number_format($s['value'], 0, ',', ' ') ?>
      </div>
      <div class="admin-stat-label"><?= $s['label'] ?></div>
      <div class="admin-stat-trend text-muted"><?= $s['sub'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>

</div><!-- /.stats -->

<div class="row g-4">

  <!-- ── Latest users ─────────────────────────────────────── -->
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-person-plus text-danger"></i> Derniers inscrits
        </h6>
        <a href="<?= SITE_URL ?>/admin/users.php"
           class="btn btn-sm btn-outline-secondary rounded-pill">Voir tous</a>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Utilisateur</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Inscrit le</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($latestUsers as $u): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= avatarUrlFromFilename($u['avatar'] ?? null) ?>"
                     class="tbl-avatar" alt="">
                <div>
                  <div class="fw-semibold"><?= h($u['full_name']) ?></div>
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
            <td class="text-muted small"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td>
              <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$u['id'] ?>"
                 class="btn btn-sm btn-light rounded-pill" target="_blank">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── Right column ─────────────────────────────────────── -->
  <div class="col-lg-5">

    <!-- Top posts -->
    <div class="admin-card mb-4">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-trophy text-warning"></i> Top créations
        </h6>
        <a href="<?= SITE_URL ?>/admin/posts.php"
           class="btn btn-sm btn-outline-secondary rounded-pill">Voir toutes</a>
      </div>
      <div class="p-0">
        <?php foreach ($topPosts as $i => $p): ?>
          <div class="d-flex align-items-center gap-3 px-3 py-2
                      <?= $i < count($topPosts) - 1 ? 'border-bottom' : '' ?>">
            <span class="fw-black"
                  style="font-size:1.1rem;color:<?= $i === 0 ? '#f4b942' : ($i === 1 ? '#9e9e9e' : ($i === 2 ? '#cd7f32' : '#ddd')) ?>">
              #<?= $i + 1 ?>
            </span>
            <img src="<?= UPLOAD_URL_POSTS . h($p['image']) ?>"
                 class="tbl-thumb" alt="">
            <div class="flex-grow-1 overflow-hidden">
              <div class="fw-semibold text-truncate" style="font-size:.85rem">
                <?= h($p['title']) ?>
              </div>
              <div class="text-muted" style="font-size:.75rem">
                @<?= h($p['username']) ?>
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="fw-bold text-danger"><?= (int)$p['likes'] ?></div>
              <div class="text-muted" style="font-size:.7rem">likes</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Recent activity feed -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-activity text-success"></i> Activité récente
        </h6>
      </div>
      <div class="px-3 py-1">
        <?php foreach ($recentActivity as $act):
          $dotColors = ['like' => '#c9184a', 'comment' => '#1565c0', 'register' => '#2e7d32'];
          $dot = $dotColors[$act['type']] ?? '#888';
        ?>
          <div class="activity-item">
            <img src="<?= avatarUrlFromFilename($act['avatar'] ?? null) ?>"
                 style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0">
            <div class="flex-grow-1">
              <div style="font-size:.82rem"><?= h($act['msg']) ?></div>
              <div class="text-muted" style="font-size:.72rem">
                <?= timeAgo($act['created_at']) ?>
              </div>
            </div>
            <div class="activity-dot" style="background:<?= $dot ?>"></div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($recentActivity)): ?>
          <p class="text-muted text-center small py-3">Aucune activité récente.</p>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /.col-lg-5 -->
</div><!-- /.row -->

<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/admin_footer.php'; ?>
