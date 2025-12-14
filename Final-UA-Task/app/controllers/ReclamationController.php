<?php

class ReclamationController extends Controller
{
    private Reclamation $reclamationModel;
    private CauseSignalement $causeModel;
    private ReclamationCause $pivotModel;

    public function __construct()
    {
        $this->reclamationModel = new Reclamation();
        $this->causeModel = new CauseSignalement();
        $this->pivotModel = new ReclamationCause();
    }

    public function index()
    {
        $this->requireAdmin();
        $this->view('reclamation/index', [
            'reclamations' => $this->reclamationModel->getWithRelations(),
        ], 'back');
    }

    public function create()
    {
        $this->requireUser();
        $this->view('reclamation/create', [
            'causes' => $this->causeModel->findAll('libelle ASC'),
            'target' => [
                'histoire' => $_GET['id_histoire'] ?? null,
                'commentaire' => $_GET['id_commentaire'] ?? null,
            ],
        ]);
    }

    public function store()
    {
        $user = $this->requireUser();
        $data = [
            'description_personnalisee' => trim($_POST['description_personnalisee'] ?? ''),
            'statut' => 'nouvelle',
            'id_auteur' => $user['id_utilisateur'],
            'id_histoire_cible' => $_POST['id_histoire_cible'] ?: null,
            'id_commentaire_cible' => $_POST['id_commentaire_cible'] ?: null,
        ];

        if (!$data['description_personnalisee']) {
            $_SESSION['flash'] = 'Merci de décrire votre signalement.';
            $this->redirect('?controller=reclamation&action=create');
        }

        $recId = $this->reclamationModel->create($data);
        $causeIds = $_POST['causes'] ?? [];
        $this->pivotModel->syncCauses($recId, $causeIds);

        $this->redirect('?controller=histoire&action=index');
    }

    public function show()
    {
        $this->index();
    }

    public function edit()
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $reclamation = $this->reclamationModel->findById($id);
        $this->view('reclamation/edit', [
            'reclamation' => $reclamation,
            'causes' => $this->causeModel->findAll(),
        ], 'back');
    }

    public function update()
    {
        $this->requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? 'en_cours';
        $this->reclamationModel->update($id, ['statut' => $statut]);
        $this->pivotModel->syncCauses($id, $_POST['causes'] ?? []);
        $this->redirect('?controller=reclamation&action=index');
    }

    public function delete()
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $this->reclamationModel->delete($id);
        $this->redirect('?controller=reclamation&action=index');
    }
}

