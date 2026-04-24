<?php
// ============================================================
//  views/post_card.php — Reusable Post Card Component
//  Expected variable: $post (array from Post model with author info)
// ============================================================

$isLiked = isLoggedIn() ? (new Post())->isLiked((int)$post['id'], currentUserId()) : false;
$comments = (new Post())->getComments((int)$post['id']);
?>
<div class="post-card">

  <!-- Post image with hover overlay -->
  <div class="post-card-img-wrap">
    <a href="<?= SITE_URL ?>/post_detail.php?id=<?= (int)$post['id'] ?>">
      <img src="<?= postImageUrl($post) ?>"
           alt="<?= h($post['title']) ?>"
           loading="lazy">
    </a>
    <div class="post-overlay">
      <div class="post-overlay-stat">
        <i class="bi bi-heart-fill"></i>
        <span><?= (int)$post['like_count'] ?></span>
      </div>
      <div class="post-overlay-stat">
        <i class="bi bi-chat-fill"></i>
        <span><?= (int)$post['comment_count'] ?></span>
      </div>
    </div>
    <!-- Category badge -->
    <div class="position-absolute top-0 start-0 m-2">
      <?= categoryBadge($post['category']) ?>
    </div>
  </div>

  <!-- Card body -->
  <div class="post-card-body">

    <!-- Author -->
    <div class="post-card-author">
      <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$post['user_id'] ?>">
        <img src="<?= avatarUrl($post) ?>"
             alt="<?= h($post['username']) ?>"
             class="post-author-avatar">
      </a>
      <div>
        <a href="<?= SITE_URL ?>/profile.php?id=<?= (int)$post['user_id'] ?>"
           class="post-author-name d-block"><?= h($post['full_name']) ?></a>
        <span class="post-time"><?= timeAgo($post['created_at']) ?></span>
      </div>
    </div>

    <!-- Title & description -->
    <a href="<?= SITE_URL ?>/post_detail.php?id=<?= (int)$post['id'] ?>" class="text-decoration-none">
      <div class="post-title"><?= h($post['title']) ?></div>
    </a>
    <?php if (!empty($post['description'])): ?>
      <p class="post-desc"><?= h(truncate($post['description'], 90)) ?></p>
    <?php endif; ?>

    <!-- Actions -->
    <div class="post-actions">

      <!-- Like button -->
      <button class="btn-like <?= $isLiked ? 'liked' : '' ?>"
              data-post-id="<?= (int)$post['id'] ?>">
        <i class="<?= $isLiked ? 'bi bi-heart-fill text-danger' : 'bi bi-heart' ?>"></i>
        <span class="like-count"><?= (int)$post['like_count'] ?></span>
      </button>

      <!-- Comment toggle -->
      <button class="btn-comment-toggle"
              data-post-id="<?= (int)$post['id'] ?>">
        <i class="bi bi-chat"></i>
        <span class="comment-count" data-post-id="<?= (int)$post['id'] ?>"><?= (int)$post['comment_count'] ?></span>
      </button>

      <!-- View detail link -->
      <a href="<?= SITE_URL ?>/post_detail.php?id=<?= (int)$post['id'] ?>"
         class="ms-auto text-muted" style="font-size:.8rem">
        <i class="bi bi-arrow-right-circle"></i>
      </a>

    </div><!-- /.post-actions -->
  </div><!-- /.post-card-body -->

  <!-- Comment section (collapsible) -->
  <div class="comment-section" data-post-id="<?= (int)$post['id'] ?>">

    <div class="comment-list">
      <?php foreach (array_slice($comments, -3) as $comment): ?>
        <div class="comment-item">
          <img src="<?= avatarUrl($comment) ?>"
               alt="<?= h($comment['username']) ?>"
               class="comment-avatar">
          <div class="comment-bubble">
            <span class="comment-author"><?= h($comment['username']) ?></span>
            <span class="ms-1"><?= h($comment['content']) ?></span>
            <div class="text-muted" style="font-size:.72rem"><?= timeAgo($comment['created_at']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (isLoggedIn()): ?>
      <form class="comment-form comment-form-wrap" data-post-id="<?= (int)$post['id'] ?>">
        <input type="text"
               class="comment-input"
               placeholder="Ajouter un commentaire…"
               maxlength="500"
               required>
        <button type="submit" class="btn-submit-comment">
          <i class="bi bi-send-fill"></i>
        </button>
      </form>
    <?php else: ?>
      <p class="text-muted text-center small mt-2">
        <a href="<?= SITE_URL ?>/login.php">Connectez-vous</a> pour commenter.
      </p>
    <?php endif; ?>

  </div><!-- /.comment-section -->

</div><!-- /.post-card -->
