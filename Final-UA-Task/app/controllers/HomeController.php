<?php

/**
 * Contrôleur Home : page d'accueil publique (avant connexion).
 */
class HomeController extends Controller
{
    public function index()
    {
        $postModel = new Post();
        $stories = $postModel->getLatestPublicStories(6);
        
        $this->view('home/index', [
            'stories' => $stories
        ], 'front');
    }
}

