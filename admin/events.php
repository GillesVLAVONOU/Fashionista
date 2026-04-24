<?php
// ============================================================
//  admin/events.php — Event Management
// ============================================================

require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../models/Event.php';

$pageTitle  = 'Événements';
$activePage = 'events';
$breadcrumb = 'Événements';

$db = getDB();

// ── Delete event ──────────────────────────────────────────────
if (isset($_GET['action'], $_GET['event_id']) && $_GET['action'] === 'delete'
    && verifyCsrf($_GET['csrf'] ?? '')) {
    $db->prepare('DELETE FROM events WHERE id = ?')->execute([(int)$_GET['event_id']]);
    setFlash('success', 'Événement supprimé.');
    redirect(SITE_URL . '/admin/events.php');
}

// ── Filters ──────────────────────────────────────────────────
$typeFilter = $_GET['type']   ?? '';
$period     = $_GET['period'] ?? 'all'; // upcoming | past | all
$search     = trim($_GET['q'] ?? '');

$where  = 'WHERE 1=1';
$params = [];

if ($typeFilter) {
    $where   .= ' AND e.type = ?';
    $params[] = $typeFilter;
}
if ($period === 'upcoming') {
    $where .= ' AND e.event_date >= NOW()';
} elseif ($period === 'past') {
    $where .= ' AND e.event_date < NOW()';
}
if ($search) {
    $where   .= ' AND (e.title LIKE ? OR e.location LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = array_merge($params, [$like, $like]);
}

$stmtC = $db->prepare(
    "SELECT COUNT(*) FROM events e JOIN users u ON e.created_by = u.id $where"
);
$stmtC->execute($params);
$total = (int)$stmtC->fetchColumn();

$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$pages   = (int)ceil($total / $perPage);

