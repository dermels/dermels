<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../src/Controller/AuthController.php';
require_once '../src/Twig/PathExtension.php';
require_once '../src/router.php';

// Initialiser PDO pour la base de données
try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
//    echo 'Connexion réussie' ;
} catch (PDOException $e) {
    print 'error 500 : ' . $e->getMessage();
}
// Démarrer la session
session_start();
// Initialiser Twig
$loader = new FilesystemLoader('../templates');
$twig = new Environment($loader);
$twig->addExtension(new App\Twig\PathExtension(''));

// Utiliser le routeur
$router = new Router($twig, $db);
$router->route();