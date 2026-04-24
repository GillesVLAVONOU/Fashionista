<?php
// ============================================================
//  controllers/like_controller.php — Toggle Like (AJAX)
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

$postId = (int)($_POST['post_id'] ?? 0);
if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID invalide.']);
    exit;
}

$postModel = new Post();
$post      = $postModel->findById($postId);

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Publication introuvable.']);
    exit;
}

$result = $postModel->toggleLike($postId, currentUserId());

// Create notification for post owner if liked (not unlike)
if ($result['liked'] && $post['user_id'] !== currentUserId()) {
    $notifModel = new Notification();
    $me         = currentUser();
    $notifModel->create(
        (int)$post['user_id'],
        currentUserId(),
        'like',
        $me['username'] . ' a aimé votre création "' . $post['title'] . '"',
        $postId
    );
}

echo json_encode([
    'success' => true,
    'liked'   => $result['liked'],
    'count'   => $result['count'],
]);
