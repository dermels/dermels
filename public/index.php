<?php

use Controller\HomeController;
use Model\PostModel;

require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../app/Model/PostModel.php';
require_once '../app/Controller/HomeController.php';
require_once '../app/Twig/PathExtension.php';


// Initialiser PDO pour la base de données
try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
    echo 'Connexion réussie';
} catch (PDOException $e) {
    echo 'Erreur de connexion : ' . $e->getMessage();
}


// Initialiser Twig
$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);
$twig->addExtension(new App\Twig\PathExtension('index.php'));

// Initialiser le modèle
$postModel = new PostModel($db);



// Vérifier la requête de l'utilisateur et appeler le contrôleur approprié
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = 'home';
}

switch ($action) {
    case 'home':
        $controller = new HomeController($twig, $postModel);
        $controller->index();
        break;
    // Ajoutez d'autres cas pour d'autres pages/contrôleurs
    default:
        // Gérer les cas d'action non reconnus (peut-être afficher une page d'erreur)
        echo 'Action non reconnue';
        break;
}