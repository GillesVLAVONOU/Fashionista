<?php
// ============================================================
//  includes/auth.php — Session & Authentication Helpers
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require the user to be logged in.
 * Redirects to login page with a return URL if not authenticated.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        $returnTo = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header('Location: ' . SITE_URL . '/login.php?redirect=' . $returnTo);
        exit;
    }
}

/**
 * Returns true if a user session is active.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Returns the current user's ID or null.
 */
function currentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Returns the full current user array from DB, or null.
 */
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([currentUserId()]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

/**
 * Returns true if the current user is an admin.
 */
function isAdmin(): bool {
    $u = currentUser();
    return $u && $u['role'] === 'admin';
}

/**
 * Start a user session after successful login.
 */
function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];
}

/**
 * Destroy the current session (logout).
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Count unread notifications for the current user.
 */
function countUnreadNotifications(): int {
    if (!isLoggedIn()) return 0;
    $db   = getDB();
    $stmt = $db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([currentUserId()]);
    return (int)$stmt->fetchColumn();
}
