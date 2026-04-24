<?php
// ============================================================
//  models/Post.php — Post Data Access Object
// ============================================================

class Post {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Get paginated feed with author info, like/comment counts */
    public function getFeed(int $page = 1, int $perPage = POSTS_PER_PAGE): array {
        $offset = ($page - 1) * $perPage;
        $stmt   = $this->db->prepare(
            'SELECT p.*,
                    u.username, u.full_name, u.avatar,
                    (SELECT COUNT(*) FROM likes    WHERE post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
             FROM posts p
             JOIN users u ON p.user_id = u.id
             ORDER BY p.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Count total posts (for pagination) */
    public function countAll(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    }

    /** Find a single post with full author + counts */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT p.*,
                    u.username, u.full_name, u.avatar,
                    (SELECT COUNT(*) FROM likes    WHERE post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Get all posts by a user */
    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT p.*,
                    (SELECT COUNT(*) FROM likes    WHERE post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
             FROM posts p
             WHERE p.user_id = ?
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Create a new post */
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO posts (user_id, title, description, image, category)
             VALUES (:user_id, :title, :description, :image, :category)'
        );
        $stmt->execute([
            'user_id'     => $data['user_id'],
            'title'       => $data['title'],
            'description' => $data['description'] ?? '',
            'image'       => $data['image'],
            'category'    => $data['category'] ?? 'autre',
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Delete a post (checks ownership) */
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    /** Get comments for a post */
    public function getComments(int $postId): array {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.username, u.avatar
             FROM comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.post_id = ?
             ORDER BY c.created_at ASC'
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    /** Add a comment */
    public function addComment(int $postId, int $userId, string $content): int {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)'
        );
        $stmt->execute([$postId, $userId, $content]);
        return (int)$this->db->lastInsertId();
    }

    /** Check if a user has liked a post */
    public function isLiked(int $postId, int $userId): bool {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM likes WHERE post_id = ? AND user_id = ?'
        );
        $stmt->execute([$postId, $userId]);
        return (bool)$stmt->fetch();
    }

    /** Toggle like — returns ['liked' => bool, 'count' => int] */
    public function toggleLike(int $postId, int $userId): array {
        if ($this->isLiked($postId, $userId)) {
            $stmt = $this->db->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?');
            $stmt->execute([$postId, $userId]);
            $liked = false;
        } else {
            $stmt = $this->db->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)');
            $stmt->execute([$postId, $userId]);
            $liked = true;
        }
        $count = (int)$this->db->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ?')
                               ->execute([$postId]) ? $this->db->query("SELECT COUNT(*) FROM likes WHERE post_id = $postId")->fetchColumn() : 0;

        // Re-fetch count safely
        $stmtC = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ?');
        $stmtC->execute([$postId]);
        $count = (int)$stmtC->fetchColumn();

        return ['liked' => $liked, 'count' => $count];
    }

    /** Search posts by title or description */
    public function search(string $query): array {
        $like = '%' . $query . '%';
        $stmt = $this->db->prepare(
            'SELECT p.*,
                    u.username, u.full_name, u.avatar,
                    (SELECT COUNT(*) FROM likes    WHERE post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.title LIKE ? OR p.description LIKE ?
             ORDER BY p.created_at DESC
             LIMIT 30'
        );
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }
}
