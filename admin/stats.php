<?php
// ============================================================
//  admin/stats.php — Platform Statistics
// ============================================================

require_once __DIR__ . '/admin_guard.php';

$pageTitle  = 'Statistiques';
$activePage = 'stats';
$breadcrumb = 'Statistiques';

$db = getDB();

// ── Global counts ─────────────────────────────────────────────
$totalUsers    = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalPosts    = (int)$db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
$totalLikes    = (int)$db->query('SELECT COUNT(*) FROM likes')->fetchColumn();
$totalComments = (int)$db->query('SELECT COUNT(*) FROM comments')->fetchColumn();
$totalEvents   = (int)$db->query('SELECT COUNT(*) FROM events')->fetchColumn();
$totalPartic   = (int)$db->query('SELECT COUNT(*) FROM event_participants')->fetchColumn();
$totalNotifs   = (int)$db->query('SELECT COUNT(*) FROM notifications')->fetchColumn();

// ── Registrations last 7 days (daily) ───────────────────────
$registrations = $db->query(
    "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
     FROM users
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     GROUP BY DATE(created_at)
     ORDER BY day ASC"
)->fetchAll();

// ── Posts last 7 days ────────────────────────────────────────
$postsPerDay = $db->query(
    "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
     FROM posts
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     GROUP BY DATE(created_at)
     ORDER BY day ASC"
)->fetchAll();

// ── Posts by category ────────────────────────────────────────
$postsByCategory = $db->query(
    "SELECT category, COUNT(*) AS cnt
     FROM posts
     GROUP BY category
     ORDER BY cnt DESC"
)->fetchAll();

// ── Events by type ───────────────────────────────────────────
$eventsByType = $db->query(
    "SELECT type, COUNT(*) AS cnt
     FROM events
     GROUP BY type
     ORDER BY cnt DESC"
)->fetchAll();

// ── Top 10 authors (most posts) ──────────────────────────────
$topAuthors = $db->query(
    "SELECT u.id, u.username, u.full_name, u.avatar,
            COUNT(p.id) AS post_count,
            SUM((SELECT COUNT(*) FROM likes WHERE post_id = p.id)) AS total_likes
     FROM users u
     LEFT JOIN posts p ON p.user_id = u.id
     GROUP BY u.id
     HAVING post_count > 0
     ORDER BY post_count DESC
     LIMIT 10"
)->fetchAll();

// ── Most popular events ──────────────────────────────────────
$topEvents = $db->query(
    "SELECT e.id, e.title, e.type, e.event_date,
            COUNT(ep.id) AS participant_count
     FROM events e
     LEFT JOIN event_participants ep ON ep.event_id = e.id
     GROUP BY e.id
     ORDER BY participant_count DESC
     LIMIT 5"
)->fetchAll();

// ── Engagement rate ──────────────────────────────────────────
$avgLikesPerPost    = $totalPosts ? round($totalLikes    / $totalPosts, 1) : 0;
$avgCommentsPerPost = $totalPosts ? round($totalComments / $totalPosts, 1) : 0;

// Build JS arrays for charts
$regLabels  = json_encode(array_column($registrations, 'day'));
$regData    = json_encode(array_column($registrations, 'cnt'));
$postLabels = json_encode(array_column($postsPerDay,   'day'));
$postData   = json_encode(array_column($postsPerDay,   'cnt'));

$catLabels  = json_encode(array_column($postsByCategory, 'category'));
$catData    = json_encode(array_column($postsByCategory, 'cnt'));

$evtLabels  = json_encode(array_column($eventsByType, 'type'));
$evtData    = json_encode(array_column($eventsByType, 'cnt'));

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">
      <i class="bi bi-bar-chart-line text-danger me-2"></i>Statistiques
    </h1>
    <p class="admin-page-subtitle">Tableau de bord analytique — mis à jour en temps réel</p>
  </div>
  <div class="text-muted small">
    <i class="bi bi-clock me-1"></i><?= date('d/m/Y à H:i') ?>
  </div>
</div>

