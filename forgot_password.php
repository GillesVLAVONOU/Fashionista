<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$pageTitle = 'Mot de passe oublie';
$activePage = '';
$debugResetLink = $_SESSION['password_reset_debug_link'] ?? null;
unset($_SESSION['password_reset_debug_link']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center py-4">
  <div class="col-md-6 col-lg-5">
    <div class="auth-card">
      <div class="text-center mb-4">
        <div style="font-size:2.5rem; color:var(--clr-primary)">
          <i class="bi bi-key"></i>
        </div>
        <h1 class="auth-title">Mot de passe oublie</h1>
        <p class="auth-subtitle">Entrez votre email pour recevoir un lien de reinitialisation.</p>
      </div>

      <?php if ($debugResetLink): ?>
        <div class="alert alert-warning small">
          L email n a pas pu etre envoye depuis cet environnement. Utilisez ce lien local de reinitialisation :
          <a href="<?= h($debugResetLink) ?>" class="fw-semibold">ouvrir le lien</a>
        </div>
      <?php endif; ?>

      <form action="<?= SITE_URL ?>/controllers/auth_controller.php" method="POST" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="action" value="request_reset">

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
                   required autofocus>
          </div>
        </div>

        <button type="submit" class="btn btn-fashion w-100 py-2 mb-3">
          <i class="bi bi-send me-2"></i>Envoyer le lien
        </button>

        <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-fashion w-100 py-2">
          <i class="bi bi-arrow-left me-2"></i>Retour a la connexion
        </a>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
