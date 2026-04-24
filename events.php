<?php
// ============================================================
//  events.php — Events Listing
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Event.php';

$pageTitle  = 'Événements';
$activePage = 'events';

$eventModel = new Event();
$allEvents  = $eventModel->getAll();

// Separate upcoming vs past
$upcoming = array_filter($allEvents, fn($e) => strtotime($e['event_date']) >= time());
$past     = array_filter($allEvents, fn($e) => strtotime($e['event_date']) <  time());

// Type filter
$typeFilter = $_GET['type'] ?? '';
$types      = ['défilé','concours','atelier','exposition','autre'];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page header -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
  <div>
    <h1 class="mb-1" style="font-size:1.8rem">
      <i class="bi bi-calendar-event text-danger me-2"></i>Événements
    </h1>
    <p class="text-muted mb-0">Défilés, concours, ateliers — rejoignez la communauté !</p>
  </div>
  <?php if (isAdmin()): ?>
    <a href="<?= SITE_URL ?>/admin/create_event.php" class="btn btn-fashion">
      <i class="bi bi-plus-circle me-2"></i>Créer un événement
    </a>
  <?php endif; ?>
</div>

<!-- Type filter pills -->
<div class="d-flex gap-2 flex-wrap mb-4">
  <a href="<?= SITE_URL ?>/events.php"
     class="badge rounded-pill <?= $typeFilter === '' ? 'bg-danger' : 'bg-light text-dark border' ?> text-decoration-none px-3 py-2 fs-6">
    Tous
  </a>
  <?php foreach ($types as $t): ?>
    <a href="?type=<?= urlencode($t) ?>"
       class="badge rounded-pill <?= $typeFilter === $t ? 'bg-danger' : 'bg-light text-dark border' ?> text-decoration-none px-3 py-2 fs-6">
      <?= ucfirst($t) ?>
    </a>
  <?php endforeach; ?>
</div>

<!-- ── Upcoming Events ────────────────────────────────────── -->
<h2 class="section-title">
  <i class="bi bi-rocket-takeoff text-danger me-2"></i>À venir
</h2>

<?php
$filteredUpcoming = $typeFilter
    ? array_filter($upcoming, fn($e) => $e['type'] === $typeFilter)
    : $upcoming;
?>

<?php if (empty($filteredUpcoming)): ?>
  <div class="empty-state">
    <i class="bi bi-calendar-x d-block"></i>
    <p>Aucun événement à venir pour le moment.</p>
  </div>
<?php else: ?>
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
    <?php foreach ($filteredUpcoming as $event):
      $isParticipating = isLoggedIn() ? (new Event())->isParticipating((int)$event['id'], currentUserId()) : false;
    ?>
      <div class="col">
        <div class="event-card h-100">

          <!-- Event image -->
          <div class="position-relative overflow-hidden" style="height:190px">
            <img src="<?= SITE_URL ?>/assets/images/default_event.png"
                 alt="<?= h($event['title']) ?>"
                 class="w-100 h-100" style="object-fit:cover">
            <!-- Type badge -->
            <div class="position-absolute top-0 start-0 m-2">
              <span class="badge badge-event-<?= h($event['type']) ?> event-type-badge">
                <?= ucfirst(h($event['type'])) ?>
              </span>
            </div>
          </div>

          <div class="p-4 d-flex flex-column h-100">

            <!-- Title -->
            <h5 class="fw-bold mb-2">
              <a href="<?= SITE_URL ?>/event_detail.php?id=<?= (int)$event['id'] ?>"
                 class="text-decoration-none text-dark">
                <?= h($event['title']) ?>
              </a>
            </h5>

            <!-- Description excerpt -->
            <?php if (!empty($event['description'])): ?>
              <p class="text-muted small mb-3">
                <?= h(truncate($event['description'], 100)) ?>
              </p>
            <?php endif; ?>

            <div class="mt-auto">
              <!-- Meta info -->
              <div class="d-flex flex-column gap-1 mb-3">
                <div class="event-date-chip">
                  <i class="bi bi-calendar3 text-danger"></i>
                  <?= date('l d F Y à H\hi', strtotime($event['event_date'])) ?>
                </div>
                <?php if (!empty($event['location'])): ?>
                  <div class="event-date-chip">
                    <i class="bi bi-geo-alt text-danger"></i>
                    <?= h($event['location']) ?>
                  </div>
                <?php endif; ?>
                <div class="event-date-chip">
                  <i class="bi bi-people text-danger"></i>
                  <span class="participants-count" data-event-id="<?= (int)$event['id'] ?>">
                    <?= (int)$event['participant_count'] ?>
                  </span>
                  participant<?= $event['participant_count'] != 1 ? 's' : '' ?>
                  <?php if ($event['max_participants']): ?>
                    / <?= (int)$event['max_participants'] ?> places
                  <?php endif; ?>
                </div>
              </div>

              <!-- Participate button -->
              <?php if (isLoggedIn()): ?>
                <button class="btn btn-participate w-100 <?= $isParticipating ? 'btn-success' : 'btn-fashion' ?>"
                        data-event-id="<?= (int)$event['id'] ?>">
                  <?php if ($isParticipating): ?>
                    <i class="bi bi-check-circle-fill me-2"></i>Inscrit(e) !
                  <?php else: ?>
                    <i class="bi bi-plus-circle me-2"></i>Participer
                  <?php endif; ?>
                </button>
              <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-fashion w-100">
                  <i class="bi bi-box-arrow-in-right me-2"></i>Connexion pour participer
                </a>
              <?php endif; ?>

              <a href="<?= SITE_URL ?>/event_detail.php?id=<?= (int)$event['id'] ?>"
                 class="btn btn-sm btn-outline-secondary w-100 mt-2 rounded-pill">
                Voir les détails
              </a>

            </div>
          </div>

        </div><!-- /.event-card -->
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- ── Past Events ────────────────────────────────────────── -->
<?php
$filteredPast = $typeFilter
    ? array_filter($past, fn($e) => $e['type'] === $typeFilter)
    : $past;
if (!empty($filteredPast)):
?>
<h2 class="section-title">
  <i class="bi bi-clock-history text-muted me-2"></i>Événements passés
</h2>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
  <?php foreach ($filteredPast as $event): ?>
    <div class="col">
      <div class="event-card opacity-75 h-100">
        <div class="p-3 d-flex gap-3 align-items-center">
          <div class="text-center rounded-3 p-2 flex-shrink-0"
               style="background:#f0f0f0; min-width:50px;">
            <div style="font-size:1.2rem; font-weight:800; color:#666; line-height:1">
              <?= date('d', strtotime($event['event_date'])) ?>
            </div>
            <div style="font-size:.65rem; color:#666; text-transform:uppercase; font-weight:700">
              <?= date('M Y', strtotime($event['event_date'])) ?>
            </div>
          </div>
          <div>
            <a href="<?= SITE_URL ?>/event_detail.php?id=<?= (int)$event['id'] ?>"
               class="fw-semibold text-decoration-none text-dark d-block">
              <?= h(truncate($event['title'], 45)) ?>
            </a>
            <div class="text-muted small">
              <span class="badge badge-event-<?= h($event['type']) ?> me-1"><?= h($event['type']) ?></span>
              <?= (int)$event['participant_count'] ?> participants
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- CSRF meta -->
<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/includes/footer.php'; ?>
