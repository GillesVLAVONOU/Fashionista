<?php
// ============================================================
//  login.php - Login Page
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$pageTitle = 'Connexion';
$activePage = '';
$redirect = htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES);

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center py-4">
  <div class="col-md-6 col-lg-5">

    <div class="auth-card">

      <div class="text-center mb-4">
        <div style="font-size:2.5rem; color:var(--clr-primary)">
          <i class="bi bi-scissors"></i>
        </div>
        <h1 class="auth-title"><?= SITE_NAME ?></h1>
        <p class="auth-subtitle">Connectez-vous a votre espace createur</p>
      </div>

      <form action="<?= SITE_URL ?>/controllers/auth_controller.php" method="POST" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="redirect" value="<?= $redirect ?>">

        <div class="mb-3">
          <label for="email" class="form-label">Adresse email</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
              <i class="bi bi-envelope text-muted"></i>
            </span>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control border-start-0"
                   placeholder="votre@email.edu"
                   value="<?= h($_GET['email'] ?? '') ?>"
                   required autofocus>
          </div>
        </div>

        <div class="mb-4">
          <label for="password" class="form-label">
            Mot de passe
          </label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
              <i class="bi bi-lock text-muted"></i>
            </span>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control border-start-0 border-end-0"
                   placeholder="••••••••"
                   required>
            <button class="btn btn-light border" type="button" id="togglePwd">
              <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
          </div>
          <div class="text-end mt-2">
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
          <div class="form-check mb-0">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label small" for="remember">Se souvenir de moi</label>
          </div>
        </div>

        <button type="submit" class="btn btn-fashion w-100 py-2 mb-3">
          <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
        </button>
           <a href="<?= SITE_URL ?>/forgot_password.php" class="small fw-semibold">Mot de passe oublié ?</a>

        <div class="divider-text">ou</div>

        <a href="<?= SITE_URL ?>/register.php" class="btn btn-outline-fashion w-100 py-2">
          <i class="bi bi-person-plus me-2"></i>Creer un compte
        </a>

      </form>

    </div>
  </div>
</div>

<script>
document.getElementById('togglePwd').addEventListener('click', function () {
  const input = document.getElementById('password');
  const icon = document.getElementById('eyeIcon');
  const visible = input.type === 'text';
  input.type = visible ? 'password' : 'text';
  icon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
