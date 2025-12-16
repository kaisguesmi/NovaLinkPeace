<?php
class Histoire {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllStories() {
        $sql = "SELECT h.id_histoire, h.titre, h.contenu, h.statut, h.date_publication, h.id_auteur,
                       COALESCE(c.nom_complet, u.email) AS auteur_nom
                FROM histoire h
                LEFT JOIN Client c ON c.id_utilisateur = h.id_auteur
                LEFT JOIN Utilisateur u ON u.id_utilisateur = h.id_auteur
                ORDER BY h.date_publication DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStoryById($id) {
        $sql = "SELECT h.id_histoire, h.titre, h.contenu, h.statut, h.date_publication, h.id_auteur,
                       COALESCE(c.nom_complet, u.email) AS auteur_nom
                FROM histoire h
                LEFT JOIN Client c ON c.id_utilisateur = h.id_auteur
                LEFT JOIN Utilisateur u ON u.id_utilisateur = h.id_auteur
                WHERE h.id_histoire = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCauses() {
        $stmt = $this->conn->prepare("SELECT id_cause, libelle FROM cause_signalement ORDER BY id_cause ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addReclamation($idAuteur, $idHistoire, $description, array $causes) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("INSERT INTO reclamation (description_personnalisee, statut, id_auteur, id_histoire_cible) VALUES (?, 'nouvelle', ?, ?)");
            $stmt->execute([$description, $idAuteur, $idHistoire]);
            $reclamationId = $this->conn->lastInsertId();

            if (!empty($causes)) {
                $rcStmt = $this->conn->prepare("INSERT INTO reclamation_cause (id_reclamation, id_cause) VALUES (?, ?)");
                foreach ($causes as $causeId) {
                    $rcStmt->execute([$reclamationId, $causeId]);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getAllReclamations() {
        $sql = "SELECT r.id_reclamation, r.description_personnalisee, r.statut, r.id_auteur, r.id_histoire_cible, r.id_commentaire_cible,
                       u.email AS auteur_email, COALESCE(c.nom_complet, u.email) AS auteur_nom,
                       h.titre AS histoire_titre
                FROM reclamation r
                LEFT JOIN Utilisateur u ON u.id_utilisateur = r.id_auteur
                LEFT JOIN Client c ON c.id_utilisateur = r.id_auteur
                LEFT JOIN histoire h ON h.id_histoire = r.id_histoire_cible
                ORDER BY r.id_reclamation DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReclamation($idReclamation) {
        $stmt = $this->conn->prepare("SELECT * FROM reclamation WHERE id_reclamation = ?");
        $stmt->execute([$idReclamation]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addStory($idAuteur, $titre, $contenu) {
        $sql = "INSERT INTO histoire (titre, contenu, statut, date_publication, id_auteur) VALUES (?, ?, 'publiee', NOW(), ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$titre, $contenu, $idAuteur]);
    }

    public function updateReclamationStatus($idReclamation, $statut) {
        $stmt = $this->conn->prepare("UPDATE reclamation SET statut = ? WHERE id_reclamation = ?");
        return $stmt->execute([$statut, $idReclamation]);
    }

    public function deleteStory($idHistoire) {
        $stmt = $this->conn->prepare("DELETE FROM histoire WHERE id_histoire = ?");
        return $stmt->execute([$idHistoire]);
    }

    public function addComment($idHistoire, $idUser, $contenu) {
        $stmt = $this->conn->prepare("INSERT INTO commentaire (contenu, id_utilisateur, id_histoire, status) VALUES (?, ?, ?, 'approved')");
        return $stmt->execute([$contenu, $idUser, $idHistoire]);
    }

    public function getComments($idHistoire) {
        $sql = "SELECT cm.id_commentaire, cm.contenu, cm.date_publication, cm.id_utilisateur, 
                       COALESCE(cl.nom_complet, u.email) AS auteur_nom
                FROM commentaire cm
                LEFT JOIN Client cl ON cl.id_utilisateur = cm.id_utilisateur
                LEFT JOIN Utilisateur u ON u.id_utilisateur = cm.id_utilisateur
                WHERE cm.id_histoire = ?
                ORDER BY cm.date_publication DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idHistoire]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleReaction($idHistoire, $idUser, $type) {
        // Remove if same reaction exists; otherwise replace
        $stmt = $this->conn->prepare("SELECT id_reaction, type FROM reaction_histoire WHERE id_histoire = ? AND id_utilisateur = ?");
        $stmt->execute([$idHistoire, $idUser]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing && $existing['type'] === $type) {
            $del = $this->conn->prepare("DELETE FROM reaction_histoire WHERE id_reaction = ?");
            return $del->execute([$existing['id_reaction']]);
        }

        // replace any other reaction for this user/story
        $delOther = $this->conn->prepare("DELETE FROM reaction_histoire WHERE id_histoire = ? AND id_utilisateur = ?");
        $delOther->execute([$idHistoire, $idUser]);

        $ins = $this->conn->prepare("INSERT INTO reaction_histoire (id_histoire, id_utilisateur, type) VALUES (?, ?, ?)");
        return $ins->execute([$idHistoire, $idUser, $type]);
    }

    public function getReactions($idHistoire) {
        $stmt = $this->conn->prepare("SELECT type, COUNT(*) AS count FROM reaction_histoire WHERE id_histoire = ? GROUP BY type");
        $stmt->execute([$idHistoire]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = ['like'=>0,'dislike'=>0,'love'=>0,'laugh'=>0,'angry'=>0];
        foreach ($rows as $r) {
            $result[$r['type']] = (int)$r['count'];
        }
        return $result;
    }

    public function getUserReaction($idHistoire, $idUser) {
        $stmt = $this->conn->prepare("SELECT type FROM reaction_histoire WHERE id_histoire = ? AND id_utilisateur = ? LIMIT 1");
        $stmt->execute([$idHistoire, $idUser]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['type'] : null;
    }
}
?>
