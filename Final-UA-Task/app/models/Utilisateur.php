<?php

/**
 * Modèle Utilisateur : gère les opérations d'authentification de base.
 */
class Utilisateur extends Model
{
    protected string $table = 'Utilisateur';
    protected string $primaryKey = 'id_utilisateur';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}

