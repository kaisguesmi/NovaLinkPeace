<?php

class Notifications extends Model
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id';
    private ?bool $tableExists = null;

    private function ensureTableExists(): bool
    {
        if ($this->tableExists !== null) {
            return $this->tableExists;
        }

        try {
            $this->db->query("SELECT 1 FROM {$this->table} LIMIT 1");
            $this->tableExists = true;
        } catch (PDOException $e) {
            $this->tableExists = false;
        }

        return $this->tableExists;
    }

    public function getByUser(int $userId): array
    {
        if (!$this->ensureTableExists()) {
            return [];
        }

        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadByUser(int $userId): array
    {
        if (!$this->ensureTableExists()) {
            return [];
        }

        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id AND `read` = 0 ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnreadByUser(int $userId): int
    {
        if (!$this->ensureTableExists()) {
            return 0;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND `read` = 0");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    public function markAsRead(int $notificationId): bool
    {
        if (!$this->ensureTableExists()) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE {$this->table} SET `read` = 1 WHERE id = :id");
        return $stmt->execute(['id' => $notificationId]);
    }

    public function markAllRead(int $userId): bool
    {
        if (!$this->ensureTableExists()) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE {$this->table} SET `read` = 1 WHERE user_id = :user_id AND `read` = 0");
        return $stmt->execute(['user_id' => $userId]);
    }

    public function create(array $data): int
    {
        if (!$this->ensureTableExists()) {
            return 0; // Return 0 instead of null to match int return type
        }

        // Normalize data and avoid SQL issues with reserved word `read`
        $payload = [
            'user_id'    => $data['user_id'],
            'title'      => $data['title'] ?? 'Notification',
            'message'    => $data['message'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'read'       => 0,
        ];

        $sql = "INSERT INTO {$this->table} (user_id, title, message, created_at, `read`) 
                VALUES (:user_id, :title, :message, :created_at, :read)";

        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($payload)) {
            return (int) $this->db->lastInsertId();
        }
        
        return 0; // Return 0 instead of null to match int return type
    }
}
