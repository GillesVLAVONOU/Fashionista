<?php
// ============================================================
//  models/Notification.php — Notification Data Access Object
// ============================================================

class Notification {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Create a notification */
    public function create(int $userId, int $fromUserId, string $type, string $message, ?int $postId = null): void {
        // Don't notify yourself
        if ($userId === $fromUserId) return;

        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, from_user_id, post_id, type, message)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $fromUserId, $postId, $type, $message]);
    }

    /** Get notifications for a user */
    public function getForUser(int $userId, int $limit = 30): array {
        $stmt = $this->db->prepare(
            'SELECT n.*, u.username AS from_username, u.avatar AS from_avatar
             FROM notifications n
             JOIN users u ON n.from_user_id = u.id
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Mark all notifications as read for a user */
    public function markAllRead(int $userId): void {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
    }

    /** Mark a single notification as read */
    public function markRead(int $id, int $userId): void {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
    }

    /** Count unread notifications for a user */
    public function countUnread(int $userId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
