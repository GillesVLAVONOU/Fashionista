<?php
// ============================================================
//  event_detail.php — Single Event Detail
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Event.php';

$eventId    = (int)($_GET['id'] ?? 0);
$eventModel = new Event();
$event      = $eventModel->findById($eventId);

if (!$event) {
    setFlash('error', 'Événement introuvable.');
    redirect(SITE_URL . '/events.php');
}

$participants   = $eventModel->getParticipants($eventId);
$isParticipating = isLoggedIn() ? $eventModel->isParticipating($eventId, currentUserId()) : false;
$isPast         = strtotime($event['event_date']) < time();
$isFull         = $event['max_participants'] && $event['participant_count'] >= $event['max_participants'];

$pageTitle  = h($event['title']);
$activePage = 'events';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Back -->
<a href="<?= SITE_URL ?>/events.php"
   class="btn btn-light btn-sm rounded-pill mb-4">
  <i class="bi bi-arrow-left me-1"></i>Retour aux événements
</a>

<div class="row g-4">

  <!-- ── Event main content ─────────────────────────────── -->
  <div class="col-lg-8">

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
      <!-- Event image / banner -->
      <div class="position-relative" style="height:260px; background: linear-gradient(135deg,#1a1a2e,#16213e)">
        <div class="d-flex align-items-center justify-content-center h-100">
          <i class="bi bi-calendar-event" style="font-size:5rem; color:rgba(255,255,255,.2)"></i>
        </div>
        <!-- Type badge -->
        <div class="position-absolute top-0 start-0 m-3">
          <span class="badge badge-event-<?= h($event['type']) ?> event-type-badge fs-6 px-3 py-2">
            <?= ucfirst(h($event['type'])) ?>
          </span>
        </div>
        <?php if ($isPast): ?>
          <div class="position-absolute top-0 end-0 m-3">
            <span class="badge bg-secondary fs-6 px-3 py-2">Événement passé</span>
          </div>
        <?php endif; ?>
      </div>

      <div class="card-body p-4 p-md-5">

        <h1 class="mb-3"><?= h($event['title']) ?></h1>

        <!-- Meta info grid -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6">
            <div class="d-flex gap-2 align-items-center">
              <div class="rounded-3 p-2" style="background:#fce4ec;">
                <i class="bi bi-calendar3 text-danger fs-5"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:.75rem">Date & heure</div>
                <div class="fw-semibold">
                  <?= date('l d F Y', strtotime($event['event_date'])) ?>
                  à <?= date('H\hi', strtotime($event['event_date'])) ?>
                </div>
              </div>
            </div>
          </div>

          <?php if (!empty($event['location'])): ?>
            <div class="col-sm-6">
              <div class="d-flex gap-2 align-items-center">
                <div class="rounded-3 p-2" style="background:#e3f2fd;">
                  <i class="bi bi-geo-alt text-primary fs-5"></i>
                </div>
                <div>
                  <div class="text-muted" style="font-size:.75rem">Lieu</div>
                  <div class="fw-semibold"><?= h($event['location']) ?></div>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <div class="col-sm-6">
            <div class="d-flex gap-2 align-items-center">
              <div class="rounded-3 p-2" style="background:#e8f5e9;">
                <i class="bi bi-people text-success fs-5"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:.75rem">Participants</div>
                <div class="fw-semibold">
                  <span class="participants-count" data-event-id="<?= (int)$event['id'] ?>">
                    <?= (int)$event['participant_count'] ?>
                  </span>
                  <?php if ($event['max_participants']): ?>
                    / <?= (int)$event['max_participants'] ?> places
                  <?php else: ?>
                    inscrits
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="d-flex gap-2 align-items-center">
              <div class="rounded-3 p-2" style="background:#f3e5f5;">
                <i class="bi bi-person-badge text-purple fs-5" style="color:#7b1fa2"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:.75rem">Organisateur</div>
                <div class="fw-semibold"><?= h($event['creator_fullname'] ?? $event['creator_name']) ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Capacity progress bar -->
        <?php if ($event['max_participants']): ?>
          <?php $pct = min(100, round(($event['participant_count'] / $event['max_participants']) * 100)); ?>
          <div class="mb-4">
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span>Places réservées</span>
              <span><?= $pct ?>%</span>
            </div>
            <div class="progress" style="height:8px; border-radius:4px">
              <div class="progress-bar <?= $pct >= 100 ? 'bg-danger' : 'bg-success' ?>"
                   role="progressbar"
                   style="width:<?= $pct ?>%"></div>
            </div>
            <?php if ($isFull): ?>
              <div class="text-danger small mt-1 fw-semibold">
                <i class="bi bi-exclamation-triangle me-1"></i>Cet événement est complet.
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Description -->
        <?php if (!empty($event['description'])): ?>
          <div class="mb-4">
            <h5 class="fw-bold mb-2">À propos</h5>
            <div style="line-height:1.75; color:#444;">
              <?= nl2br(h($event['description'])) ?>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <!-- ── Sidebar ────────────────────────────────────────── -->
  <div class="col-lg-4">

    <!-- Participation card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top:80px;">
      <div class="card-body p-4">

        <?php if (!$isPast): ?>
          <h5 class="fw-bold mb-3">
            <i class="bi bi-ticket-perforated text-danger me-2"></i>Inscription
          </h5>

          <?php if (isLoggedIn()): ?>
            <?php if ($isParticipating): ?>
              <div class="alert alert-success rounded-3 mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                Vous êtes inscrit(e) à cet événement !
              </div>
            <?php endif; ?>

            <button class="btn btn-participate w-100 py-2 mb-2
                    <?= $isParticipating ? 'btn-success' : ($isFull ? 'btn-secondary disabled' : 'btn-fashion') ?>"
                    data-event-id="<?= (int)$event['id'] ?>"
                    <?= $isFull && !$isParticipating ? 'disabled' : '' ?>>
              <?php if ($isFull && !$isParticipating): ?>
                <i class="bi bi-x-circle me-2"></i>Complet
              <?php elseif ($isParticipating): ?>
                <i class="bi bi-check-circle-fill me-2"></i>Inscrit(e) — Annuler
              <?php else: ?>
                <i class="bi bi-plus-circle me-2"></i>Je participe !
              <?php endif; ?>
            </button>

          <?php else: ?>
            <p class="text-muted small mb-3">Connectez-vous pour vous inscrire à cet événement.</p>
            <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
               class="btn btn-fashion w-100">
              <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
            </a>
          <?php endif; ?>

          <div class="text-center text-muted small mt-2">
            <i class="bi bi-clock me-1"></i>
            Dans <?= timeAgo($event['event_date']) ?>
          </div>

        <?php else: ?>
          <div class="text-center py-3 text-muted">
            <i class="bi bi-clock-history fs-2 mb-2 d-block opacity-50"></i>
            <p class="mb-0">Cet événement est terminé.</p>
          </div>
        <?php endif; ?>

      </div>
    </div>

    <!-- Participants list -->
    <?php if (!empty($participants)): ?>
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
          <h6 class="fw-bold mb-3">
            <i class="bi bi-people text-danger me-2"></i>
            Participants (<?= count($participants) ?>)
          </h6>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach (array_slice($participants, 0, 15) as $p): ?>
              <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$p['id'] ?>"
                 title="<?= h($p['full_name']) ?>">
                <img src="<?= avatarUrlFromFilename($p['avatar'] ?? null) ?>"
                     alt="<?= h($p['username']) ?>"
                     style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.15)">
              </a>
            <?php endforeach; ?>
            <?php if (count($participants) > 15): ?>
              <div class="d-flex align-items-center justify-content-center"
                   style="width:38px;height:38px;border-radius:50%;background:#fce4ec;color:#c9184a;font-size:.75rem;font-weight:700">
                +<?= count($participants) - 15 ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div><!-- /.col-lg-4 -->
</div><!-- /.row -->

<!-- CSRF meta -->
<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/includes/footer.php'; ?>
