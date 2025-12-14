<?php

/**
 * Modèle PostComment : gestion des commentaires sur les posts.
 */
class PostComment extends Model
{
    protected string $table = 'PostComment';
    protected string $primaryKey = 'id_comment';

    /**
     * Get comments for a specific post with user info
     */
    public function getByPost(int $postId, bool $includePending = false): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sql = "SELECT pc.*, 
                c.nom_complet as author_name, 
                {$avatarField},
                u.email,
                p.titre as post_title
                FROM postcomment pc
                JOIN utilisateur u ON u.id_utilisateur = pc.user_id
                LEFT JOIN client c ON c.id_utilisateur = pc.user_id
                JOIN post p ON p.id_post = pc.post_id
                WHERE pc.post_id = :post_id";
                
        // Comments are no longer moderated: always return all comments for the post.
        
        $sql .= " ORDER BY pc.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all comments with user info
     */
    public function getAllWithUsers(bool $includePending = false): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sql = "SELECT pc.*, 
                c.nom_complet as author_name, 
                {$avatarField},
                u.email,
                p.titre as post_title
                FROM postcomment pc
                JOIN utilisateur u ON u.id_utilisateur = pc.user_id
                LEFT JOIN client c ON c.id_utilisateur = pc.user_id
                JOIN post p ON p.id_post = pc.post_id";
                
        if (!$includePending) {
            $sql .= " WHERE pc.status = 'approved'";
        }
        
        $sql .= " ORDER BY pc.created_at DESC";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Get comments pending moderation
     */
    public function getPendingComments(): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sql = "SELECT pc.*, 
                c.nom_complet as author_name, 
                {$avatarField},
                u.email,
                p.titre as post_title
                FROM postcomment pc
                JOIN utilisateur u ON u.id_utilisateur = pc.user_id
                LEFT JOIN client c ON c.id_utilisateur = pc.user_id
                JOIN post p ON p.id_post = pc.post_id
                WHERE pc.status = 'pending'
                ORDER BY pc.created_at ASC";
                
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Approve a comment
     */
    public function approve(int $commentId): bool
    {
        return $this->update($commentId, [
            'status' => 'approved',
            'moderated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Reject a comment
     */
    public function reject(int $commentId, string $reason): bool
    {
        return $this->update($commentId, [
            'status' => 'rejected',
            'moderation_notes' => $reason,
            'moderated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get comments for a specific user
     */
    public function getByUser(int $userId, bool $includePending = false): array
    {
        $sql = "SELECT pc.*, p.titre as post_title 
                FROM postcomment pc
                JOIN post p ON p.id_post = pc.post_id
                WHERE pc.user_id = :user_id";
                
        if (!$includePending) {
            $sql .= " AND pc.status = 'approved'";
        }
        
        $sql .= " ORDER BY pc.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
