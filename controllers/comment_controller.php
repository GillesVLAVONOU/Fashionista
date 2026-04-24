<?php
// ============================================================
//  controllers/comment_controller.php — Add Comment (AJAX)
// ============================================================

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Notification.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => SITE_URL . '/login.php']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$postId  = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($postId <= 0 || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

if (mb_strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Commentaire trop long (max 1000 caractères).']);
    exit;
}

$postModel = new Post();
$post      = $postModel->findById($postId);

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Publication introuvable.']);
    exit;
}

$postModel->addComment($postId, currentUserId(), $content);

// Notification to post owner
if ($post['user_id'] !== currentUserId()) {
    $notifModel = new Notification();
    $me         = currentUser();
    $notifModel->create(
        (int)$post['user_id'],
        currentUserId(),
        'comment',
        $me['username'] . ' a commenté votre création "' . $post['title'] . '"',
        $postId
    );
}

$me = currentUser();
echo json_encode([
    'success'  => true,
    'username' => $me['username'],
    'avatar'   => avatarUrl($me),
    'content'  => $content,
]);
