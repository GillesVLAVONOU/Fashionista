<?php
// ============================================================
//  admin/edit_event.php — Edit an Existing Event
// ============================================================

require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../models/Event.php';

$pageTitle  = 'Modifier un événement';
$activePage = 'events';
$breadcrumb = 'Modifier un événement';

$eventId    = (int)($_GET['id'] ?? 0);
$eventModel = new Event();
$event      = $eventModel->findById($eventId);

if (!$event) {
    setFlash('error', 'Événement introuvable.');
    redirect(SITE_URL . '/admin/events.php');
}

$db = getDB();

// ── Handle form submission ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de sécurité invalide.');
        redirect(SITE_URL . '/admin/edit_event.php?id=' . $eventId);
    }

    $title           = trim($_POST['title']       ?? '');
    $description     = trim($_POST['description'] ?? '');
    $location        = trim($_POST['location']    ?? '');
    $eventDate       = trim($_POST['event_date']  ?? '');
    $eventTime       = trim($_POST['event_time']  ?? '00:00');
    $type            = $_POST['type']             ?? 'autre';
    $maxPart         = (int)($_POST['max_participants'] ?? 0);

    $errors = [];
    if (empty($title))     $errors[] = 'Le titre est obligatoire.';
    if (empty($eventDate)) $errors[] = 'La date est obligatoire.';

    $fullDateTime = $eventDate . ' ' . $eventTime . ':00';

    // Optional image update
    $imageFilename = $event['image'];
    if (!empty($_FILES['image']['name'])) {
        try {
            $imageFilename = uploadImage($_FILES['image'], UPLOAD_POSTS);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare(
            'UPDATE events SET title=?, description=?, image=?, location=?,
             event_date=?, type=?, max_participants=? WHERE id=?'
        );
        $stmt->execute([
            $title,
            $description,
            $imageFilename,
            $location,
            $fullDateTime,
            $type,
            $maxPart > 0 ? $maxPart : null,
            $eventId,
        ]);
        setFlash('success', 'Événement mis à jour avec succès.');
        redirect(SITE_URL . '/admin/events.php');
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}

require_once __DIR__ . '/admin_header.php';

// Pre-fill from existing event
$evDate = date('Y-m-d', strtotime($event['event_date']));
$evTime = date('H:i',   strtotime($event['event_date']));
?>

<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">
      <i class="bi bi-pencil-square text-danger me-2"></i>Modifier l'événement
    </h1>
    <p class="admin-page-subtitle">ID #<?= $eventId ?> · Créé par @<?= h($event['creator_name']) ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= SITE_URL ?>/event_detail.php?id=<?= $eventId ?>"
       target="_blank"
       class="btn btn-outline-secondary rounded-pill">
      <i class="bi bi-eye me-1"></i>Voir la page
    </a>
    <a href="<?= SITE_URL ?>/admin/events.php"
       class="btn btn-light rounded-pill">
      <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
  </div>
</div>

<!-- Stats bar -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="admin-stat-card" style="--accent-start:#2e7d32;--accent-end:#66bb6a">
      <i class="bi bi-people admin-stat-icon"></i>
      <div class="admin-stat-value"><?= (int)$event['participant_count'] ?></div>
      <div class="admin-stat-label">Participants inscrits</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="admin-stat-card" style="--accent-start:#1565c0;--accent-end:#42a5f5">
      <i class="bi bi-calendar-check admin-stat-icon"></i>
      <div class="admin-stat-value">
        <?= $event['max_participants'] ? h($event['max_participants']) : '∞' ?>
      </div>
      <div class="admin-stat-label">Places max</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="admin-stat-card" style="--accent-start:#c9184a;--accent-end:#ff6b9d">
      <i class="bi bi-hourglass-split admin-stat-icon"></i>
      <div class="admin-stat-value" style="font-size:1.2rem">
        <?= strtotime($event['event_date']) > time() ? timeAgo($event['event_date']) : 'Terminé' ?>
      </div>
      <div class="admin-stat-label">Statut</div>
    </div>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <h6 class="admin-card-title">
      <i class="bi bi-calendar-event text-danger"></i> Modifier les informations
    </h6>
  </div>

  <div class="p-4">
    <form action="<?= SITE_URL ?>/admin/edit_event.php?id=<?= $eventId ?>"
          method="POST"
          enctype="multipart/form-data"
          novalidate>
      <?= csrfField() ?>

      <div class="row g-4">

        <div class="col-12">
          <label for="title" class="form-label fw-semibold">
            Titre <span class="text-danger">*</span>
          </label>
          <input type="text" id="title" name="title"
                 class="form-control form-control-lg"
                 maxlength="200"
                 value="<?= h($event['title']) ?>"
                 required>
        </div>

        <div class="col-md-6">
          <label for="type" class="form-label fw-semibold">Type</label>
          <select id="type" name="type" class="form-select">
            <?php foreach (['défilé','concours','atelier','exposition','autre'] as $t): ?>
              <option value="<?= $t ?>" <?= $event['type'] === $t ? 'selected' : '' ?>>
                <?= ucfirst($t) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label for="max_participants" class="form-label fw-semibold">
            Places max <span class="text-muted fw-normal">(0 = illimité)</span>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-people"></i></span>
            <input type="number" id="max_participants" name="max_participants"
                   class="form-control" min="0"
                   value="<?= (int)($event['max_participants'] ?? 0) ?>">
          </div>
        </div>

        <div class="col-md-6">
          <label for="event_date" class="form-label fw-semibold">
            Date <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
            <input type="date" id="event_date" name="event_date"
                   class="form-control"
                   value="<?= h($evDate) ?>"
                   required>
          </div>
        </div>

        <div class="col-md-6">
          <label for="event_time" class="form-label fw-semibold">Heure</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-clock"></i></span>
            <input type="time" id="event_time" name="event_time"
                   class="form-control"
                   value="<?= h($evTime) ?>">
          </div>
        </div>

        <div class="col-12">
          <label for="location" class="form-label fw-semibold">Lieu</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
            <input type="text" id="location" name="location"
                   class="form-control"
                   maxlength="255"
                   value="<?= h($event['location'] ?? '') ?>">
          </div>
        </div>

        <div class="col-12">
          <label for="description" class="form-label fw-semibold">Description</label>
          <textarea id="description" name="description"
                    class="form-control" rows="6"
                    maxlength="3000"><?= h($event['description'] ?? '') ?></textarea>
        </div>

        <!-- Current image -->
        <div class="col-12">
          <label class="form-label fw-semibold">Image de bannière</label>
          <div class="d-flex gap-3 align-items-start flex-wrap">
            <div>
              <p class="text-muted small mb-1">Image actuelle :</p>
              <img src="<?= UPLOAD_URL_POSTS . h($event['image']) ?>"
                   alt="Bannière actuelle"
                   style="height:100px; border-radius:8px; object-fit:cover; border:1px solid #dee2e6">
            </div>
            <div class="flex-grow-1">
              <p class="text-muted small mb-1">Remplacer par :</p>
              <input type="file" name="image" class="form-control" accept="image/*"
                     id="imageInput">
              <img id="imagePreview" src="" alt=""
                   style="display:none;max-height:100px;border-radius:8px;margin-top:.5rem">
            </div>
          </div>
        </div>

        <div class="col-12 d-flex gap-3 justify-content-end">
          <a href="<?= SITE_URL ?>/admin/events.php"
             class="btn btn-light rounded-pill px-4">Annuler</a>
          <button type="submit" class="btn btn-fashion px-5 rounded-pill">
            <i class="bi bi-check-lg me-2"></i>Enregistrer les modifications
          </button>
        </div>

      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('imageInput')?.addEventListener('change', function () {
  const file    = this.files[0];
  const preview = document.getElementById('imagePreview');
  if (file) {
    const r = new FileReader();
    r.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    r.readAsDataURL(file);
  }
});
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
