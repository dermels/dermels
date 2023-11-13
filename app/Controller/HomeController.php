<?php

namespace Controller;


use Model\PostModel;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController
{
    private $twig;
    private $postModel;

    public function __construct(\Twig\Environment $twig, PostModel $postModel) {
        $this->twig = $twig;
        $this->postModel = $postModel;
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
        echo $this->twig->render('home/index.twig', [
            'page_title' => 'Mon Blog',
            'posts' => $posts,
        ]);
    }
}