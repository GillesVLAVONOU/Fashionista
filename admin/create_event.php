<?php
// ============================================================
//  admin/create_event.php — Create a New Event (Admin only)
// ============================================================

require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../models/Event.php';

$pageTitle  = 'Créer un événement';
$activePage = 'create_event';
$breadcrumb = 'Créer un événement';

// ── Handle form submission ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de sécurité invalide.');
        redirect(SITE_URL . '/admin/create_event.php');
    }

    $title           = trim($_POST['title']           ?? '');
    $description     = trim($_POST['description']     ?? '');
    $location        = trim($_POST['location']        ?? '');
    $eventDate       = trim($_POST['event_date']      ?? '');
    $eventTime       = trim($_POST['event_time']      ?? '00:00');
    $type            = $_POST['type']                 ?? 'autre';
    $maxParticipants = (int)($_POST['max_participants'] ?? 0);

    $validTypes = ['défilé','concours','atelier','exposition','autre'];
    if (!in_array($type, $validTypes, true)) $type = 'autre';

    $errors = [];
    if (empty($title))      $errors[] = 'Le titre est obligatoire.';
    if (empty($eventDate))  $errors[] = 'La date de l\'événement est obligatoire.';
    if (!empty($eventDate) && strtotime($eventDate) === false)
        $errors[] = 'Date invalide.';

    // Combine date + time
    $fullDateTime = $eventDate . ' ' . $eventTime . ':00';

    // Handle optional image upload
    $imageFilename = 'default_event.png';
    if (!empty($_FILES['image']['name'])) {
        try {
            $imageFilename = uploadImage($_FILES['image'], UPLOAD_POSTS);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $eventModel = new Event();
        $eventId    = $eventModel->create([
            'title'            => $title,
            'description'      => $description,
            'image'            => $imageFilename,
            'location'         => $location,
            'event_date'       => $fullDateTime,
            'type'             => $type,
            'max_participants' => $maxParticipants > 0 ? $maxParticipants : null,
            'created_by'       => currentUserId(),
        ]);
        setFlash('success', 'Événement « ' . $title . ' » créé avec succès ! 🎉');
        redirect(SITE_URL . '/admin/events.php');
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">
      <i class="bi bi-calendar-plus text-danger me-2"></i>Créer un événement
    </h1>
    <p class="admin-page-subtitle">Publiez un défilé, concours, atelier ou exposition.</p>
  </div>
  <a href="<?= SITE_URL ?>/admin/events.php" class="btn btn-outline-secondary rounded-pill">
    <i class="bi bi-arrow-left me-1"></i>Retour aux événements
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-xl-8">

    <div class="admin-card">
      <div class="admin-card-header">
        <h6 class="admin-card-title">
          <i class="bi bi-pencil-square text-danger"></i> Informations de l'événement
        </h6>
      </div>

      <div class="p-4">
        <form action="<?= SITE_URL ?>/admin/create_event.php"
              method="POST"
              enctype="multipart/form-data"
              novalidate>
          <?= csrfField() ?>

          <div class="row g-4">

            <!-- Title -->
            <div class="col-12">
              <label for="title" class="form-label fw-semibold">
                Titre <span class="text-danger">*</span>
              </label>
              <input type="text" id="title" name="title"
                     class="form-control form-control-lg"
                     placeholder="Ex : Grand Défilé de Printemps 2025"
                     maxlength="200"
                     value="<?= h($_POST['title'] ?? '') ?>"
                     required>
            </div>

            <!-- Type + Max participants -->
            <div class="col-md-6">
              <label for="type" class="form-label fw-semibold">Type d'événement</label>
              <select id="type" name="type" class="form-select">
                <?php
                $types = [
                  'défilé'     => '🎭 Défilé',
                  'concours'   => '🏆 Concours',
                  'atelier'    => '🧵 Atelier',
                  'exposition' => '🖼️ Exposition',
                  'autre'      => '📌 Autre',
                ];
                foreach ($types as $val => $lbl):
                  $sel = ($_POST['type'] ?? 'autre') === $val ? 'selected' : '';
                ?>
                  <option value="<?= $val ?>" <?= $sel ?>><?= $lbl ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label for="max_participants" class="form-label fw-semibold">
                Places maximum
                <span class="text-muted fw-normal">(0 = illimité)</span>
              </label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-people"></i></span>
                <input type="number" id="max_participants" name="max_participants"
                       class="form-control"
                       min="0" max="10000"
                       value="<?= (int)($_POST['max_participants'] ?? 0) ?>">
              </div>
            </div>

            <!-- Date + Time -->
            <div class="col-md-6">
              <label for="event_date" class="form-label fw-semibold">
                Date <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                <input type="date" id="event_date" name="event_date"
                       class="form-control"
                       min="<?= date('Y-m-d') ?>"
                       value="<?= h($_POST['event_date'] ?? '') ?>"
                       required>
              </div>
            </div>

            <div class="col-md-6">
              <label for="event_time" class="form-label fw-semibold">Heure</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                <input type="time" id="event_time" name="event_time"
                       class="form-control"
                       value="<?= h($_POST['event_time'] ?? '18:00') ?>">
              </div>
            </div>

            <!-- Location -->
            <div class="col-12">
              <label for="location" class="form-label fw-semibold">Lieu</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                <input type="text" id="location" name="location"
                       class="form-control"
                       placeholder="Amphithéâtre Principal — Campus Central"
                       maxlength="255"
                       value="<?= h($_POST['location'] ?? '') ?>">
              </div>
            </div>

            <!-- Description -->
            <div class="col-12">
              <label for="description" class="form-label fw-semibold">Description</label>
              <textarea id="description" name="description"
                        class="form-control"
                        rows="5"
                        maxlength="3000"
                        placeholder="Décrivez l'événement : programme, règles, récompenses…"><?= h($_POST['description'] ?? '') ?></textarea>
              <div class="text-end"><small class="text-muted" id="descCount">0 / 3000</small></div>
            </div>

            <!-- Image upload -->
            <div class="col-12">
              <label class="form-label fw-semibold">
                Image de bannière
                <span class="text-muted fw-normal">(optionnel — JPG, PNG, WEBP · max 5 Mo)</span>
              </label>
              <div class="border-2 border-dashed rounded-4 p-4 text-center"
                   style="border-color:#dee2e6; cursor:pointer;"
                   onclick="document.getElementById('imageInput').click()">
                <div id="uploadPlaceholder">
                  <i class="bi bi-cloud-upload fs-2 text-muted d-block mb-1"></i>
                  <p class="text-muted small mb-0">Cliquez pour choisir une image</p>
                </div>
                <img id="imagePreview" src="" alt=""
                     style="display:none; max-height:200px; border-radius:10px; max-width:100%;">
                <input type="file" id="imageInput" name="image"
                       accept="image/*" class="d-none">
              </div>
            </div>

            <!-- Preview card -->
            <div class="col-12">
              <div class="p-3 rounded-3" style="background:#f8f9fa; border:1px dashed #dee2e6;">
                <p class="text-muted small fw-semibold mb-2">
                  <i class="bi bi-eye me-1"></i>Aperçu de la carte événement
                </p>
                <div class="event-card" style="max-width:340px;">
                  <div class="p-3">
                    <div class="mb-2">
                      <span id="previewBadge" class="badge badge-event-autre event-type-badge">Autre</span>
                    </div>
                    <div class="fw-bold" id="previewTitle" style="font-size:.95rem">Titre de l'événement</div>
                    <div class="event-date-chip mt-2">
                      <i class="bi bi-calendar3 text-danger"></i>
                      <span id="previewDate">Date à définir</span>
                    </div>
                    <div class="event-date-chip mt-1" id="previewLocWrap">
                      <i class="bi bi-geo-alt text-danger"></i>
                      <span id="previewLocation">Lieu à définir</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Buttons -->
            <div class="col-12 d-flex gap-3 justify-content-end pt-2">
              <a href="<?= SITE_URL ?>/admin/events.php"
                 class="btn btn-light rounded-pill px-4">Annuler</a>
              <button type="submit" class="btn btn-fashion px-5 rounded-pill">
                <i class="bi bi-send-fill me-2"></i>Publier l'événement
              </button>
            </div>

          </div><!-- /.row -->
        </form>
      </div>
    </div>

  </div>
</div>

<script>
// Live preview
const titleInput  = document.getElementById('title');
const typeSelect  = document.getElementById('type');
const dateInput   = document.getElementById('event_date');
const timeInput   = document.getElementById('event_time');
const locInput    = document.getElementById('location');

function updatePreview() {
  const t = titleInput.value || 'Titre de l\'événement';
  document.getElementById('previewTitle').textContent = t;

  const type = typeSelect.value;
  const badge = document.getElementById('previewBadge');
  badge.textContent = type.charAt(0).toUpperCase() + type.slice(1);
  badge.className = 'badge badge-event-' + type + ' event-type-badge';

  if (dateInput.value) {
    const d   = new Date(dateInput.value + 'T' + (timeInput.value || '00:00'));
    const str = d.toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long', year:'numeric' })
              + ' à ' + (timeInput.value || '00:00');
    document.getElementById('previewDate').textContent = str;
  } else {
    document.getElementById('previewDate').textContent = 'Date à définir';
  }

  document.getElementById('previewLocation').textContent = locInput.value || 'Lieu à définir';
}

[titleInput, typeSelect, dateInput, timeInput, locInput].forEach(el =>
  el?.addEventListener('input', updatePreview)
);

// Description counter
document.getElementById('description').addEventListener('input', function () {
  document.getElementById('descCount').textContent = this.value.length + ' / 3000';
});

// Image preview
document.getElementById('imageInput').addEventListener('change', function () {
  const file    = this.files[0];
  const preview = document.getElementById('imagePreview');
  const ph      = document.getElementById('uploadPlaceholder');
  if (file) {
    const r = new FileReader();
    r.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
      ph.style.display = 'none';
    };
    r.readAsDataURL(file);
  }
});
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
