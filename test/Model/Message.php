<?php
// test/Model/Message.php
class Message {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Créer ou récupérer une conversation entre un expert et un client
     */
    public function getOrCreateConversation($idExpert, $idClient) {
        // Vérifier si une conversation existe déjà
        $sql = "SELECT id_conversation FROM conversation 
                WHERE id_expert = :expert AND id_client = :client LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':expert' => $idExpert, ':client' => $idClient]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['id_conversation'];
        }
        
        // Créer une nouvelle conversation
        $sql = "INSERT INTO conversation (id_expert, id_client) VALUES (:expert, :client)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':expert' => $idExpert, ':client' => $idClient]);
        
        return $this->conn->lastInsertId();
    }

    /**
     * Envoyer un message d'un expert vers un client
     */
    public function sendMessage($idExpert, $idClient, $contenu, $idHistoire = null) {
        try {
            // Obtenir ou créer la conversation
            $idConv = $this->getOrCreateConversation($idExpert, $idClient);
            
            // Vérifier si la conversation est ouverte et sous la limite de 5 messages
            $sqlCheck = "SELECT message_count, statut FROM conversation WHERE id_conversation = :conv";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->execute([':conv' => $idConv]);
            $conv = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($conv['statut'] === 'fermee' || $conv['message_count'] >= 5) {
                return false; // Conversation fermée ou limite atteinte
            }
            
            // Insérer le message
            $sql = "INSERT INTO message_prive (id_expert, id_client, id_conversation, id_histoire, contenu) 
                    VALUES (:expert, :client, :conv, :histoire, :contenu)";
            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([
                ':expert' => $idExpert,
                ':client' => $idClient,
                ':conv' => $idConv,
                ':histoire' => $idHistoire,
                ':contenu' => $contenu
            ]);
            
            if ($success) {
                // Incrémenter le compteur de messages
                $newCount = $conv['message_count'] + 1;
                $sqlUpdate = "UPDATE conversation SET message_count = :count";
                
                // Fermer la conversation si on atteint 5 messages
                if ($newCount >= 5) {
                    $sqlUpdate .= ", statut = 'fermee'";
                }
                
                $sqlUpdate .= " WHERE id_conversation = :conv";
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute([':count' => $newCount, ':conv' => $idConv]);
            }
            
            return $success;
        } catch (Exception $e) {
            error_log("Error sending message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer tous les messages d'une conversation
     */
    public function getConversationMessages($idExpert, $idClient) {
        $sql = "SELECT m.*, 
                       e.nom_complet as expert_nom, 
                       c.nom_complet as client_nom,
                       h.titre as histoire_titre
                FROM message_prive m
                JOIN Expert e ON e.id_utilisateur = m.id_expert
                JOIN Client c ON c.id_utilisateur = m.id_client
                LEFT JOIN histoire h ON h.id_histoire = m.id_histoire
                WHERE m.id_expert = :expert AND m.id_client = :client
                ORDER BY m.date_envoi ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':expert' => $idExpert, ':client' => $idClient]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer toutes les conversations d'un client
     */
    public function getClientConversations($idClient) {
        $sql = "SELECT DISTINCT c.id_conversation, c.id_expert, c.derniere_activite, 
                       c.message_count, c.statut,
                       e.nom_complet as expert_nom,
                       e.specialite as expert_specialite,
                       (SELECT COUNT(*) FROM message_prive 
                        WHERE id_conversation = c.id_conversation AND lu = FALSE AND id_client = :client) as unread_count
                FROM conversation c
                JOIN Expert e ON e.id_utilisateur = c.id_expert
                WHERE c.id_client = :client
                ORDER BY c.derniere_activite DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':client' => $idClient]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compter les messages non lus pour un client
     */
    public function getUnreadCount($idClient) {
        $sql = "SELECT COUNT(*) as count FROM message_prive 
                WHERE id_client = :client AND lu = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':client' => $idClient]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['count'];
    }

    /**
     * Marquer les messages d'une conversation comme lus
     */
    public function markAsRead($idConversation, $idClient) {
        $sql = "UPDATE message_prive SET lu = TRUE 
                WHERE id_conversation = :conv AND id_client = :client AND lu = FALSE";
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([':conv' => $idConversation, ':client' => $idClient]);
    }

    /**
     * Récupérer toutes les histoires publiées (pour les experts)
     */
    public function getAllStoriesForExperts() {
        $sql = "SELECT h.id_histoire, h.titre, h.contenu, h.date_publication,
                       c.nom_complet as auteur_nom, c.id_utilisateur as auteur_id,
                       u.photo_profil
                FROM histoire h
                JOIN Client c ON c.id_utilisateur = h.id_auteur
                JOIN Utilisateur u ON u.id_utilisateur = h.id_auteur
                WHERE h.statut = 'publiee'
                ORDER BY h.date_publication DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifier si un utilisateur est un expert
     */
    public function isExpert($idUtilisateur) {
        $sql = "SELECT id_utilisateur FROM Expert WHERE id_utilisateur = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $idUtilisateur]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Récupérer toutes les conversations d'un expert
     */
    public function getExpertConversations($idExpert) {
        $sql = "SELECT c.id_conversation, c.id_client, c.derniere_activite,
                       c.message_count, c.statut,
                       cl.nom_complet as client_nom,
                       (SELECT contenu FROM message_prive 
                        WHERE id_conversation = c.id_conversation 
                        ORDER BY date_envoi DESC LIMIT 1) as dernier_message,
                       (SELECT COUNT(*) FROM message_prive 
                        WHERE id_conversation = c.id_conversation) as total_messages
                FROM conversation c
                JOIN Client cl ON cl.id_utilisateur = c.id_client
                WHERE c.id_expert = :expert
                ORDER BY c.derniere_activite DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':expert' => $idExpert]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
