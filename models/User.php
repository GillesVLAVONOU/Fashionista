<?php
// ============================================================
//  models/User.php — User Data Access Object
// ============================================================

class User {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Find a user by ID */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Find a user by email */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /** Find a user by username */
    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    /** Create a new user — returns new user ID */
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, full_name, email, password, bio, avatar)
             VALUES (:username, :full_name, :email, :password, :bio, :avatar)'
        );
        $stmt->execute([
            'username'  => $data['username'],
            'full_name' => $data['full_name'],
            'email'     => $data['email'],
            'password'  => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
            'bio'       => $data['bio'] ?? null,
            'avatar'    => $data['avatar'] ?? 'default_avatar.png',
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Update user profile fields */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];

        foreach (['full_name', 'bio', 'avatar'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;

        $sql  = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /** Update password */
    public function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
        return $stmt->rowCount() > 0;
    }

    /** Verify password against stored hash */
    public function verifyPassword(string $plain, string $hash): bool {
        return password_verify($plain, $hash);
    }

    /** Count user's total likes received */
    public function totalLikesReceived(int $userId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM likes l
             JOIN posts p ON l.post_id = p.id
             WHERE p.user_id = ?'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Count user's posts */
    public function postCount(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM posts WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Count events user is participating in */
    public function eventCount(int $userId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM event_participants WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Search users by name or username */
    public function search(string $query, int $limit = 20): array {
        $like = '%' . $query . '%';
        $stmt = $this->db->prepare(
            'SELECT id, username, full_name, avatar, bio
             FROM users
             WHERE username LIKE ? OR full_name LIKE ?
             LIMIT ?'
        );
        $stmt->execute([$like, $like, $limit]);
        return $stmt->fetchAll();
    }
}
