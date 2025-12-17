<?php

require_once __DIR__ . '/../config.php';

class HistoireModel
{
    /**
     * Liste toutes les histoires avec auteur et statut.
     */
    public static function getAll()
    {
        global $pdo;

        $sql = "SELECT 
                    h.id_histoire AS id,
                    h.titre,
                    h.contenu,
                    h.statut,
                    h.date_publication,
                    COALESCE(c.nom_complet, u.email) AS auteur_nom
                FROM histoire h
                LEFT JOIN Client c ON c.id_utilisateur = h.id_auteur
                LEFT JOIN Utilisateur u ON u.id_utilisateur = h.id_auteur
                ORDER BY h.date_publication DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprime une histoire ainsi que ses dépendances pour éviter les orphelins.
     */
    public static function deleteById($id)
    {
        global $pdo;

        try {
            $pdo->beginTransaction();

            // Récupérer l'auteur pour nettoyer ses conversations liées aux experts
            $stmtAuthor = $pdo->prepare('SELECT id_auteur FROM histoire WHERE id_histoire = :id LIMIT 1');
            $stmtAuthor->execute([':id' => $id]);
            $authorId = (int)($stmtAuthor->fetchColumn() ?: 0);

            // Nettoyage des dépendances éventuelles
            $pdo->prepare('DELETE FROM reclamation WHERE id_histoire_cible = :id')->execute([':id' => $id]);
            $pdo->prepare('DELETE FROM reaction_histoire WHERE id_histoire = :id')->execute([':id' => $id]);
            $pdo->prepare('DELETE FROM commentaire WHERE id_histoire = :id')->execute([':id' => $id]);

            // Supprimer les messages privés et conversations liés à l'auteur (client) si existants
            if ($authorId > 0) {
                $pdo->prepare('DELETE mp FROM message_prive mp JOIN conversation c ON c.id_conversation = mp.id_conversation WHERE c.id_client = :cid')->execute([':cid' => $authorId]);
                $pdo->prepare('DELETE FROM conversation WHERE id_client = :cid')->execute([':cid' => $authorId]);
            }

            // Suppression de l'histoire
            $ok = $pdo->prepare('DELETE FROM histoire WHERE id_histoire = :id')->execute([':id' => $id]);
            $pdo->commit();

            return $ok;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Erreur suppression histoire: ' . $e->getMessage());
            return false;
        }
    }
}
