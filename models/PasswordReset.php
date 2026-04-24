<?php
// ============================================================
//  models/PasswordReset.php - Password reset tokens
// ============================================================

class PasswordReset {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS password_resets (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                token_hash CHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                used_at DATETIME DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_pr_user (user_id),
                KEY idx_pr_token (token_hash),
                CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function createForUser(int $userId, int $ttlSeconds = 3600): string {
        $this->db->prepare(
            'UPDATE password_resets
             SET used_at = NOW()
             WHERE user_id = ? AND used_at IS NULL'
        )->execute([$userId]);

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + $ttlSeconds);

        $stmt = $this->db->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $tokenHash, $expiresAt]);

        return $token;
    }

    public function findValidToken(string $token): ?array {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare(
            'SELECT *
             FROM password_resets
             WHERE token_hash = ?
               AND used_at IS NULL
               AND expires_at > NOW()
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function markUsed(int $id): void {
        $stmt = $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }
}
