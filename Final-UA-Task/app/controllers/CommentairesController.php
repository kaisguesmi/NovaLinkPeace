<?php

/**
 * Gère les discussions associées aux histoires.
 */
class CommentairesController extends Controller
{
    private Commentaire $commentaireModel;
    private Notifications $notificationModel;

    public function __construct()
    {
        $this->commentaireModel = new Commentaire();
        $this->notificationModel = new Notifications();
    }

    public function index()
    {
        $storyId = (int) ($_GET['id_histoire'] ?? 0);
        $comments = $storyId ? $this->commentaireModel->getByStory($storyId) : $this->commentaireModel->getAllWithUsers();
        $user = $this->currentUser();
        $this->view('commentaires/index', [
            'comments' => $comments,
            'storyId' => $storyId,
        ], !empty($user['is_admin']) ? 'back' : 'front');
    }

    public function create()
    {
        $this->requireUser();
        $storyId = (int) ($_GET['id_histoire'] ?? 0);
        $this->view('commentaires/create', ['storyId' => $storyId]);
    }

    public function store()
    {
        $user = $this->requireUser();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=commentaires&action=create');
        }

        $contenu = trim($_POST['contenu'] ?? '');
        $storyId = (int) ($_POST['id_histoire'] ?? 0);
        if (!$contenu || !$storyId) {
            $_SESSION['flash'] = 'Commentaire invalide.';
            $this->redirect('?controller=commentaires&action=create&id_histoire=' . $storyId);
        }

        $this->commentaireModel->create([
            'contenu' => $contenu,
            'id_utilisateur' => $user['id_utilisateur'],
            'id_histoire' => $storyId,
        ]);

        $this->redirect('?controller=histoire&action=show&id=' . $storyId);
    }

    public function edit()
    {
        $user = $this->requireUser();
        $id = (int) ($_GET['id'] ?? 0);
        $comment = $this->commentaireModel->findById($id);
        if (!$comment || ($comment['id_utilisateur'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $this->view('commentaires/edit', ['comment' => $comment]);
    }

    public function update()
    {
        $user = $this->requireUser();
        $id = (int) ($_POST['id'] ?? 0);
        $comment = $this->commentaireModel->findById($id);
        if (!$comment || ($comment['id_utilisateur'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès refusé');
        }
        $contenu = trim($_POST['contenu'] ?? $comment['contenu']);
        $this->commentaireModel->update($id, ['contenu' => $contenu]);
        $this->redirect('?controller=histoire&action=show&id=' . $comment['id_histoire']);
    }

    public function delete()
    {
        $user = $this->requireUser();
        $id = (int) ($_GET['id'] ?? 0);
        $comment = $this->commentaireModel->findById($id);
        if ($comment && ($comment['id_utilisateur'] === $user['id_utilisateur'] || !empty($user['is_admin']))) {
            // If admin deletes someone else's comment, notify the owner
            if (!empty($user['is_admin']) && $comment['id_utilisateur'] !== $user['id_utilisateur']) {
                $histoireModel = new Histoire();
                $story = $histoireModel->findById((int) $comment['id_histoire']);
                $title = $story['titre'] ?? '';
                $message = $title
                    ? sprintf('Your comment on the story "%s" was deleted by an administrator.', $title)
                    : 'One of your comments was deleted by an administrator.';

                $this->notificationModel->create([
                    'user_id' => $comment['id_utilisateur'],
                    'message' => $message,
                    'created_at' => date('Y-m-d H:i:s'),
                    'read' => 0,
                ]);
            }

            $this->commentaireModel->delete($id);
        }
        $this->redirect('?controller=histoire&action=show&id=' . ($comment['id_histoire'] ?? 0));
    }

    public function show()
    {
        $this->edit(); // reuse form
    }
}

