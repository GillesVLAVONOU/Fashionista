  </div><!-- /.container -->
</main><!-- /.main-content -->

<!-- ── Footer ── -->
<footer class="site-footer mt-5">
  <div class="container">
    <div class="row gy-4">

      <!-- Brand column -->
      <div class="col-lg-4">
        <h5 class="footer-brand"><i class="bi bi-scissors me-2"></i><?= SITE_NAME ?></h5>
        <p class="footer-tagline"><?= SITE_TAGLINE ?></p>
        <p class="text-muted small">La plateforme dédiée aux étudiants créateurs de mode.</p>
      </div>

      <!-- Links column -->
      <div class="col-lg-2 col-6">
        <h6 class="footer-heading">Découvrir</h6>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/index.php">Créations</a></li>
          <li><a href="<?= SITE_URL ?>/events.php">Événements</a></li>
          <?php if (isLoggedIn()): ?>
          <li><a href="<?= SITE_URL ?>/create_post.php">Publier</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Account column -->
      <div class="col-lg-2 col-6">
        <h6 class="footer-heading">Mon compte</h6>
        <ul class="footer-links">
          <?php if (isLoggedIn()): ?>
          <li><a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a></li>
          <li><a href="<?= SITE_URL ?>/profile.php?id=<?= currentUserId() ?>">Profil</a></li>
          <li><a href="<?= SITE_URL ?>/logout.php">Déconnexion</a></li>
          <?php else: ?>
          <li><a href="<?= SITE_URL ?>/login.php">Connexion</a></li>
          <li><a href="<?= SITE_URL ?>/register.php">Inscription</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Social column -->
      <div class="col-lg-4">
        <h6 class="footer-heading">Réseaux</h6>
        <div class="footer-social">
          <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-pinterest"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-tiktok"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
        </div>
      </div>

    </div><!-- /.row -->
    <hr class="footer-divider">
    <div class="d-flex flex-wrap justify-content-between align-items-center pb-3">
      <p class="text-muted small mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.</p>
      <p class="text-muted small mb-0">Plateforme universitaire — usage académique</p>
    </div>
  </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>
</body>
</html>
