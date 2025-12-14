<?php

/**
 * Contrôleur User : gestion de la liste des utilisateurs.
 */
class UserController extends Controller
{
    private Utilisateur $userModel;
    private Client $clientModel;
    private Admin $adminModel;

    public function __construct()
    {
        $this->userModel = new Utilisateur();
        $this->clientModel = new Client();
        $this->adminModel = new Admin();
    }

    public function index()
    {
        // Only allow admin users to access this page
        $user = $this->requireAdmin();
        
        // Check if avatar column exists
        $db = Database::getInstance()->getConnection();
        try {
            $checkColumn = $db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        // Get all users with their profiles
        $sql = "SELECT u.*, 
                c.nom_complet, 
                c.bio,
                {$avatarField},
                (SELECT COUNT(*) FROM Admin a WHERE a.id_utilisateur = u.id_utilisateur) > 0 as is_admin
                FROM Utilisateur u
                LEFT JOIN Client c ON c.id_utilisateur = u.id_utilisateur
                ORDER BY u.date_inscription DESC";
        
        $users = $db->query($sql)->fetchAll();

        $this->view('user/index', [
            'users' => $users,
            'user' => $user,
            'isAdmin' => true  // Ensure isAdmin is set for the view
        ], 'back');
    }
}

