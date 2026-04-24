<?php
// ============================================================
//  edit_profile.php — Edit User Profile
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/User.php';

requireLogin();

$pageTitle  = 'Modifier mon profil';
$activePage = '';
$userModel  = new User();
$user       = $userModel->findById(currentUserId());

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de sécurité invalide.');
        redirect(SITE_URL . '/edit_profile.php');
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $bio      = trim($_POST['bio']       ?? '');
    $errors   = [];

    if (empty($fullName) || mb_strlen($fullName) < 2)
        $errors[] = 'Le nom complet est requis (min. 2 caractères).';

    $updateData = ['full_name' => $fullName, 'bio' => $bio];

    // Handle avatar upload
    if (!empty($_FILES['avatar']['name'])) {
        try {
            $filename = uploadImage($_FILES['avatar'], UPLOAD_AVATARS);
            // Delete old avatar if not default
            deleteUpload($user['avatar'] ?? '', UPLOAD_AVATARS);
            $updateData['avatar'] = $filename;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    // Handle password change
    $newPwd  = $_POST['new_password']  ?? '';
    $newPwd2 = $_POST['new_password2'] ?? '';
    if (!empty($newPwd)) {
        if (mb_strlen($newPwd) < 8)
            $errors[] = 'Le nouveau mot de passe doit comporter au moins 8 caractères.';
        elseif ($newPwd !== $newPwd2)
            $errors[] = 'Les mots de passe ne correspondent pas.';
        else {
            $oldPwd = $_POST['current_password'] ?? '';
            if (!$userModel->verifyPassword($oldPwd, $user['password']))
                $errors[] = 'Mot de passe actuel incorrect.';
            else
                $userModel->updatePassword(currentUserId(), $newPwd);
        }
    }

    if (empty($errors)) {
        $userModel->update(currentUserId(), $updateData);
        setFlash('success', 'Profil mis à jour avec succès.');
        redirect(SITE_URL . '/profile.php?id=' . currentUserId());
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">

    <div class="d-flex align-items-center gap-3 mb-4">
      <a href="<?= SITE_URL ?>/profile.php?id=<?= currentUserId() ?>"
         class="btn btn-light btn-sm rounded-circle p-2">
        <i class="bi bi-arrow-left"></i>
      </a>
      <h1 class="mb-0" style="font-size:1.5rem">
        <i class="bi bi-gear text-danger me-2"></i>Modifier mon profil
      </h1>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4 p-md-5">

        <form action="<?= SITE_URL ?>/edit_profile.php"
              method="POST"
              enctype="multipart/form-data"
              novalidate>
          <?= csrfField() ?>

          <!-- Avatar -->
          <div class="text-center mb-5">
            <div class="position-relative d-inline-block">
              <img src="<?= avatarUrl($user) ?>"
                   alt="Avatar"
                   id="avatarPreview"
                   style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:3px solid var(--clr-primary)">
              <label for="avatarInput"
                     class="position-absolute bottom-0 end-0 btn btn-fashion btn-sm rounded-circle p-2"
                     style="cursor:pointer;width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-camera-fill" style="font-size:.85rem"></i>
              </label>
              <input type="file" id="avatarInput" name="avatar"
                     accept="image/*" class="d-none">
            </div>
            <p class="text-muted small mt-2">Cliquez sur l'icône pour changer de photo</p>
          </div>

          <div class="row g-3">

            <!-- Full name -->
            <div class="col-12">
              <label for="full_name" class="form-label fw-semibold">Nom complet</label>
              <input type="text" id="full_name" name="full_name"
                     class="form-control"
                     value="<?= h($user['full_name']) ?>"
                     required>
            </div>

            <!-- Username (read-only) -->
            <div class="col-12">
              <label class="form-label fw-semibold">Pseudo</label>
              <input type="text" class="form-control bg-light" value="@<?= h($user['username']) ?>" readonly>
              <div class="form-text">Le pseudo ne peut pas être modifié.</div>
            </div>

            <!-- Bio -->
            <div class="col-12">
              <label for="bio" class="form-label fw-semibold">Bio</label>
              <textarea id="bio" name="bio" class="form-control" rows="3"
                        maxlength="500"
                        placeholder="Parlez de votre style, vos inspirations…"><?= h($user['bio'] ?? '') ?></textarea>
            </div>

            <!-- Divider: password section -->
            <div class="col-12">
              <hr class="my-1">
              <h6 class="fw-bold text-muted mb-3">
                <i class="bi bi-shield-lock me-2"></i>Changer le mot de passe
                <span class="fw-normal">(optionnel)</span>
              </h6>
            </div>

            <div class="col-12">
              <label for="current_password" class="form-label fw-semibold">Mot de passe actuel</label>
              <input type="password" id="current_password" name="current_password"
                     class="form-control" placeholder="Requis pour changer le mot de passe">
            </div>

            <div class="col-md-6">
              <label for="new_password" class="form-label fw-semibold">Nouveau mot de passe</label>
              <input type="password" id="new_password" name="new_password"
                     class="form-control" placeholder="Min. 8 caractères" minlength="8">
            </div>

            <div class="col-md-6">
              <label for="new_password2" class="form-label fw-semibold">Confirmer</label>
              <input type="password" id="new_password2" name="new_password2"
                     class="form-control" placeholder="Répéter le nouveau">
            </div>

            <!-- Buttons -->
            <div class="col-12 d-flex gap-2 justify-content-end pt-2">
              <a href="<?= SITE_URL ?>/profile.php?id=<?= currentUserId() ?>"
                 class="btn btn-light rounded-pill px-4">Annuler</a>
              <button type="submit" class="btn btn-fashion px-5">
                <i class="bi bi-check-lg me-2"></i>Enregistrer
              </button>
            </div>

          </div><!-- /.row -->
        </form>

      </div>
    </div>
  </div>
</div>

<script>
// Avatar preview
document.getElementById('avatarInput').addEventListener('change', function () {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
    reader.readAsDataURL(file);
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
