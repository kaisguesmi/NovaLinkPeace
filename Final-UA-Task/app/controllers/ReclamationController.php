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
            $status = $_GET['statut'] ?? null;
            $search = $_GET['q'] ?? null;
            $sort = $_GET['sort'] ?? 'recent';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 15;
            [$rows, $total] = $this->reclamationModel->getWithFilters($status, $page, $perPage, $search, $sort);

        $this->view('reclamation/index', [
            'reclamations' => $rows,
            'statut' => $status,
                'search' => $search,
                'sort' => $sort,
            'page' => $page,
            'pages' => (int)ceil($total / $perPage),
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

    public function my()
    {
        $user = $this->requireUser();
        $list = $this->reclamationModel->getByUser((int)$user['id_utilisateur']);
        $this->view('reclamation/my', ['reclamations' => $list], 'front');
    }

    public function received()
    {
        $user = $this->requireUser();
        $list = $this->reclamationModel->getByStoryAuthor((int)$user['id_utilisateur']);
        $this->view('reclamation/received', ['reclamations' => $list], 'front');
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
        if (!$data['id_histoire_cible'] && !$data['id_commentaire_cible']) {
            $_SESSION['flash'] = 'Cible manquante (histoire ou commentaire).';
            $this->redirect('?controller=reclamation&action=create');
        }

        $causeIds = $_POST['causes'] ?? [];

            // Anti-doublon: empêcher multiples signalements identiques actifs
            if ($this->reclamationModel->existsForUserTarget($user['id_utilisateur'], (int)$data['id_histoire_cible'], (int)$data['id_commentaire_cible'])) {
                $_SESSION['flash'] = 'Vous avez déjà signalé cette cible (en cours de traitement).';
                $this->redirect('?controller=histoire&action=index');
            }

        // Anti-bot: honeypot + rate limit 10s
        $honeypot = trim($_POST['website'] ?? '');
        if ($honeypot !== '') {
            $_SESSION['flash'] = 'Requête bloquée (bot).';
            $this->redirect('?controller=histoire&action=index');
        }
        if (isset($_SESSION['last_rec_ts']) && (time() - (int)$_SESSION['last_rec_ts']) < 10) {
            $_SESSION['flash'] = 'Trop rapide. Merci de patienter.';
            $this->redirect('?controller=histoire&action=index');
        }
        $_SESSION['last_rec_ts'] = time();

        // AI scoring: provider switch (off | heuristic)
        $cfg = require ROOT_PATH . '/config/config.php';
        $provider = $cfg['ai']['provider'] ?? 'heuristic';
        [$aiScore, $aiAnalysis, $aiModel] = $this->reclamationModel->scoreWithProvider(
            $data['description_personnalisee'],
            (array)$causeIds,
            $provider,
            $cfg['ai']['gemini_api_key'] ?? null,
            $cfg['ai']['gemini_model'] ?? 'gemini-1.5-flash'
        );
        if ($aiModel !== 'off') {
            $data['ai_score'] = $aiScore;
            $data['ai_analysis'] = $aiAnalysis;
            $data['ai_model'] = $aiModel;
        }

        $recId = $this->reclamationModel->create($data);
        $this->pivotModel->syncCauses($recId, $causeIds);

        $this->redirect('?controller=histoire&action=index');
    }

    public function stats()
    {
        $this->requireAdmin();
        $stats = $this->reclamationModel->getStats();
        header('Content-Type: application/json');
        echo json_encode($stats);
    }

    public function export()
    {
        $this->requireAdmin();
        $rows = $this->reclamationModel->getAllForExport();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reclamations.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id', 'auteur_email', 'statut', 'histoire_titre', 'commentaire_contenu', 'ai_score', 'ai_analysis', 'ai_model', 'created_at']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id_reclamation'],
                $r['auteur_email'],
                $r['statut'],
                $r['histoire_titre'],
                $r['commentaire_contenu'],
                $r['ai_score'],
                $r['ai_analysis'],
                $r['ai_model'],
                $r['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }

    public function exportExcel()
    {
        $this->requireAdmin();
        $rows = $this->reclamationModel->getAllForExport();
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="reclamations.xls"');
        echo "id\tauteur_email\tstatut\thistoire_titre\tcommentaire_contenu\tai_score\tai_analysis\tai_model\tcreated_at\n";
        foreach ($rows as $r) {
            $line = [
                $r['id_reclamation'],
                $r['auteur_email'],
                $r['statut'],
                $r['histoire_titre'],
                $r['commentaire_contenu'],
                $r['ai_score'],
                $r['ai_analysis'],
                $r['ai_model'],
                $r['created_at'],
            ];
            echo implode("\t", array_map(function($v){ return str_replace(["\t","\n","\r"], ' ', (string)$v); }, $line)) . "\n";
        }
        exit;
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
        $this->logDecision($id, $statut, $_SESSION['user_id'] ?? null);
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

    private function logDecision(int $recId, string $status, $adminId): void
    {
        $line = sprintf("%s	id=%d	status=%s	admin=%s\n", date('c'), $recId, $status, $adminId ?: 'n/a');
        $logFile = __DIR__ . '/../logs/reclamations.log';
        @file_put_contents($logFile, $line, FILE_APPEND);
    }
}

