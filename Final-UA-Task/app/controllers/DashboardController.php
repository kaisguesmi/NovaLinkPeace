<?php

/**
 * Contrôleur Dashboard : page principale après connexion.
 */
class DashboardController extends Controller
{
    private Post $postModel;
    private Reaction $reactionModel;
    private PostComment $commentModel;
    private Client $clientModel;
    private Notifications $notificationModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->reactionModel = new Reaction();
        $this->commentModel = new PostComment();
        $this->clientModel = new Client();
        $this->notificationModel = new Notifications();
    }

    public function index()
    {
        // Legacy dashboard is no longer used; redirect to admin dashboard.
        $this->redirect('?controller=admin&action=index');
    }

    public function notifications()
    {
        // Mark notifications as read then send user back to Stories page.
        $user = $this->requireUser();
        $this->notificationModel->markAllRead((int) $user['id_utilisateur']);
        $this->redirect('?controller=histoire&action=index#notifications');
    }
}

