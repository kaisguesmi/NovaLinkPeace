<?php

/**
 * Contrôleur des histoires: CRUD + réactions.
 */
class HistoireController extends Controller
{
    private Histoire $histoireModel;
    private Commentaire $commentaireModel;
    private Post $postModel;
    private Reaction $reactionModel;
    private PostComment $commentModel;
    private Notifications $notificationModel;

    public function __construct()
    {
        $this->histoireModel = new Histoire();
        $this->commentaireModel = new Commentaire();
        $this->postModel = new Post();
        $this->reactionModel = new Reaction();
        $this->commentModel = new PostComment();
        $this->notificationModel = new Notifications();
    }

    public function index()
    {
        // Anonymous "user" context; no real authentication.
        $user = $this->currentUser();
        
        // Show all approved posts (not just user's posts)
        $posts = $this->postModel->getAllWithUsers(false);
        
        // Get user reactions for each post
        foreach ($posts as &$post) {
            $post['user_reaction'] = $this->reactionModel->getUserReaction(
                (int) $post['id_post'],
                (int) $user['id_utilisateur']
            );
            $post['reactions'] = $this->reactionModel->getByPost((int) $post['id_post']);
            $post['comments'] = $this->commentModel->getByPost((int) $post['id_post']);
        }

        $toastNotification = null;
        $unread = $this->notificationModel->getUnreadByUser((int) $user['id_utilisateur']);
        
        foreach ($unread as $notification) {
            if (in_array($notification['title'], ['Post approuvé', 'Post rejeté', 'Post soumis', 'Post modifié soumis'], true)) {
                $toastNotification = [
                    'type' => $notification['title'] === 'Post rejeté' ? 'error' : 'success',
                    'message' => $notification['message'],
                ];
                // Mark this specific notification as read
                $this->notificationModel->markAsRead((int) $notification['id']);
                break;
            }
        }

        // Mark all notifications as read if we have a toast notification
        if ($toastNotification) {
            $this->notificationModel->markAllRead((int) $user['id_utilisateur']);
        }

        $this->view('histoire/index', [
            'posts' => $posts,
            'user' => $user,
            'toastNotification' => $toastNotification,
        ], 'front');
    }

    /**
     * Show all stories (public)
     */
    public function all()
    {
        $stories = $this->postModel->getAllPublicStories();

        $this->view('histoire/all', [
            'stories' => $stories
        ], 'front');
    }
    
    public function show()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $story = $this->histoireModel->getWithComments($id);
        
        if (!$story) {
            http_response_code(404);
            exit('Histoire introuvable');
        }
        
        // In anonymous mode, allow viewing any existing story.
        $reclamationModel = new Reclamation();
        $reclamationRecap = $reclamationModel->getRecapForStory($id);

        $this->view('histoire/show', [
            'story' => $story,
            'user'  => $this->currentUser(),
            'reclamationRecap' => $reclamationRecap,
        ], 'front');
    }

    public function create()
    {
        $user = $this->currentUser();
        $this->view('histoire/create', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ], 'front');
    }

    public function store()
    {
        $user = $this->currentUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=histoire&action=index');
        }

        $titre = trim($_POST['titre'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');

        if (!$titre || !$contenu) {
            // Minimal validation in anonymous mode: just go back to form.
            $this->redirect('?controller=histoire&action=create');
        }

        $success = $this->histoireModel->create([
            'titre' => $titre,
            'contenu' => $contenu,
            'statut' => 'pending',
            'id_client' => (int) ($user['id_utilisateur'] ?? 1)
        ]);

        if ($success) {
            $this->redirect('?controller=histoire&action=index');
        } else {
            $this->redirect('?controller=histoire&action=create');
        }
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $story = $this->histoireModel->find($id);
        
        $this->view('histoire/edit', [
            'story' => $story,
            'user' => $this->currentUser(),
            'csrf_token' => $this->generateCsrfToken()
        ], 'front');
    }

    public function update()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $story = $this->histoireModel->findById($id);

        $data = [
            'titre' => trim($_POST['titre'] ?? $story['titre']),
            'contenu' => trim($_POST['contenu'] ?? $story['contenu']),
        ];

        $this->histoireModel->update($id, $data);
        $this->redirect('?controller=histoire&action=show&id=' . $id);
    }

    public function delete()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $story = $this->histoireModel->findById($id);
        if ($story) {
            $this->histoireModel->delete($id);
        }
        $this->redirect('?controller=histoire&action=index');
    }

    public function pollNotifications()
    {
        $user = $this->currentUser();

        header('Content-Type: application/json');

        $toastNotification = null;
        $unread = $this->notificationModel->getUnreadByUser((int) $user['id_utilisateur']);
        foreach ($unread as $notification) {
            if (in_array($notification['title'], ['Post approuvé', 'Post rejeté', 'Post soumis', 'Post modifié soumis'], true)) {
                $toastNotification = [
                    'type' => $notification['title'] === 'Post rejeté' ? 'error' : 'success',
                    'message' => $notification['message'],
                ];
                break;
            }
        }

        if ($toastNotification) {
            $this->notificationModel->markAllRead((int) $user['id_utilisateur']);
        }

        echo json_encode([
            'success' => true,
            'toast' => $toastNotification,
        ]);
        exit;
    }

    public function react()
    {
        $user = $this->currentUser();
        $storyId = (int) ($_POST['id_histoire'] ?? 0);
        $emoji = $_POST['emoji'] ?? '❤️';
        $this->histoireModel->react($storyId, $user['id_utilisateur'], $emoji);
        $this->redirect('?controller=histoire&action=show&id=' . $storyId);
    }

    public function moderate()
    {
        $this->requireAdmin();
        $storyId = (int) ($_POST['id_histoire'] ?? 0);
        $status = $_POST['statut'] ?? 'published';
        $this->histoireModel->moderate($storyId, $status);
        $this->redirect('?controller=admin&action=index');
    }
}

