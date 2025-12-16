<?php

/**
 * Modèle Histoire : opérations CRUD + agrégations.
 */
class Histoire extends Model
{
    // Table Histoire: id_histoire, id_client, titre, contenu, status, rejection_reason, notification_sent, date_publication
    protected string $table = 'Histoire';
    protected string $primaryKey = 'id_histoire';
    private ?bool $statusColumnExists = null;

    private function hasStatusColumn(): bool
    {
        if ($this->statusColumnExists !== null) {
            return $this->statusColumnExists;
        }

        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM Histoire LIKE 'status'");
            $this->statusColumnExists = (bool) $stmt->fetch();
        } catch (Exception $e) {
            $this->statusColumnExists = false;
        }

        return $this->statusColumnExists;
    }

    public function getWithClients(?string $status = null): array
    {
        $sql = "SELECT h.*, c.nom_complet 
                FROM Histoire h
                JOIN Client c ON c.id_utilisateur = h.id_client";

        $params = [];
        if ($status !== null && $this->hasStatusColumn()) {
            $sql .= " WHERE h.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY h.date_publication DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWithComments(int $storyId): ?array
    {
        $story = $this->findById($storyId);
        if (!$story) {
            return null;
        }

        $commentModel = new Commentaire();
        $story['commentaires'] = $commentModel->getByStory($storyId);
        return $story;
    }

    /**
     * Les réactions sont implémentées sous forme de commentaires courts (emoji/mot-clé).
     */
    public function react(int $storyId, int $userId, string $emoji): bool
    {
        $reaction = [
            'contenu' => "[reaction] {$emoji}",
            'id_utilisateur' => $userId,
            'id_histoire' => $storyId,
        ];

        $commentModel = new Commentaire();
        return (bool) $commentModel->create($reaction);
    }

    public function moderate(int $id, string $status, ?string $reason = null): bool
    {
        if (!$this->hasStatusColumn()) {
            return false;
        }

        return $this->update($id, [
            'status' => $status,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Get latest published stories with user information
     */
    public function getLatestStories(int $limit = 6): array
    {
        if ($this->hasStatusColumn()) {
            $sql = "SELECT 
                        h.id_histoire AS id,
                        h.titre,
                        h.contenu,
                        h.date_publication,
                        c.nom_complet, 
                        c.avatar AS photo_profil 
                    FROM Histoire h
                    JOIN Client c ON c.id_utilisateur = h.id_client
                    WHERE h.status = 'approved'
                    ORDER BY h.date_publication DESC
                    LIMIT :limit";
        } else {
            $sql = "SELECT 
                        h.id_histoire AS id,
                        h.titre,
                        h.contenu,
                        h.date_publication,
                        c.nom_complet, 
                        c.avatar AS photo_profil 
                    FROM Histoire h
                    JOIN Client c ON c.id_utilisateur = h.id_client
                    ORDER BY h.date_publication DESC
                    LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all published stories with user information
     */
    public function getAllStories(): array
    {
        if ($this->hasStatusColumn()) {
            $sql = "SELECT 
                        h.id_histoire AS id,
                        h.titre,
                        h.contenu,
                        h.date_publication,
                        c.nom_complet, 
                        c.avatar AS photo_profil 
                    FROM Histoire h
                    JOIN Client c ON c.id_utilisateur = h.id_client
                    WHERE h.status = 'approved'
                    ORDER BY h.date_publication DESC";
        } else {
            $sql = "SELECT 
                        h.id_histoire AS id,
                        h.titre,
                        h.contenu,
                        h.date_publication,
                        c.nom_complet, 
                        c.avatar AS photo_profil 
                    FROM Histoire h
                    JOIN Client c ON c.id_utilisateur = h.id_client
                    ORDER BY h.date_publication DESC";
        }

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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

    public function updateStory(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function deleteStory(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Get all approved stories with user information
     */
    public function getAllApprovedWithUsers(): array
    {
        $sql = "SELECT 
                    h.*, 
                    c.nom_complet,
                    c.avatar,
                    (SELECT COUNT(*) FROM Commentaire cm WHERE cm.id_histoire = h.id_histoire) as comment_count
                FROM Histoire h
                JOIN Client c ON c.id_utilisateur = h.id_client
                WHERE h.statut = 'approved'
                ORDER BY h.date_publication DESC";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all pending stories with user information
     */
    public function getAllPendingWithUsers(): array
    {
        $sql = "SELECT 
                    h.*, 
                    c.nom_complet,
                    c.avatar,
                    (SELECT COUNT(*) FROM Commentaire cm WHERE cm.id_histoire = h.id_histoire) as comment_count
                FROM Histoire h
                JOIN Client c ON c.id_utilisateur = h.id_client
                WHERE h.statut = 'pending'
                ORDER BY h.date_publication DESC";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}

