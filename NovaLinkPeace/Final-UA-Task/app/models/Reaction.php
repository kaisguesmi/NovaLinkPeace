<?php

/**
 * Modèle Reaction : gestion des réactions aux posts.
 */
class Reaction extends Model
{
    protected string $table = 'Reaction';
    protected string $primaryKey = 'id_reaction';

    public function toggle(int $postId, int $userId, string $type): bool
    {
        // Check if reaction exists
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE post_id = :post_id AND id_utilisateur = :user_id AND type = :type"
        );
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
            'type' => $type
        ]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Remove reaction
            $stmt = $this->db->prepare(
                "DELETE FROM {$this->table} WHERE id_reaction = :id"
            );
            return $stmt->execute(['id' => $existing['id_reaction']]);
        } else {
            // Remove any other reaction from this user on this post
            $stmt = $this->db->prepare(
                "DELETE FROM {$this->table} WHERE post_id = :post_id AND id_utilisateur = :user_id"
            );
            $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);

            // Add new reaction
            return (bool) $this->create([
                'post_id' => $postId,
                'id_utilisateur' => $userId,
                'type' => $type
            ]);
        }
    }

    public function getByPost(int $postId): array
    {
        $sql = "SELECT type, COUNT(*) as count 
                FROM {$this->table} 
                WHERE post_id = :post_id 
                GROUP BY type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }

    public function getUserReaction(int $postId, int $userId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT type FROM {$this->table} WHERE post_id = :post_id AND id_utilisateur = :user_id LIMIT 1"
        );
        $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ? $result['type'] : null;
    }
}
