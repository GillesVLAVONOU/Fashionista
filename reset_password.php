<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/PasswordReset.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$token = trim($_GET['token'] ?? '');
$resetModel = new PasswordReset();
$resetRow = $token !== '' ? $resetModel->findValidToken($token) : null;

$pageTitle = 'Reinitialiser le mot de passe';
$activePage = '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center py-4">
  <div class="col-md-6 col-lg-5">
    <div class="auth-card">
      <div class="text-center mb-4">
        <div style="font-size:2.5rem; color:var(--clr-primary)">
          <i class="bi bi-shield-lock"></i>
        </div>
        <h1 class="auth-title">Nouveau mot de passe</h1>
        <p class="auth-subtitle">Choisissez un nouveau mot de passe securise.</p>
      </div>

      <?php if (!$resetRow): ?>
        <div class="alert alert-danger small">
          Ce lien de reinitialisation est invalide ou expire.
        </div>
        <a href="<?= SITE_URL ?>/forgot_password.php" class="btn btn-outline-fashion w-100 py-2">
          <i class="bi bi-arrow-repeat me-2"></i>Demander un nouveau lien
        </a>
      <?php else: ?>
        <form action="<?= SITE_URL ?>/controllers/auth_controller.php" method="POST" novalidate id="resetPwdForm">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="reset_password">
          <input type="hidden" name="token" value="<?= h($token) ?>">

          <div class="mb-3">
            <label for="password" class="form-label">Nouveau mot de passe</label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   minlength="8"
                   placeholder="Minimum 8 caracteres"
                   required>
          </div>

          <div class="mb-4">
            <label for="password2" class="form-label">Confirmer</label>
            <input type="password"
                   id="password2"
                   name="password2"
                   class="form-control"
                   placeholder="Repetez le mot de passe"
                   required>
          </div>

          <button type="submit" class="btn btn-fashion w-100 py-2 mb-3">
            <i class="bi bi-check2-circle me-2"></i>Enregistrer
          </button>

          <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-fashion w-100 py-2">
            Retour a la connexion
          </a>
        </form>

        <script>
        document.getElementById('resetPwdForm').addEventListener('submit', function (e) {
          const p1 = document.getElementById('password').value;
          const p2 = document.getElementById('password2').value;
          if (p1 !== p2) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            document.getElementById('password2').focus();
          }
        });
        </script>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