$stmt = $db->prepare(
    "SELECT e.*,
            u.username AS creator_name,
            (SELECT COUNT(*) FROM event_participants WHERE event_id = e.id) AS participant_count
     FROM events e
     JOIN users u ON e.created_by = u.id
     $where
     ORDER BY e.event_date DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Counts for tabs
$totalUpcoming = (int)$db->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW()")->fetchColumn();
$totalPast     = (int)$db->query("SELECT COUNT(*) FROM events WHERE event_date < NOW()")->fetchColumn();

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">
      <i class="bi bi-calendar-event text-danger me-2"></i>Événements
    </h1>
    <p class="admin-page-subtitle"><?= $total ?> événements · <?= $totalUpcoming ?> à venir · <?= $totalPast ?> passés</p>
  </div>
  <a href="<?= SITE_URL ?>/admin/create_event.php" class="btn btn-fashion rounded-pill">
    <i class="bi bi-plus-circle me-2"></i>Créer un événement
  </a>
</div>

<!-- Period tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
  <?php foreach (['all' => 'Tous', 'upcoming' => 'À venir', 'past' => 'Passés'] as $val => $lbl): ?>
    <a href="?period=<?= $val ?>&type=<?= urlencode($typeFilter) ?>&q=<?= urlencode($search) ?>"
       class="btn btn-sm <?= $period === $val ? 'btn-danger' : 'btn-outline-secondary' ?> rounded-pill">
      <?= $lbl ?>
      <?php if ($val === 'upcoming'): ?>
        <span class="badge <?= $period === 'upcoming' ? 'bg-white text-danger' : 'bg-secondary' ?> ms-1"><?= $totalUpcoming ?></span>
      <?php elseif ($val === 'past'): ?>
        <span class="badge <?= $period === 'past' ? 'bg-white text-danger' : 'bg-secondary' ?> ms-1"><?= $totalPast ?></span>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <!-- Toolbar -->
  <div class="admin-card-header">
    <h6 class="admin-card-title">
      <i class="bi bi-list text-danger"></i> Liste des événements
    </h6>
    <div class="admin-toolbar">
      <form method="GET" class="admin-search">
        <i class="bi bi-search"></i>
        <input type="text" name="q" id="tableSearch"
               value="<?= h($search) ?>"
               placeholder="Titre, lieu…"
               class="form-control form-control-sm">
        <input type="hidden" name="period" value="<?= h($period) ?>">
        <input type="hidden" name="type"   value="<?= h($typeFilter) ?>">
      </form>
      <select class="form-select form-select-sm w-auto rounded-pill"
              onchange="window.location.href='?type='+this.value+'&period=<?= h($period) ?>&q=<?= urlencode($search) ?>'">
        <option value="">Tous types</option>
        <?php foreach (['défilé','concours','atelier','exposition','autre'] as $t): ?>
          <option value="<?= $t ?>" <?= $typeFilter === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Titre</th>
          <th>Type</th>
          <th>Date</th>
          <th>Lieu</th>
          <th>Participants</th>
          <th>Créateur</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($events)): ?>
          <tr><td colspan="9" class="text-center text-muted py-5">Aucun événement trouvé.</td></tr>
        <?php endif; ?>
        <?php foreach ($events as $ev):
          $isPast = strtotime($ev['event_date']) < time();
          $isFull = $ev['max_participants'] && $ev['participant_count'] >= $ev['max_participants'];
        ?>
          <tr>
            <td class="text-muted small"><?= (int)$ev['id'] ?></td>
            <td>
              <a href="<?= SITE_URL ?>/event_detail.php?id=<?= (int)$ev['id'] ?>"
                 target="_blank"
                 class="fw-semibold text-dark text-decoration-none">
                <?= h(truncate($ev['title'], 42)) ?>
              </a>
            </td>
            <td>
              <span class="badge badge-event-<?= h($ev['type']) ?>">
                <?= ucfirst(h($ev['type'])) ?>
              </span>
            </td>
            <td class="text-muted small" style="white-space:nowrap">
              <?= date('d/m/Y', strtotime($ev['event_date'])) ?><br>
              <span style="font-size:.72rem"><?= date('H\hi', strtotime($ev['event_date'])) ?></span>
            </td>
            <td class="text-muted small"><?= h(truncate($ev['location'] ?? '', 30)) ?></td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <span class="fw-semibold"><?= (int)$ev['participant_count'] ?></span>
                <?php if ($ev['max_participants']): ?>
                  <span class="text-muted small">/ <?= (int)$ev['max_participants'] ?></span>
                <?php endif; ?>
                <?php if ($isFull): ?>
                  <span class="badge badge-status-banned ms-1" style="font-size:.65rem">Complet</span>
                <?php endif; ?>
              </div>
              <?php if ($ev['max_participants']): ?>
                <?php $pct = min(100, round(($ev['participant_count'] / $ev['max_participants']) * 100)); ?>
                <div class="progress mt-1" style="height:4px;width:80px">
                  <div class="progress-bar <?= $pct >= 100 ? 'bg-danger' : 'bg-success' ?>"
                       style="width:<?= $pct ?>%"></div>
                </div>
              <?php endif; ?>
            </td>
            <td class="text-muted small">@<?= h($ev['creator_name']) ?></td>
            <td>
              <?php if ($isPast): ?>
                <span class="badge bg-secondary">Terminé</span>
              <?php else: ?>
                <span class="badge badge-status-active">En cours</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= SITE_URL ?>/event_detail.php?id=<?= (int)$ev['id'] ?>"
                   class="btn btn-sm btn-light rounded-pill"
                   target="_blank" title="Voir">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="<?= SITE_URL ?>/admin/edit_event.php?id=<?= (int)$ev['id'] ?>"
                   class="btn btn-sm btn-outline-primary rounded-pill"
                   title="Modifier">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="?action=delete&event_id=<?= (int)$ev['id'] ?>&csrf=<?= csrfToken() ?>&period=<?= h($period) ?>"
                   class="btn btn-sm btn-outline-danger rounded-pill"
                   title="Supprimer"
                   data-confirm="Supprimer l'événement « <?= h($ev['title']) ?> » et toutes ses inscriptions ?">
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
      <nav><ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page-1 ?>&period=<?= h($period) ?>&type=<?= urlencode($typeFilter) ?>&q=<?= urlencode($search) ?>">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&period=<?= h($period) ?>&type=<?= urlencode($typeFilter) ?>&q=<?= urlencode($search) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page+1 ?>&period=<?= h($period) ?>&type=<?= urlencode($typeFilter) ?>&q=<?= urlencode($search) ?>">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>
      </ul></nav>
    </div>
  <?php endif; ?>

</div><!-- /.admin-card -->

<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/admin_footer.php'; ?>
