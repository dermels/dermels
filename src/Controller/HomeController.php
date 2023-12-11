<?php

namespace Controller;


use Model\PostModel;
use PDO;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController
{
    private Environment $twig;
    private PostModel $postModel;

    public function __construct(Environment $twig, PDO $db) {
        $this->twig = $twig;
        $this->postModel = new PostModel($db);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index() {
        // Utiliser le modèle pour récupérer tous les articles de blog
        $posts = $this->postModel->getAll();

        // Utiliser Twig pour afficher la page d'accueil
        return $this->twig->render('home/index.twig', [
            'page_title' => 'Mon Blog',
            'posts' => $posts,
        ]);
    }
}