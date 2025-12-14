<?php

class InitiativeController extends Controller
{
    private Initiative $initiativeModel;
    private Participation $participationModel;

    public function __construct()
    {
        $this->initiativeModel = new Initiative();
        $this->participationModel = new Participation();
    }

    public function index()
    {
        $user = $this->requireUser();
        $initiatives = $this->initiativeModel->getWithCreator();
        $this->view('initiative/index', [
            'initiatives' => $initiatives,
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ], 'front');
    }

    public function show()
    {
        $user = $this->requireUser();
        $id = (int) ($_GET['id'] ?? 0);
        $initiative = $this->initiativeModel->findById($id);
        
        if (!$initiative) {
            http_response_code(404);
            exit('Initiative introuvable');
        }
        
        $participants = $this->participationModel->getParticipants($id);
        $isParticipant = $this->participationModel->isParticipant($id, $user['id_utilisateur']);
        
        $this->view('initiative/show', [
            'initiative' => $initiative, 
            'participants' => $participants,
            'isParticipant' => $isParticipant,
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ], 'front');
    }

    public function create()
    {
        $user = $this->requireUser();
        $this->view('initiative/create', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ], 'front');
    }

    public function store()
    {
        $user = $this->requireUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=initiative&action=create');
        }
        
        // Validate CSRF token
        if (empty($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash'] = 'Session expirée. Veuillez réessayer.';
            $this->redirect('?controller=initiative&action=create');
        }
        
        $data = [
            'nom' => trim($_POST['nom'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'statut' => 'en_attente',
            'date_evenement' => $_POST['date_evenement'] ?? date('Y-m-d H:i:s'),
            'id_createur' => $user['id_utilisateur'],
        ];

        if (!$data['nom'] || !$data['description']) {
            $_SESSION['flash'] = 'Tous les champs sont requis.';
            $this->redirect('?controller=initiative&action=create');
        }

        $this->initiativeModel->create($data);
        $this->redirect('?controller=initiative&action=index');
    }

    public function edit()
    {
        $user = $this->requireUser();
        $id = (int) ($_GET['id'] ?? 0);
        $initiative = $this->initiativeModel->findById($id);
        
        if (!$initiative || ($initiative['id_createur'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès non autorisé');
        }
        
        $this->view('initiative/edit', [
            'initiative' => $initiative,
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ], 'front');
    }

    public function update()
    {
        $user = $this->requireUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=initiative&action=index');
        }
        
        // Validate CSRF token
        if (empty($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash'] = 'Session expirée. Veuillez réessayer.';
            $this->redirect('?controller=initiative&action=index');
        }
        
        $id = (int) ($_POST['id'] ?? 0);
        $initiative = $this->initiativeModel->findById($id);
        
        if (!$initiative || ($initiative['id_createur'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès non autorisé');
        }
        $data = [
            'nom' => trim($_POST['nom'] ?? $initiative['nom']),
            'description' => trim($_POST['description'] ?? $initiative['description']),
            'date_evenement' => $_POST['date_evenement'] ?? $initiative['date_evenement'],
        ];
        $this->initiativeModel->update($id, $data);
        $this->redirect('?controller=initiative&action=show&id=' . $id);
    }

    public function participate()
    {
        $user = $this->requireUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=initiative&action=index');
        }
        
        // Validate CSRF token
        if (empty($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            http_response_code(403);
            if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Session expirée. Veuillez rafraîchir la page.']);
                exit();
            }
            $_SESSION['flash'] = 'Session expirée. Veuillez réessayer.';
            $this->redirect('?controller=initiative&action=index');
        }
        
        $initiativeId = (int) ($_POST['initiative_id'] ?? 0);
        
        if ($this->participationModel->isParticipant($initiativeId, $user['id_utilisateur'])) {
            $this->participationModel->removeParticipation($initiativeId, $user['id_utilisateur']);
            $message = 'Participation annulée.';
            $isParticipant = false;
        } else {
            $this->participationModel->addParticipation($initiativeId, $user['id_utilisateur']);
            $message = 'Participation enregistrée !';
            $isParticipant = true;
        }
        
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $message,
                'isParticipant' => $isParticipant,
                'participantCount' => $this->participationModel->countParticipants($initiativeId)
            ]);
            exit();
        }
        
        $_SESSION['flash'] = $message;
        $this->redirect('?controller=initiative&action=show&id=' . $initiativeId);
    }

    public function delete()
    {
        $user = $this->requireUser();
        $id = (int) ($_GET['id'] ?? 0);
        $initiative = $this->initiativeModel->findById($id);
        if ($initiative && ($initiative['id_createur'] === $user['id_utilisateur'] || !empty($user['is_admin']))) {
            $this->initiativeModel->delete($id);
        }
        $this->redirect('?controller=initiative&action=index');
    }

    public function moderate()
    {
        $this->requireAdmin();
        $id = (int) ($_POST['id_initiative'] ?? 0);
        $status = $_POST['statut'] ?? 'approuvee';
        $this->initiativeModel->moderate($id, $status);
        $this->redirect('?controller=admin&action=index');
    }
}