<!-- ── KPI Cards ──────────────────────────────────────────── -->
<div class="row g-3 mb-4">
  <?php
  $kpis = [
    ['icon' => 'bi-people-fill',          'label' => 'Utilisateurs',    'value' => $totalUsers,    'color' => '#c9184a,#ff6b9d'],
    ['icon' => 'bi-images',               'label' => 'Publications',    'value' => $totalPosts,    'color' => '#7b1fa2,#ba68c8'],
    ['icon' => 'bi-heart-fill',           'label' => 'Likes',           'value' => $totalLikes,    'color' => '#e91e63,#f48fb1'],
    ['icon' => 'bi-chat-fill',            'label' => 'Commentaires',    'value' => $totalComments, 'color' => '#1565c0,#42a5f5'],
    ['icon' => 'bi-calendar-event-fill',  'label' => 'Événements',      'value' => $totalEvents,   'color' => '#2e7d32,#66bb6a'],
    ['icon' => 'bi-ticket-perforated',    'label' => 'Inscriptions',    'value' => $totalPartic,   'color' => '#e65100,#ffa726'],
  ];
  foreach ($kpis as $k):
    [$cs, $ce] = explode(',', $k['color']);
  ?>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="admin-stat-card text-center" style="--accent-start:<?= $cs ?>;--accent-end:<?= $ce ?>">
        <i class="bi <?= $k['icon'] ?>" style="font-size:1.6rem;color:<?= $cs ?>;display:block;margin-bottom:.4rem"></i>
        <div class="admin-stat-value" style="font-size:1.7rem" data-target="<?= $k['value'] ?>">
          <?= number_format($k['value'], 0, ',', '\u00a0') ?>
        </div>
        <div class="admin-stat-label"><?= $k['label'] ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ── Engagement KPIs ────────────────────────────────────── -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="admin-card p-4 text-center">
      <div style="font-size:2rem;font-weight:800;color:#c9184a"><?= $avgLikesPerPost ?></div>
      <div class="text-muted small">Likes moyens par publication</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="admin-card p-4 text-center">
      <div style="font-size:2rem;font-weight:800;color:#1565c0"><?= $avgCommentsPerPost ?></div>
      <div class="text-muted small">Commentaires moyens par publication</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="admin-card p-4 text-center">
      <div style="font-size:2rem;font-weight:800;color:#2e7d32">
        <?= $totalEvents ? round($totalPartic / $totalEvents, 1) : 0 ?>
      </div>
      <div class="text-muted small">Participants moyens par événement</div>
    </div>
  </div>
</div>

<!-- ── Charts row ─────────────────────────────────────────── -->
<div class="row g-4 mb-4">

  <!-- Registrations chart -->
  <div class="col-lg-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-person-plus text-danger"></i> Inscriptions — 7 derniers jours
        </h6>
      </div>
      <div class="p-4">
        <canvas id="regChart" height="160"></canvas>
      </div>
    </div>
  </div>

  <!-- Posts chart -->
  <div class="col-lg-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-images text-danger"></i> Publications — 7 derniers jours
        </h6>
      </div>
      <div class="p-4">
        <canvas id="postChart" height="160"></canvas>
      </div>
    </div>
  </div>

  <!-- Category pie -->
  <div class="col-lg-5">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-pie-chart text-danger"></i> Répartition par catégorie
        </h6>
      </div>
      <div class="p-4 d-flex justify-content-center">
        <canvas id="catChart" style="max-height:240px;max-width:240px"></canvas>
      </div>
    </div>
  </div>

  <!-- Events type -->
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-calendar2-week text-danger"></i> Événements par type
        </h6>
      </div>
      <div class="p-4">
        <canvas id="evtChart" height="160"></canvas>
      </div>
    </div>
  </div>

</div><!-- /.charts -->

