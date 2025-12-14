<?php

/**
 * Contrôleur Post : gestion des posts (création, affichage, réactions).
 */
class PostController extends Controller
{
    private Post $postModel;
    private Reaction $reactionModel;
    private PostComment $commentModel;
    private Notifications $notificationModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->reactionModel = new Reaction();
        $this->commentModel = new PostComment();
        $this->notificationModel = new Notifications();
    }
    
    /**
     * List all approved posts
     */
    public function index()
    {
        // Show all approved posts for everyone (anonymous users).
        $user = $this->currentUser();
        $posts = $this->postModel->getAllWithUsers(false);

        $this->view('post/index', [
            'posts' => $posts,
            'user' => $user
        ]);
    }
    
    /**
     * Show a single post
     */
    public function show($id)
    {
        $post = $this->postModel->getByIdWithUser($id);
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }

        // In anonymous mode, allow viewing any existing post regardless of status.
        $user = $this->currentUser();

        $comments = $this->commentModel->getForPost($id);
        
        $this->view('post/view', [
            'post' => $post,
            'comments' => $comments,
            'user' => $user
        ]);
    }

    public function create()
    {
        // Any visitor can access the post creation form.
        $user = $this->currentUser();
        $this->view('post/create', ['user' => $user], 'back');
    }

    public function store()
    {
        // Anonymous creation: use the default current user for IDs.
        $user = $this->currentUser();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=post&action=create');
        }
        
        // Include content filter helper
        require_once __DIR__ . '/../helpers/content_filter.php';
        
        // Filter title and content for bad words
        $title = filter_content(trim($_POST['title'] ?? ''));
        $content = filter_content(trim($_POST['content'] ?? ''));

        if (!$content) {
            $this->view('post/create', [
                'user' => $user,
                'formError' => 'Le contenu est obligatoire.',
                'old' => [
                    'title' => $title,
                    'content' => $content,
                ],
            ], 'back');
            return;
        }

        $postId = $this->postModel->create([
            'id_auteur' => (int) ($user['id_utilisateur'] ?? 1),
            'titre' => $title ?: null,
            'contenu' => $content,
            'est_publie' => 0,
            'status' => 'pending', // New posts require admin approval
            'moderation_notes' => null,
        ]);

        // Notify user that their post is under review
        $this->notificationModel->create([
            'user_id' => (int) ($user['id_utilisateur'] ?? 1),
            'title'   => 'Post soumis',
            'message' => sprintf('Votre post "%s" a été soumis et est en attente de modération.', $title ?: 'sans titre'),
        ]);

        // Stay in the authenticated Stories workflow (back-office layout)
        $this->redirect('?controller=histoire&action=index');
    }

    public function edit()
    {
        $postId = (int) ($_GET['id'] ?? 0);

        $post = $this->postModel->findById($postId);
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }

        $this->view('post/edit', [
            'post' => $post,
            'user' => $this->currentUser(),
        ], 'back');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=post&action=index');
        }

        $postId = (int) ($_POST['id'] ?? 0);
        $post = $this->postModel->findById($postId);
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (!$content) {
            // Stay on edit form with minimal validation in anonymous mode.
            $this->redirect('?controller=post&action=edit&id=' . $postId);
        }

        $this->postModel->update($postId, [
            'titre' => $title ?: null,
            'contenu' => $content,
            'status' => 'pending', // Set back to pending for re-approval after edit
            'est_publie' => 0,     // Hide from public Stories until re-approved
            'moderation_notes' => null // Clear previous moderation notes
        ]);

        // Notify user that their edited post is under review again
        $user = $this->currentUser();
        $this->notificationModel->create([
            'user_id' => (int) ($user['id_utilisateur'] ?? 1),
            'title'   => 'Post modifié soumis',
            'message' => sprintf('Votre post "%s" modifié est en attente de modération.', $title ?: 'sans titre'),
        ]);

        // After editing, return to the Stories dashboard (back-office)
        $this->redirect('?controller=histoire&action=index');
    }

    public function react()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            exit('Invalid request');
        }

        $postId = (int) ($_POST['post_id'] ?? 0);
        $type = $_POST['type'] ?? 'like';

        if (!in_array($type, ['like', 'love', 'laugh', 'angry'])) {
            $type = 'like';
        }

        $user = $this->currentUser();
        $this->reactionModel->toggle($postId, (int) ($user['id_utilisateur'] ?? 1), $type);

        // Return JSON response for AJAX
        header('Content-Type: application/json');
        $reactions = $this->reactionModel->getByPost($postId);
        $userReaction = $this->reactionModel->getUserReaction($postId, (int) ($user['id_utilisateur'] ?? 1));
        
        echo json_encode([
            'success' => true,
            'reactions' => $reactions,
            'user_reaction' => $userReaction
        ]);
        exit;
    }

    public function delete()
    {
        $postId = (int) ($_GET['id'] ?? 0);
        
        $post = $this->postModel->findById($postId);
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }

        $this->postModel->delete($postId);
        
        // Redirect back to appropriate page based on context
        $user = $this->currentUser();
        if (!empty($user['is_admin'])) {
            $this->redirect('?controller=admin&action=index');
        } else {
            $this->redirect('?controller=histoire&action=index');
        }
    }
}

