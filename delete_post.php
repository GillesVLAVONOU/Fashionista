<?php
// ============================================================
//  delete_post.php — Delete a Post (owner or admin only)
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Post.php';

requireLogin();

// Accept POST (from form) or GET with csrf param (from link)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId    = (int)($_POST['post_id'] ?? 0);
    $csrfToken = $_POST['csrf_token'] ?? '';
} else {
    $postId    = (int)($_GET['post_id'] ?? 0);
    $csrfToken = $_GET['csrf'] ?? '';
}

if (!verifyCsrf($csrfToken)) {
    setFlash('error', 'Action non autorisée.');
    redirect(SITE_URL . '/index.php');
}

if ($postId <= 0) {
    redirect(SITE_URL . '/index.php');
}

$postModel = new Post();
$post      = $postModel->findById($postId);

if (!$post) {
    setFlash('error', 'Publication introuvable.');
    redirect(SITE_URL . '/index.php');
}

// Only owner or admin
if ((int)$post['user_id'] !== currentUserId() && !isAdmin()) {
    setFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette publication.');
    redirect(SITE_URL . '/index.php');
}

// Delete image file
deleteUpload($post['image'], UPLOAD_POSTS);

// Delete from DB
$postModel->delete($postId, isAdmin() ? (int)$post['user_id'] : currentUserId());

setFlash('success', 'Création supprimée avec succès.');
redirect(SITE_URL . '/profile.php?id=' . currentUserId());
