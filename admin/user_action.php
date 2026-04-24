<?php
// ============================================================
//  admin/user_action.php — AJAX User Actions
// ============================================================

header('Content-Type: application/json');

require_once __DIR__ . '/admin_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'CSRF invalide.']);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = (int)($_POST['user_id'] ?? 0);
$db     = getDB();

if ($userId <= 0 || $userId === currentUserId()) {
    echo json_encode(['success' => false, 'message' => 'ID invalide.']);
    exit;
}

if ($action === 'make_admin') {
    $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$userId]);
    echo json_encode(['success' => true]);
} elseif ($action === 'make_student') {
    $db->prepare("UPDATE users SET role = 'student' WHERE id = ?")->execute([$userId]);
    echo json_encode(['success' => true]);
} elseif ($action === 'delete') {
    $row = $db->prepare('SELECT avatar FROM users WHERE id = ?');
    $row->execute([$userId]);
    $u = $row->fetch();
    if ($u) deleteUpload($u['avatar'] ?? '', UPLOAD_AVATARS);
    $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
}
