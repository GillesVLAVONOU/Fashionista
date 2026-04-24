<?php
// ============================================================
//  post_detail.php - Single Post View
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Post.php';

$postId    = (int)($_GET['id'] ?? 0);
$postModel = new Post();
$post      = $postModel->findById($postId);

if (!$post) {
    setFlash('error', 'Publication introuvable.');
    redirect(SITE_URL . '/index.php');
}

$comments   = $postModel->getComments($postId);
$isLiked    = isLoggedIn() ? $postModel->isLiked($postId, currentUserId()) : false;
$isOwner    = isLoggedIn() && currentUserId() === (int)$post['user_id'];
$pageTitle  = h($post['title']);
$activePage = 'feed';

require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4 justify-content-center">

  <div class="col-lg-7">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <img src="<?= postImageUrl($post) ?>"
           alt="<?= h($post['title']) ?>"
           class="post-detail-img w-100">
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4">

        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
          <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$post['user_id'] ?>">
            <img src="<?= avatarUrl($post) ?>"
                 alt="<?= h($post['username']) ?>"
                 style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid var(--clr-primary)">
          </a>
          <div class="flex-grow-1">
            <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$post['user_id'] ?>"
               class="fw-bold text-decoration-none text-dark d-block">
              <?= h($post['full_name']) ?>
            </a>
            <span class="text-muted small">@<?= h($post['username']) ?></span>
          </div>
          <div class="text-muted small"><?= timeAgo($post['created_at']) ?></div>
        </div>

        <div class="mb-2"><?= categoryBadge($post['category']) ?></div>
        <h1 style="font-size:1.5rem;" class="mb-2"><?= h($post['title']) ?></h1>
        <?php if (!empty($post['description'])): ?>
          <p class="text-muted" style="font-size:.9rem; line-height:1.65">
            <?= nl2br(h($post['description'])) ?>
          </p>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-3 py-3 border-top border-bottom my-3">
          <button class="btn-like d-flex align-items-center gap-2 <?= $isLiked ? 'liked' : '' ?>"
                  data-post-id="<?= (int)$post['id'] ?>"
                  style="background:none;border:none;cursor:pointer;font-size:1rem;font-weight:600;">
            <i class="<?= $isLiked ? 'bi bi-heart-fill text-danger' : 'bi bi-heart' ?>" style="font-size:1.3rem"></i>
            <span class="like-count"><?= (int)$post['like_count'] ?></span>
            <span class="text-muted small">likes</span>
          </button>

          <span class="d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-chat" style="font-size:1.2rem"></i>
            <span class="comment-count" data-post-id="<?= (int)$post['id'] ?>"><?= count($comments) ?></span>
            <span class="small">commentaires</span>
          </span>

          <?php if ($isOwner): ?>
            <div class="ms-auto d-flex gap-2">
              <a href="<?= SITE_URL ?>/delete_post.php?post_id=<?= (int)$post['id'] ?>&csrf=<?= csrfToken() ?>"
                 class="btn btn-sm btn-outline-danger rounded-pill"
                 onclick="return confirm('Supprimer cette creation ?')">
                <i class="bi bi-trash me-1"></i>Supprimer
              </a>
            </div>
          <?php endif; ?>
        </div>

        <div class="comment-section open" data-post-id="<?= (int)$post['id'] ?>" style="display:block">
          <div class="mb-3 comment-list" style="max-height:280px; overflow-y:auto;" id="commentList">
            <?php if (empty($comments)): ?>
              <p class="text-muted text-center small py-3">
                Soyez le premier a commenter...
              </p>
            <?php else: ?>
              <?php foreach ($comments as $c): ?>
                <div class="comment-item">
                  <img src="<?= avatarUrl($c) ?>"
                       alt="<?= h($c['username']) ?>"
                       class="comment-avatar">
                  <div class="comment-bubble">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$c['user_id'] ?>"
                       class="comment-author"><?= h($c['username']) ?></a>
                    <span class="ms-1"><?= h($c['content']) ?></span>
                    <div class="text-muted mt-1" style="font-size:.72rem">
                      <?= timeAgo($c['created_at']) ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <?php if (isLoggedIn()): ?>
            <form class="comment-form comment-form-wrap" data-post-id="<?= (int)$post['id'] ?>">
              <img src="<?= avatarUrl(currentUser()) ?>"
                   alt=""
                   style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0">
              <input type="text"
                     class="comment-input"
                     placeholder="Ajouter un commentaire..."
                     maxlength="500"
                     required>
              <button type="submit" class="btn-submit-comment">
                <i class="bi bi-send-fill"></i>
              </button>
            </form>
          <?php else: ?>
            <div class="text-center small text-muted p-3 bg-light rounded-3">
              <a href="<?= SITE_URL ?>/login.php">Connectez-vous</a> pour laisser un commentaire.
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>

</div>

<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/includes/footer.php'; ?>
