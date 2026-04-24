<?php
// ============================================================
//  controllers/notification_controller.php
// ============================================================

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Notification.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'mark_all_read') {
    $notifModel = new Notification();
    $notifModel->markAllRead(currentUserId());
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false]);
