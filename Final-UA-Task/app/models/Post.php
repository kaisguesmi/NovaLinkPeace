<?php

/**
 * Modèle Post : gestion des posts de type réseau social.
 */
class Post extends Model
{
    protected string $table = 'Post';
    protected string $primaryKey = 'id_post';

    public function getAllWithUsers(bool $includePending = false): array
    {
        // Check if avatar column exists, if not use NULL
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $baseSql = "SELECT p.*, 
                c.nom_complet, 
                {$avatarField},
                u.email
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                WHERE 1=1";

        $sqlWithCounts = "SELECT p.*, 
                c.nom_complet, 
                {$avatarField},
                u.email,
                (SELECT COUNT(*) FROM Reaction r WHERE r.post_id = p.id_post) as reaction_count,
                (SELECT COUNT(*) FROM PostComment pc WHERE pc.post_id = p.id_post) as comment_count
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                WHERE 1=1";
                
        $sql = $sqlWithCounts;
        if (!$includePending) {
            $sql .= " AND p.status = 'approved'";
        }
        $sql .= " ORDER BY p.date_creation DESC";

        try {
            return $this->db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            // Fallback if Reaction/PostComment tables do not exist.
            $sql = $baseSql;
            if (!$includePending) {
                $sql .= " AND p.status = 'approved'";
            }
            $sql .= " ORDER BY p.date_creation DESC";
            $rows = $this->db->query($sql)->fetchAll();
            foreach ($rows as &$row) {
                $row['reaction_count'] = 0;
                $row['comment_count'] = 0;
            }
            return $rows;
        }
    }

    public function getByIdWithUser(int $postId): ?array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sqlWithCounts = "SELECT p.*, 
                c.nom_complet, 
                {$avatarField},
                u.email,
                (SELECT COUNT(*) FROM Reaction r WHERE r.post_id = p.id_post) as reaction_count,
                (SELECT COUNT(*) FROM PostComment pc WHERE pc.post_id = p.id_post) as comment_count
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                WHERE p.id_post = :id";

        try {
            $stmt = $this->db->prepare($sqlWithCounts);
            $stmt->execute(['id' => $postId]);
            $post = $stmt->fetch();
            return $post ?: null;
        } catch (PDOException $e) {
            $sql = "SELECT p.*, 
                c.nom_complet, 
                {$avatarField},
                u.email
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                WHERE p.id_post = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $postId]);
            $post = $stmt->fetch();
            if (!$post) {
                return null;
            }
            $post['reaction_count'] = 0;
            $post['comment_count'] = 0;
            return $post;
        }
    }

    public function getByUserId(int $userId, bool $includePending = false): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $baseSql = "SELECT p.*, 
                c.nom_complet, 
                {$avatarField},
                u.email
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                WHERE p.id_auteur = :userId";

        $sql = "SELECT p.*, 
                c.nom_complet, 
                {$avatarField},
                u.email,
                (SELECT COUNT(*) FROM Reaction r WHERE r.post_id = p.id_post) as reaction_count,
                (SELECT COUNT(*) FROM PostComment pc WHERE pc.post_id = p.id_post) as comment_count
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                WHERE p.id_auteur = :userId";
                
        // When including pending, show approved + pending but never rejected
        if ($includePending) {
            $sql .= " AND p.status IN ('approved', 'pending')";
        } else {
            $sql .= " AND p.status = 'approved'";
        }
        
        $sql .= " ORDER BY p.date_creation DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['userId' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $sql = $baseSql;
            if ($includePending) {
                $sql .= " AND p.status IN ('approved', 'pending')";
            } else {
                $sql .= " AND p.status = 'approved'";
            }
            $sql .= " ORDER BY p.date_creation DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['userId' => $userId]);
            $rows = $stmt->fetchAll();
            foreach ($rows as &$row) {
                $row['reaction_count'] = 0;
                $row['comment_count'] = 0;
            }
            return $rows;
        }
    }
    
    /**
     * Get posts pending moderation
     */
    public function getPendingPosts()
    {
        return $this->findAll('status = ?', ['pending'], 'date_creation DESC');
    }
    
    /**
     * Get all posts with user info and comment count for admin
     */
    public function findAllWithUserAndCommentCount()
    {
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sqlWithCounts = "SELECT p.*, 
                c.nom_complet as author_name,
                {$avatarField},
                (SELECT COUNT(*) FROM PostComment pc WHERE pc.post_id = p.id_post) as comment_count
                FROM Post p
                LEFT JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                ORDER BY p.date_creation DESC";

        try {
            return $this->db->query($sqlWithCounts)->fetchAll();
        } catch (PDOException $e) {
            $sql = "SELECT p.*, 
                c.nom_complet as author_name,
                {$avatarField}
                FROM Post p
                LEFT JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                ORDER BY p.date_creation DESC";
            $rows = $this->db->query($sql)->fetchAll();
            foreach ($rows as &$row) {
                $row['comment_count'] = 0;
            }
            return $rows;
        }
    }
    
    /**
     * Approve a post
     */
    public function approvePost(int $postId, ?string $notes = null): bool
    {
        return $this->update($postId, [
            'status' => 'approved',
            'est_publie' => 1,
            'moderation_notes' => $notes
        ]);
    }
    
    /**
     * Reject a post
     */
    public function rejectPost(int $postId, string $reason): bool
    {
        return $this->update($postId, [
            'status' => 'rejected',
            'moderation_notes' => $reason
        ]);
    }

    /**
     * Get latest approved posts for the public Stories section (limited)
     */
    public function getLatestPublicStories(int $limit = 6): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }

        $sql = "SELECT
                    p.id_post,
                    p.titre,
                    p.contenu,
                    p.date_creation AS date_publication,
                    COALESCE(c.nom_complet, u.email) AS nom_complet,
                    {$avatarField} AS photo_profil
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                WHERE p.status = 'approved' AND p.est_publie = 1
                ORDER BY p.date_creation DESC
                LIMIT :limit";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log('Database error in getLatestPublicStories: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all approved posts for the public Stories listing
     */
    public function getAllPublicStories(): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarExpr = $checkColumn ? 'c.avatar' : 'NULL';
        } catch (Exception $e) {
            $avatarExpr = 'NULL';
        }

        $sql = "SELECT 
                    p.id_post AS id,
                    p.titre AS titre,
                    p.contenu AS contenu,
                    p.date_creation AS date_publication,
                    COALESCE(c.nom_complet, u.email) AS nom_complet,
                    " . $avatarExpr . " AS photo_profil
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = p.id_auteur
                WHERE p.status = 'approved' AND p.est_publie = 1
                ORDER BY p.date_creation DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
