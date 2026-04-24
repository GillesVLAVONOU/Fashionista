<?php
// ============================================================
//  create_post.php — Publish a New Creation
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Post.php';

requireLogin();

$pageTitle  = 'Publier une création';
$activePage = 'create';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de sécurité invalide.');
        redirect(SITE_URL . '/create_post.php');
    }

    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = $_POST['category']         ?? 'autre';

    $validCategories = ['robe','costume','accessoire','streetwear','haute_couture','autre'];
    if (!in_array($category, $validCategories, true)) $category = 'autre';

    $errors = [];
    if (empty($title))            $errors[] = 'Le titre est obligatoire.';
    if (mb_strlen($title) > 200) $errors[] = 'Le titre est trop long (200 caractères max).';
    if (empty($_FILES['image']['name'])) $errors[] = 'Veuillez choisir une image.';

    if (empty($errors)) {
        try {
            $filename = uploadImage($_FILES['image'], UPLOAD_POSTS);
            $postModel = new Post();
            $postId    = $postModel->create([
                'user_id'     => currentUserId(),
                'title'       => $title,
                'description' => $description,
                'image'       => $filename,
                'category'    => $category,
            ]);
            setFlash('success', 'Votre création a été publiée ! 🎉');
            redirect(SITE_URL . '/post_detail.php?id=' . $postId);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!empty($errors)) {
        setFlash('error', implode('<br>', $errors));
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8 col-xl-7">

    <!-- Page title -->
    <div class="d-flex align-items-center gap-3 mb-4">
      <a href="<?= SITE_URL ?>/index.php" class="btn btn-light btn-sm rounded-circle p-2">
        <i class="bi bi-arrow-left"></i>
      </a>
      <h1 class="mb-0" style="font-size:1.6rem">
        <i class="bi bi-plus-circle text-danger me-2"></i>Publier une création
      </h1>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4 p-md-5">

        <form action="<?= SITE_URL ?>/create_post.php"
              method="POST"
              enctype="multipart/form-data"
              novalidate>

          <?= csrfField() ?>

          <div class="row g-4">

            <!-- Image upload -->
            <div class="col-12">
              <label class="form-label fw-semibold">
                <i class="bi bi-image me-1 text-danger"></i>Image de la création
                <span class="text-danger">*</span>
              </label>
              <div class="border-2 border-dashed rounded-4 p-4 text-center"
                   style="border-color:#dee2e6; cursor:pointer; transition:.2s"
                   id="dropZone"
                   onclick="document.getElementById('imageInput').click()"
                   ondragover="event.preventDefault();this.style.borderColor='#c9184a'"
                   ondragleave="this.style.borderColor='#dee2e6'"
                   ondrop="handleDrop(event)">
                <div id="uploadPlaceholder">
                  <i class="bi bi-cloud-upload fs-1 mb-2 d-block text-muted"></i>
                  <p class="mb-1 fw-semibold">Glissez votre image ici</p>
                  <p class="text-muted small mb-2">ou cliquez pour parcourir</p>
                  <span class="badge bg-light text-muted border">JPG · PNG · WEBP · GIF · Max 5 Mo</span>
                </div>
                <img id="imagePreview" src="" alt="Prévisualisation" class="rounded-3">
                <input type="file"
                       id="imageInput"
                       name="image"
                       accept="image/*"
                       class="d-none"
                       required>
              </div>
            </div>

            <!-- Title -->
            <div class="col-12">
              <label for="title" class="form-label fw-semibold">
                Titre de la création <span class="text-danger">*</span>
              </label>
              <input type="text"
                     id="title"
                     name="title"
                     class="form-control form-control-lg"
                     placeholder="Ex : Robe de soirée plissée inspiration japonaise"
                     maxlength="200"
                     value="<?= h($_POST['title'] ?? '') ?>"
                     required>
              <div class="d-flex justify-content-end">
                <small class="text-muted" id="titleCount">0 / 200</small>
              </div>
            </div>

            <!-- Category -->
            <div class="col-md-6">
              <label for="category" class="form-label fw-semibold">Catégorie</label>
              <select id="category" name="category" class="form-select form-select-lg">
                <?php
                $cats = [
                  'robe'          => '👗 Robe',
                  'costume'       => '🎭 Costume',
                  'accessoire'    => '💍 Accessoire',
                  'streetwear'    => '🧢 Streetwear',
                  'haute_couture' => '✨ Haute couture',
                  'autre'         => '📌 Autre',
                ];
                foreach ($cats as $val => $label):
                  $selected = ($_POST['category'] ?? 'autre') === $val ? 'selected' : '';
                ?>
                  <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Description -->
            <div class="col-12">
              <label for="description" class="form-label fw-semibold">
                Description
                <span class="text-muted fw-normal small">(optionnel)</span>
              </label>
              <textarea id="description"
                        name="description"
                        class="form-control"
                        rows="5"
                        maxlength="2000"
                        placeholder="Décrivez votre création : matières utilisées, techniques, inspirations, processus de création…"><?= h($_POST['description'] ?? '') ?></textarea>
              <div class="d-flex justify-content-end">
                <small class="text-muted" id="descCount">0 / 2000</small>
              </div>
            </div>

            <!-- Submit -->
            <div class="col-12 d-flex gap-2 justify-content-end">
              <a href="<?= SITE_URL ?>/index.php"
                 class="btn btn-light rounded-pill px-4">Annuler</a>
              <button type="submit" class="btn btn-fashion px-5">
                <i class="bi bi-send-fill me-2"></i>Publier
              </button>
            </div>

          </div><!-- /.row -->
        </form>

      </div>
    </div>

  </div>
</div>

<script>
// Character counters
function updateCount(inputId, countId, max) {
  const val = document.getElementById(inputId)?.value?.length || 0;
  const el  = document.getElementById(countId);
  if (el) el.textContent = val + ' / ' + max;
}

document.getElementById('title')?.addEventListener('input', () => updateCount('title','titleCount',200));
document.getElementById('description')?.addEventListener('input', () => updateCount('description','descCount',2000));

// Drag & drop
function handleDrop(e) {
  e.preventDefault();
  document.getElementById('dropZone').style.borderColor = '#dee2e6';
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    const input    = document.getElementById('imageInput');
    const dt       = new DataTransfer();
    dt.items.add(file);
    input.files    = dt.files;
    input.dispatchEvent(new Event('change'));
  }
}

// Show preview when image selected
document.getElementById('imageInput')?.addEventListener('change', function () {
  const file     = this.files[0];
  const preview  = document.getElementById('imagePreview');
  const placeholder = document.getElementById('uploadPlaceholder');
  if (file) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.src           = e.target.result;
      preview.style.display = 'block';
      placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