<!-- ── Top authors + Top events ───────────────────────────── -->
<div class="row g-4">

  <!-- Top authors -->
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-trophy text-warning"></i> Top créateurs
        </h6>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Étudiant</th>
            <th>Publications</th>
            <th>Likes reçus</th>
            <th>Ratio</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($topAuthors as $i => $a): ?>
            <tr>
              <td>
                <span style="font-weight:800;font-size:1rem;color:<?= $i === 0 ? '#f4b942' : ($i === 1 ? '#9e9e9e' : ($i === 2 ? '#cd7f32' : '#ccc')) ?>">
                  #<?= $i + 1 ?>
                </span>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= avatarUrlFromFilename($a['avatar'] ?? null) ?>"
                       class="tbl-avatar" alt="">
                  <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$a['id'] ?>"
                     target="_blank" class="fw-semibold text-dark text-decoration-none">
                    <?= h($a['full_name']) ?>
                  </a>
                </div>
              </td>
              <td class="fw-semibold"><?= (int)$a['post_count'] ?></td>
              <td>
                <span class="text-danger fw-bold">
                  <i class="bi bi-heart-fill me-1" style="font-size:.7rem"></i>
                  <?= (int)($a['total_likes'] ?? 0) ?>
                </span>
              </td>
              <td class="text-muted small">
                <?= $a['post_count'] ? round(($a['total_likes'] ?? 0) / $a['post_count'], 1) : 0 ?> /post
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top events -->
  <div class="col-lg-5">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-calendar-heart text-danger"></i> Événements populaires
        </h6>
      </div>
      <div class="p-0">
        <?php foreach ($topEvents as $i => $ev): ?>
          <div class="d-flex align-items-center gap-3 px-4 py-3
                      <?= $i < count($topEvents)-1 ? 'border-bottom' : '' ?>">
            <span style="font-size:1.1rem;font-weight:800;color:<?= $i===0?'#f4b942':($i===1?'#9e9e9e':($i===2?'#cd7f32':'#ccc')) ?>">
              #<?= $i+1 ?>
            </span>
            <div class="flex-grow-1 overflow-hidden">
              <a href="<?= SITE_URL ?>/event_detail.php?id=<?= (int)$ev['id'] ?>"
                 target="_blank"
                 class="fw-semibold text-dark text-decoration-none d-block text-truncate">
                <?= h($ev['title']) ?>
              </a>
              <div class="text-muted" style="font-size:.74rem">
                <span class="badge badge-event-<?= h($ev['type']) ?> me-1"><?= h($ev['type']) ?></span>
                <?= date('d/m/Y', strtotime($ev['event_date'])) ?>
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="fw-bold text-success"><?= (int)$ev['participant_count'] ?></div>
              <div class="text-muted" style="font-size:.7rem">participants</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div><!-- /.row -->

<!-- Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const palette = ['#c9184a','#ff6b9d','#7b1fa2','#1565c0','#2e7d32','#e65100','#f4b942','#00838f'];

// ── Helper: line chart ──────────────────────────────────────
function lineChart(id, labels, data, label, color) {
  new Chart(document.getElementById(id), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label,
        data,
        borderColor: color,
        backgroundColor: color + '22',
        fill: true,
        tension: .4,
        pointBackgroundColor: color,
        pointRadius: 5,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
        x: { grid: { display: false } }
      }
    }
  });
}

// ── Helper: doughnut chart ──────────────────────────────────
function doughnutChart(id, labels, data) {
  new Chart(document.getElementById(id), {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{ data, backgroundColor: palette, borderWidth: 2 }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 11 } } }
      }
    }
  });
}

// ── Helper: bar chart ───────────────────────────────────────
function barChart(id, labels, data, label, color) {
  new Chart(document.getElementById(id), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label,
        data,
        backgroundColor: color,
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
        x: { grid: { display: false } }
      }
    }
  });
}

// ── Render charts ───────────────────────────────────────────
lineChart('regChart',  <?= $regLabels  ?>, <?= $regData  ?>, 'Inscriptions', '#c9184a');
lineChart('postChart', <?= $postLabels ?>, <?= $postData ?>, 'Publications',  '#7b1fa2');
doughnutChart('catChart', <?= $catLabels ?>, <?= $catData ?>);
barChart('evtChart', <?= $evtLabels ?>, <?= $evtData ?>, 'Événements', '#2e7d32');
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
