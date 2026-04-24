<?php
// ============================================================
//  register.php — Registration Page
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$pageTitle  = 'Inscription';
$activePage = '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center py-4">
  <div class="col-md-7 col-lg-6">

    <div class="auth-card">

      <!-- Logo -->
      <div class="text-center mb-4">
        <div style="font-size:2.5rem; color:var(--clr-primary)">
          <i class="bi bi-scissors"></i>
        </div>
        <h1 class="auth-title">Rejoindre Fashionista</h1>
        <p class="auth-subtitle">Créez votre espace créateur universitaire</p>
      </div>

      <!-- Register form -->
      <form action="<?= SITE_URL ?>/controllers/auth_controller.php"
            method="POST"
            enctype="multipart/form-data"
            novalidate
            id="registerForm">

        <?= csrfField() ?>
        <input type="hidden" name="action" value="register">

        <div class="row g-3">

          <!-- Full name -->
          <div class="col-12">
            <label for="full_name" class="form-label">Nom complet</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-person text-muted"></i>
              </span>
              <input type="text"
                     id="full_name"
                     name="full_name"
                     class="form-control border-start-0"
                     placeholder="Prénom Nom"
                     value="<?= h($_POST['full_name'] ?? '') ?>"
                     required autofocus>
            </div>
          </div>

          <!-- Username -->
          <div class="col-12">
            <label for="username" class="form-label">Pseudo (identifiant public)</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">@</span>
              <input type="text"
                     id="username"
                     name="username"
                     class="form-control border-start-0"
                     placeholder="mon_pseudo"
                     pattern="[a-zA-Z0-9_]{3,50}"
                     value="<?= h($_POST['username'] ?? '') ?>"
                     required>
            </div>
            <div class="form-text">3-50 caractères, lettres, chiffres et _ uniquement.</div>
          </div>

          <!-- Email -->
          <div class="col-12">
            <label for="email" class="form-label">Email universitaire</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-envelope text-muted"></i>
              </span>
              <input type="email"
                     id="email"
                     name="email"
                     class="form-control border-start-0"
                     placeholder="vous@universite.edu"
                     value="<?= h($_POST['email'] ?? '') ?>"
                     required>
            </div>
          </div>

          <!-- Password -->
          <div class="col-md-6">
            <label for="password" class="form-label">Mot de passe</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-lock text-muted"></i>
              </span>
              <input type="password"
                     id="password"
                     name="password"
                     class="form-control border-start-0 border-end-0"
                     placeholder="Min. 8 caractères"
                     minlength="8"
                     required>
              <button class="btn btn-light border" type="button" id="togglePwd">
                <i class="bi bi-eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>

          <!-- Confirm password -->
          <div class="col-md-6">
            <label for="password2" class="form-label">Confirmer</label>
            <input type="password"
                   id="password2"
                   name="password2"
                   class="form-control"
                   placeholder="Répéter le mot de passe"
                   required>
          </div>

          <!-- Bio -->
          <div class="col-12">
            <label for="bio" class="form-label">Bio <span class="text-muted fw-normal">(optionnel)</span></label>
            <textarea id="bio"
                      name="bio"
                      class="form-control"
                      rows="2"
                      placeholder="Parlez de vos inspirations, votre style…"
                      maxlength="500"><?= h($_POST['bio'] ?? '') ?></textarea>
          </div>

          <!-- Avatar -->
          <div class="col-12">
            <label for="avatar" class="form-label">Photo de profil <span class="text-muted fw-normal">(optionnel)</span></label>
            <input type="file"
                   id="avatar"
                   name="avatar"
                   class="form-control"
                   accept="image/*">
            <div class="form-text">Si vous ne choisissez rien, un avatar par defaut sera affiche jusqu'a l'ajout d'une photo.</div>
            <img id="avatarPreview"
                 src="<?= SITE_URL ?>/assets/images/default_avatar.jpg"
                 alt="Apercu avatar"
                 style="display:none;width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid var(--clr-primary);margin-top:.75rem;">
          </div>

          <!-- Password strength -->
          <div class="col-12">
            <div id="pwdStrength" class="d-none">
              <div class="d-flex justify-content-between mb-1">
                <small class="text-muted">Force du mot de passe</small>
                <small id="pwdLabel" class="fw-bold"></small>
              </div>
              <div class="progress" style="height:6px">
                <div class="progress-bar" id="pwdBar" role="progressbar" style="width:0%"></div>
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="col-12">
            <button type="submit" class="btn btn-fashion w-100 py-2 mt-1" id="submitBtn">
              <i class="bi bi-person-check me-2"></i>Créer mon compte
            </button>
          </div>

        </div><!-- /.row -->

      </form><!-- /form -->

      <div class="divider-text mt-3">déjà membre ?</div>
      <a href="<?= SITE_URL ?>/login.php"
         class="btn btn-outline-fashion w-100 py-2">
        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
      </a>

    </div><!-- /.auth-card -->

  </div>
</div>

<script>
// Toggle password
document.getElementById('togglePwd').addEventListener('click', function () {
  const input   = document.getElementById('password');
  const icon    = document.getElementById('eyeIcon');
  const visible = input.type === 'text';
  input.type    = visible ? 'password' : 'text';
  icon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
});

// Password strength indicator
document.getElementById('password').addEventListener('input', function () {
  const val = this.value;
  const el  = document.getElementById('pwdStrength');
  const bar = document.getElementById('pwdBar');
  const lbl = document.getElementById('pwdLabel');

  if (!val) { el.classList.add('d-none'); return; }
  el.classList.remove('d-none');

  let score = 0;
  if (val.length >= 8)  score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const levels = [
    { pct: 25,  cls: 'bg-danger',  label: 'Faible' },
    { pct: 50,  cls: 'bg-warning', label: 'Moyen' },
    { pct: 75,  cls: 'bg-info',    label: 'Bien' },
    { pct: 100, cls: 'bg-success', label: 'Fort' },
  ];
  const lvl = levels[Math.max(0, score - 1)];
  bar.style.width   = lvl.pct + '%';
  bar.className     = 'progress-bar ' + lvl.cls;
  lbl.textContent   = lvl.label;
  lbl.className     = 'fw-bold ' + lvl.cls.replace('bg-', 'text-');
});

// Client-side confirm password check
document.getElementById('registerForm').addEventListener('submit', function (e) {
  const p1 = document.getElementById('password').value;
  const p2 = document.getElementById('password2').value;
  if (p1 !== p2) {
    e.preventDefault();
    alert('Les mots de passe ne correspondent pas.');
    document.getElementById('password2').focus();
  }
});

// Avatar preview
document.getElementById('avatar').addEventListener('change', function () {
  const file = this.files && this.files[0];
  const preview = document.getElementById('avatarPreview');
  if (!preview) return;

  if (!file) {
    preview.style.display = 'none';
    preview.src = '<?= SITE_URL ?>/assets/images/default_avatar.jpg';
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    preview.src = e.target.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(file);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
