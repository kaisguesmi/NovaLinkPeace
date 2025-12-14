<?php

class CandidatureController extends Controller
{
    private Offre $offreModel;
    private Candidature $candidatureModel;

    public function __construct()
    {
        $this->offreModel = new Offre();
        $this->candidatureModel = new Candidature();
    }

    public function index()
    {
        if ($this->isAdmin()) {
            $this->redirect('?controller=admin&action=index');
        }

        $user = $this->currentUser();
        $this->view('candidature/index', [
            'offres' => $this->offreModel->getWithAdmin(),
            'candidatures' => $user ? $this->candidatureModel->getByClient($user['id_utilisateur']) : [],
            'user' => $user,
        ]);
    }

    public function create()
    {
        $this->requireUser();
        $id = (int) ($_GET['id_offre'] ?? 0);
        $offre = $this->offreModel->findById($id);
        $this->view('candidature/create', ['offre' => $offre]);
    }

    public function store()
    {
        $user = $this->requireUser();
        $data = [
            'motivation' => trim($_POST['motivation'] ?? ''),
            'statut' => 'pending',
            'id_client' => $user['id_utilisateur'],
            'id_offre' => (int) ($_POST['id_offre'] ?? 0),
        ];
        if (!$data['motivation']) {
            $_SESSION['flash'] = 'Expliquez votre motivation.';
            $this->redirect('?controller=candidature&action=create&id_offre=' . $data['id_offre']);
        }
        $this->candidatureModel->create($data);
        $this->redirect('?controller=candidature&action=index');
    }

    public function show()
    {
        $this->index();
    }

    public function edit()
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $candidature = $this->candidatureModel->findById($id);
        $this->view('candidature/edit', ['candidature' => $candidature], 'back');
    }

    public function update()
    {
        $this->requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? 'pending';
        $this->candidatureModel->update($id, ['statut' => $statut]);
        $this->redirect('?controller=admin&action=index');
    }

    public function delete()
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $this->candidatureModel->delete($id);
        $this->redirect('?controller=admin&action=index');
    }
}

