<?php

class Commentaire extends Model
{
    protected string $table = 'Commentaire';
    protected string $primaryKey = 'id_commentaire';

    public function getByStory(int $storyId): array
    {
        $sql = "SELECT com.*, u.email 
                FROM Commentaire com
                JOIN Utilisateur u ON u.id_utilisateur = com.id_utilisateur
                WHERE com.id_histoire = :id
                ORDER BY com.date_publication DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $storyId]);
        return $stmt->fetchAll();
    }

    public function getAllWithUsers(): array
    {
        $sql = "SELECT com.*, u.email 
                FROM Commentaire com
                JOIN Utilisateur u ON u.id_utilisateur = com.id_utilisateur
                ORDER BY com.date_publication DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): ?array
    {
        return $this->findById($id);
    }

    public function update(int $id, array $data): bool
    {
        return parent::update($id, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function updateComment(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function deleteComment(int $id): bool
    {
        return $this->delete($id);
    }

    public function deleteByStory(int $storyId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id_histoire = :id");
        return $stmt->execute(['id' => $storyId]);
    }
}

