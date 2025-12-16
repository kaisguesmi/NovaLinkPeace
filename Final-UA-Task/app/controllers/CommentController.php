<?php

/**
 * Contrôleur Comment : gestion des commentaires sur les posts.
 */
class CommentController extends Controller
{
    private PostComment $commentModel;
    private Post $postModel;
    private Notifications $notificationModel;

    public function __construct()
    {
        $this->commentModel = new PostComment();
        $this->postModel = new Post();
        $this->notificationModel = new Notifications();
    }

    public function create()
    {
        $postId = (int) ($_GET['post_id'] ?? 0);
        $post = $postId ? $this->postModel->findById($postId) : null;
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }

        $this->view('comment/create', [
            'post' => $post,
        ], 'front');
    }

    public function store()
    {
        // Anonymous user context; no real authentication.
        $user = $this->currentUser();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            exit('Invalid request');
        }

        $postId = (int) ($_POST['post_id'] ?? 0);
        
        // Include content filter helper
        require_once __DIR__ . '/../helpers/content_filter.php';
        
        // Filter content for bad words
        $content = filter_content(trim($_POST['content'] ?? ''));

        if (!$content || !$postId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Contenu requis']);
            exit;
        }

        // Verify post exists
        $post = $this->postModel->findById($postId);
        if (!$post) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Post introuvable']);
            exit;
        }

        $commentId = $this->commentModel->create([
            'post_id' => $postId,
            'user_id' => (int) ($user['id_utilisateur'] ?? 1),
            'content' => $content,
            // Comments do not go through moderation: mark as approved immediately
            'status' => 'approved',
        ]);

        // Get the created comment with user info
        $comment = $this->commentModel->findById($commentId);
        $clientModel = new Client();
        $client = $clientModel->getProfile((int) ($user['id_utilisateur'] ?? 1));
        
        $comment['nom_complet'] = $client['nom_complet'] ?? $user['email'];
        $comment['avatar'] = $client['avatar'] ?? null;
        $comment['email'] = $user['email'];

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'comment' => $comment
        ]);
        exit;
    }

    public function edit()
    {
        $commentId = (int) ($_GET['id'] ?? 0);

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            exit('Commentaire introuvable');
        }

        $post = $this->postModel->findById((int) $comment['post_id']);

        $this->view('comment/edit', [
            'comment' => $comment,
            'post' => $post,
        ], 'front');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Non-POST access: send users back to Stories
            $this->redirect('?controller=histoire&action=index');
        }

        $commentId = (int) ($_POST['id'] ?? 0);
        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            exit('Commentaire introuvable');
        }

        $content = trim($_POST['content'] ?? '');
        if (!$content) {
            $this->redirect('?controller=comment&action=edit&id=' . $commentId);
        }

        $this->commentModel->update($commentId, [
            'content' => $content,
        ]);

        // After updating, always return to Stories
        $this->redirect('?controller=histoire&action=index');
    }

    public function delete()
    {
        $commentId = (int) ($_GET['id'] ?? 0);
        
        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            exit('Commentaire introuvable');
        }

        $this->commentModel->delete($commentId);
        // After deletion, return to Stories
        $this->redirect('?controller=histoire&action=index');
    }
}

