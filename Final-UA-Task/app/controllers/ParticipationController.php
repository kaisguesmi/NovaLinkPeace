<?php

class ParticipationController extends Controller
{
    private Participation $participationModel;

    public function __construct()
    {
        $this->participationModel = new Participation();
    }

    public function index()
    {
        $user = $this->requireUser();
        $sql = "SELECT i.*, p.date_inscription 
                FROM Participation p
                JOIN Initiative i ON i.id_initiative = p.id_initiative
                WHERE p.id_client = :client";
        $stmt = Database::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(['client' => $user['id_utilisateur']]);
        $records = $stmt->fetchAll();

        $this->view('participation/index', ['participations' => $records]);
    }

    public function create()
    {
        $this->redirect('?controller=initiative&action=index');
    }

    public function store()
    {
        $user = $this->requireUser();
        $initiativeId = (int) ($_POST['id_initiative'] ?? 0);
        $this->participationModel->join($user['id_utilisateur'], $initiativeId);
        $this->redirect('?controller=initiative&action=show&id=' . $initiativeId);
    }

    public function delete()
    {
        $user = $this->requireUser();
        $initiativeId = (int) ($_GET['id_initiative'] ?? 0);
        $this->participationModel->leave($user['id_utilisateur'], $initiativeId);
        $this->redirect('?controller=participation&action=index');
    }

    public function show()
    {
        $this->index();
    }

    public function edit()
    {
        $this->index();
    }

    public function update()
    {
        $this->index();
    }
}

