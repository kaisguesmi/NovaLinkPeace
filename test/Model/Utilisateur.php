<?php
class Utilisateur {
    private $conn;

    // Le constructeur ne change pas
    public function __construct($db) {
        $this->conn = $db;
    }

    // Méthode générique pour créer n'importe quel type d'utilisateur
    // Elle prend un tableau de données en paramètre
    public function create($data) {
        
        $this->conn->beginTransaction();

        try {
            // --- Étape 1: Insertion commune dans la table Utilisateur ---
            $query_user = "INSERT INTO Utilisateur (email, mot_de_passe_hash) VALUES (:email, :mot_de_passe_hash)";
            
            $stmt_user = $this->conn->prepare($query_user);

            // "Nettoyer" et lier les données communes
            $email = htmlspecialchars(strip_tags($data['email']));
            $password_hash = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);
            
            $stmt_user->bindParam(':email', $email);
            $stmt_user->bindParam(':mot_de_passe_hash', $password_hash);
            
            $stmt_user->execute();
            
            $last_id = $this->conn->lastInsertId();

            // --- Étape 2: Insertion spécifique basée sur le rôle ---
            // On utilise un switch pour choisir la bonne requête
            switch ($data['role']) {
                case 'client':
                    $query_role = "INSERT INTO Client (id_utilisateur, nom_complet, bio) VALUES (:id_utilisateur, :nom_complet, :bio)";
                    $stmt_role = $this->conn->prepare($query_role);

                    $nom_complet = htmlspecialchars(strip_tags($data['nom_complet']));
                    $bio = htmlspecialchars(strip_tags($data['bio']));

                    $stmt_role->bindParam(':id_utilisateur', $last_id);
                    $stmt_role->bindParam(':nom_complet', $nom_complet);
                    $stmt_role->bindParam(':bio', $bio);
                    break;

                case 'organisation':
                    $query_role = "INSERT INTO Organisation (id_utilisateur, nom_organisation, adresse) VALUES (:id_utilisateur, :nom_organisation, :adresse)";
                    $stmt_role = $this->conn->prepare($query_role);

                    $nom_organisation = htmlspecialchars(strip_tags($data['nom_organisation']));
                    $adresse = htmlspecialchars(strip_tags($data['adresse']));
                    
                    $stmt_role->bindParam(':id_utilisateur', $last_id);
                    $stmt_role->bindParam(':nom_organisation', $nom_organisation);
                    $stmt_role->bindParam(':adresse', $adresse);
                    // Le statut de vérification a une valeur par défaut dans la BDD
                    break;
                
                
                default:
                    // Si le rôle n'est pas reconnu, on annule tout.
                    throw new Exception("Rôle utilisateur non valide.");
            }

            // Exécuter la requête spécifique au rôle
            $stmt_role->execute();
            
            // Si tout s'est bien passé, on valide la transaction
            $this->conn->commit();
            return $last_id;

        } catch (Exception $e) {
            // En cas d'erreur, on annule tout
            $this->conn->rollBack();
            // error_log($e->getMessage()); // Bonne pratique: logger l'erreur
            return false;
        }
    }
    public function findByEmail($email) {
        $query = "SELECT id_utilisateur, email, mot_de_passe_hash FROM Utilisateur WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC); // Renvoie l'utilisateur ou false s'il n'est pas trouvé
    }

    public function findClientById($id) {
        // On ajoute u.date_inscription à la requête SELECT
        $query = "SELECT u.id_utilisateur, u.email, u.date_inscription, u.photo_profil, 
                         c.nom_complet, c.bio 
                  FROM Utilisateur u
                  JOIN Client c ON u.id_utilisateur = c.id_utilisateur
                  WHERE u.id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // NOUVELLE MÉTHODE: Mettre à jour les informations d'un Client
    public function updateClient($id, $nom_complet, $bio) {
        $query = "UPDATE Client SET nom_complet = :nom_complet, bio = :bio WHERE id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $nom_complet = htmlspecialchars(strip_tags($nom_complet));
        $bio = htmlspecialchars(strip_tags($bio));
        $id = htmlspecialchars(strip_tags($id));
        
        // Lier les paramètres
        $stmt->bindParam(':nom_complet', $nom_complet);
        $stmt->bindParam(':bio', $bio);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Exécuter
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    // Récupérer le rôle d'un utilisateur connecté
    public function getUserRole($id_utilisateur) {
        // On vérifie dans chaque table spécifique
        // Est-ce un Admin ?
        $query = "SELECT id_utilisateur FROM Admin WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_utilisateur);
        $stmt->execute();
        if($stmt->rowCount() > 0) return 'admin';

        // Est-ce une Organisation ?
        $query = "SELECT id_utilisateur FROM Organisation WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_utilisateur);
        $stmt->execute();
        if($stmt->rowCount() > 0) return 'organisation';

        // Est-ce un Client ?
        $query = "SELECT id_utilisateur FROM Client WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_utilisateur);
        $stmt->execute();
        if($stmt->rowCount() > 0) return 'client';

        return 'inconnu';
    }

    // Récupérer toutes les infos d'une Organisation (pour le profil ou session)
    public function findOrganisationById($id) {
        $query = "SELECT u.id_utilisateur, u.email, u.date_inscription, u.photo_profil, 
                         o.nom_organisation, o.adresse, o.statut_verification 
                  FROM Utilisateur u
                  JOIN Organisation o ON u.id_utilisateur = o.id_utilisateur
                  WHERE u.id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updatePhoto($id, $filename) {
        $query = "UPDATE Utilisateur SET photo_profil = :photo WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':photo', $filename);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    // Mise à jour pour l'Organisation
    public function updateOrganisation($id, $nom_organisation, $adresse) {
        $query = "UPDATE Organisation SET nom_organisation = :nom, adresse = :adresse WHERE id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);

        $nom = htmlspecialchars(strip_tags($nom_organisation));
        $adresse = htmlspecialchars(strip_tags($adresse));
        $id = htmlspecialchars(strip_tags($id));
        
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
    // --- PARTIE ADMIN : STATISTIQUES ET GESTION ---

    // 1. Récupérer les statistiques globales
    // Dans Model/Utilisateur.php

    public function getDashboardStats() {
        // Compter les clients
        $stmt = $this->conn->query("SELECT COUNT(*) as total_clients FROM Client");
        $clients = $stmt->fetch(PDO::FETCH_ASSOC)['total_clients'];

        // Compter les organisations
        $stmt = $this->conn->query("SELECT COUNT(*) as total_orgs FROM Organisation");
        $orgs = $stmt->fetch(PDO::FETCH_ASSOC)['total_orgs'];

        $queryPending = "SELECT COUNT(*) as pending_orgs 
                         FROM Organisation 
                         WHERE statut_verification != 'Verifié' 
                         OR statut_verification IS NULL";
                         
        $stmt = $this->conn->query($queryPending);
        $pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orgs'];

        return [
            'clients' => $clients,
            'organisations' => $orgs,
            'pending_validations' => $pending
        ];
    }

    // 2. Récupérer la liste de tous les clients
    public function getAllClients() {
        $query = "SELECT u.id_utilisateur, u.email, u.date_inscription, c.nom_complet 
                  FROM Utilisateur u
                  JOIN Client c ON u.id_utilisateur = c.id_utilisateur
                  ORDER BY u.date_inscription DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Récupérer la liste de toutes les organisations
    public function getAllOrganisations() {
        $query = "SELECT u.id_utilisateur, u.email, o.nom_organisation, o.adresse, o.statut_verification 
                  FROM Utilisateur u
                  JOIN Organisation o ON u.id_utilisateur = o.id_utilisateur
                  ORDER BY o.statut_verification ASC, u.date_inscription DESC"; 
                  // On trie pour voir les "non vérifiés" en premier
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Valider une organisation
    public function validateOrganisation($id_admin, $id_organisation) {
        $query = "UPDATE Organisation SET statut_verification = 'Verifié' WHERE id_utilisateur = :id_org";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_org', $id_organisation);
        return $stmt->execute();
    }

    // 5. Supprimer un utilisateur (Client ou Organisation)
    public function deleteUser($id) {
        // Grâce aux contraintes de clé étrangère (ON DELETE CASCADE) dans la BDD,
        // supprimer la ligne dans 'Utilisateur' devrait supprimer aussi la ligne dans 'Client'/'Organisation'.
        $query = "DELETE FROM Utilisateur WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    // Rechercher des utilisateurs (Clients ou Organisations)
    // Rechercher des utilisateurs (Par Nom, Bio ou Adresse)
    public function searchGlobal($keyword) {
        // Sécurisation et ajout des % pour le LIKE
        $keyword = "%" . htmlspecialchars(strip_tags($keyword)) . "%";

        $query = "
            SELECT u.id_utilisateur, c.nom_complet as nom, 'Client' as type_compte, c.bio as description
            FROM Utilisateur u
            JOIN Client c ON u.id_utilisateur = c.id_utilisateur
            WHERE c.nom_complet LIKE :k1 OR c.bio LIKE :k2
            
            UNION
            
            SELECT u.id_utilisateur, o.nom_organisation as nom, 'Organisation' as type_compte, o.adresse as description
            FROM Utilisateur u
            JOIN Organisation o ON u.id_utilisateur = o.id_utilisateur
            WHERE (o.nom_organisation LIKE :k3 OR o.adresse LIKE :k4) 
            AND o.statut_verification = 'Verifié'
        ";
        
        $stmt = $this->conn->prepare($query);
        
        // On lie le même mot-clé à tous les paramètres
        // (On est obligé de donner des noms différents :k1, :k2... pour être compatible avec tous les drivers PDO)
        $stmt->bindParam(':k1', $keyword);
        $stmt->bindParam(':k2', $keyword);
        $stmt->bindParam(':k3', $keyword);
        $stmt->bindParam(':k4', $keyword);
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Admin : Mettre à jour les infos de connexion (Email + Mot de passe optionnel)
    public function updateUserCredentials($id, $email, $newPassword = null) {
        // Si le mot de passe est fourni, on met à jour Email + MDP
        if (!empty($newPassword)) {
            $query = "UPDATE Utilisateur SET email = :email, mot_de_passe_hash = :mdp WHERE id_utilisateur = :id";
            $stmt = $this->conn->prepare($query);
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt->bindParam(':mdp', $hash);
        } else {
            // Sinon, on met à jour uniquement l'Email
            $query = "UPDATE Utilisateur SET email = :email WHERE id_utilisateur = :id";
            $stmt = $this->conn->prepare($query);
        }

        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
    public function setResetToken($email, $token) {
        // Le token sera valide 1 heure
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $query = "UPDATE Utilisateur SET reset_token = :token, reset_expires = :expires WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $email);

        return $stmt->execute();
    }

    // 2. Vérifier si le token est valide
    public function getUserByResetToken($token) {
        $query = "SELECT id_utilisateur, email FROM Utilisateur 
                  WHERE reset_token = :token AND reset_expires > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. Mettre à jour le mot de passe et supprimer le token
    public function updatePasswordAfterReset($id, $newPassword) {
        $query = "UPDATE Utilisateur 
                  SET mot_de_passe_hash = :mdp, reset_token = NULL, reset_expires = NULL 
                  WHERE id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt->bindParam(':mdp', $hash);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }
    // --- GESTION DES BANNISSEMENTS ---

    // Bannir un utilisateur
    public function banUser($id, $raison, $duree_jours) {
        // Calculer la date de fin (Date actuelle + nombre de jours)
        // Si duree_jours = 9999, on considère que c'est "à vie" (optionnel)
        $date_fin = date('Y-m-d H:i:s', strtotime("+$duree_jours days"));

        $query = "UPDATE Utilisateur 
                  SET est_banni = 1, 
                      raison_bannissement = :raison, 
                      date_fin_bannissement = :date_fin 
                  WHERE id_utilisateur = :id";

        $stmt = $this->conn->prepare($query);
        
        $raison = htmlspecialchars(strip_tags($raison));
        
        $stmt->bindParam(':raison', $raison);
        $stmt->bindParam(':date_fin', $date_fin);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // Débannir un utilisateur (remettre à zéro)
    public function unbanUser($id) {
        $query = "UPDATE Utilisateur 
                  SET est_banni = 0, raison_bannissement = NULL, date_fin_bannissement = NULL 
                  WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Récupérer les infos de bannissement
    public function getBanInfo($id) {
        $query = "SELECT est_banni, raison_bannissement, date_fin_bannissement 
                  FROM Utilisateur WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Récupérer la liste de TOUS les utilisateurs bannis
    public function getAllBannedUsers() {
        // Cette requête est astucieuse :
        // Elle va chercher dans Client ET Organisation.
        // COALESCE prend la première valeur non nulle (donc le nom, peu importe la table).
        $query = "
            SELECT u.id_utilisateur, u.email, u.raison_bannissement, u.date_fin_bannissement,
                   COALESCE(c.nom_complet, o.nom_organisation) as nom,
                   CASE 
                       WHEN c.id_utilisateur IS NOT NULL THEN 'Client' 
                       ELSE 'Organisation' 
                   END as role
            FROM Utilisateur u
            LEFT JOIN Client c ON u.id_utilisateur = c.id_utilisateur
            LEFT JOIN Organisation o ON u.id_utilisateur = o.id_utilisateur
            WHERE u.est_banni = 1
            ORDER BY u.date_fin_bannissement ASC
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Mettre à jour l'email de l'utilisateur
    public function updateEmail($id, $newEmail) {
        $query = "UPDATE Utilisateur SET email = :email WHERE id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);
        $newEmail = htmlspecialchars(strip_tags($newEmail));
        
        $stmt->bindParam(':email', $newEmail);
        $stmt->bindParam(':id', $id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Si l'email existe déjà, ça va échouer (contrainte UNIQUE)
            return false;
        }
    }


    
}
?>