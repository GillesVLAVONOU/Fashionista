<?php
// ============================================================
//  controllers/event_controller.php — Event Participation (AJAX)
// ============================================================

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Event.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => SITE_URL . '/login.php']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$action  = $_POST['action']   ?? '';
$eventId = (int)($_POST['event_id'] ?? 0);

if ($action === 'participate' && $eventId > 0) {
    $eventModel = new Event();
    $result     = $eventModel->toggleParticipation($eventId, currentUserId());

    if (!empty($result['full'])) {
        echo json_encode(['success' => false, 'message' => 'Cet événement est complet.']);
        exit;
    }

    echo json_encode([
        'success'       => true,
        'participating' => $result['participating'],
        'count'         => $result['count'],
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
