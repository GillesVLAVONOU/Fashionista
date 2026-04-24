<?php
// ============================================================
//  models/Event.php — Event Data Access Object
// ============================================================

class Event {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Get all upcoming events */
    public function getUpcoming(int $limit = 20): array {
        $stmt = $this->db->prepare(
            'SELECT e.*,
                    u.username AS creator_name,
                    (SELECT COUNT(*) FROM event_participants WHERE event_id = e.id) AS participant_count
             FROM events e
             JOIN users u ON e.created_by = u.id
             WHERE e.event_date >= NOW()
             ORDER BY e.event_date ASC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Get all events (including past) */
    public function getAll(): array {
        $stmt = $this->db->prepare(
            'SELECT e.*,
                    u.username AS creator_name,
                    (SELECT COUNT(*) FROM event_participants WHERE event_id = e.id) AS participant_count
             FROM events e
             JOIN users u ON e.created_by = u.id
             ORDER BY e.event_date DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Find an event by ID */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT e.*,
                    u.username AS creator_name, u.full_name AS creator_fullname,
                    (SELECT COUNT(*) FROM event_participants WHERE event_id = e.id) AS participant_count
             FROM events e
             JOIN users u ON e.created_by = u.id
             WHERE e.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Check if a user is participating */
    public function isParticipating(int $eventId, int $userId): bool {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM event_participants WHERE event_id = ? AND user_id = ?'
        );
        $stmt->execute([$eventId, $userId]);
        return (bool)$stmt->fetch();
    }

    /** Toggle participation — returns ['participating' => bool, 'count' => int] */
    public function toggleParticipation(int $eventId, int $userId): array {
        if ($this->isParticipating($eventId, $userId)) {
            $stmt = $this->db->prepare(
                'DELETE FROM event_participants WHERE event_id = ? AND user_id = ?'
            );
            $stmt->execute([$eventId, $userId]);
            $participating = false;
        } else {
            // Check max_participants
            $event = $this->findById($eventId);
            if ($event && $event['max_participants'] !== null) {
                if ((int)$event['participant_count'] >= (int)$event['max_participants']) {
                    return ['participating' => false, 'count' => (int)$event['participant_count'], 'full' => true];
                }
            }
            $stmt = $this->db->prepare(
                'INSERT INTO event_participants (event_id, user_id) VALUES (?, ?)'
            );
            $stmt->execute([$eventId, $userId]);
            $participating = true;
        }

        $stmtC = $this->db->prepare(
            'SELECT COUNT(*) FROM event_participants WHERE event_id = ?'
        );
        $stmtC->execute([$eventId]);
        $count = (int)$stmtC->fetchColumn();

        return ['participating' => $participating, 'count' => $count, 'full' => false];
    }

    /** Get participants list for an event */
    public function getParticipants(int $eventId): array {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.username, u.full_name, u.avatar, ep.registered_at
             FROM event_participants ep
             JOIN users u ON ep.user_id = u.id
             WHERE ep.event_id = ?
             ORDER BY ep.registered_at ASC'
        );
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    /** Create a new event (admin) */
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO events (title, description, image, location, event_date, type, max_participants, created_by)
             VALUES (:title, :description, :image, :location, :event_date, :type, :max_participants, :created_by)'
        );
        $stmt->execute([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? '',
            'image'            => $data['image']       ?? 'default_event.png',
            'location'         => $data['location']    ?? '',
            'event_date'       => $data['event_date'],
            'type'             => $data['type']        ?? 'autre',
            'max_participants' => $data['max_participants'] ? (int)$data['max_participants'] : null,
            'created_by'       => $data['created_by'],
        ]);
        return (int)$this->db->lastInsertId();
    }
}
