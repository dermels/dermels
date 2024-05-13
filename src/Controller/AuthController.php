<?php

namespace Controller;

use Model\User;
use Model\UserRepository;
use PDO;
use ReflectionException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AuthController
{
    private UserRepository $userRepository;
    private Environment $twig;

    public function __construct(PDO $db, Environment $twig)
    {
        $this->userRepository = new UserRepository($db);
        $this->twig = $twig;
    }


    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws ReflectionException
     */
    public function register()
    {
        // Utilisez trim pour supprimer les espaces avant et après les chaînes.
        $firstname = htmlspecialchars(trim($_POST['firstname']));
        $lastname = htmlspecialchars(trim($_POST['lastname']));
        $password = htmlspecialchars(trim($_POST['password']));
        $email = htmlspecialchars(trim($_POST['email']));

        // Si les paramètres sont vides après le nettoyage, retournez immédiatement.
        if (empty($firstname) || empty($lastname) || empty($password) || empty($email)) {
            return $this->twig->render('register/register.twig', [
                "message" => "Tous les champs doivent être renseignés."
            ]);
        }

        // Verifies si l'adresse e-mail est valide.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->twig->render('register/register.twig', [
                "message" => "L'adresse e-mail n'est pas valide."
            ]);
        }

        // Verifies si l'utilisateur existe déjà avant de hacher le mot de passe.
        $user = $this->userRepository->getUserByMail($email);
        if (!($user->getRoleLevel() != 1)) {
            return $this->twig->render('register/register.twig', [
                "message" => "Email déjà utilisé"
            ]);
        }

        // Hash mot de passe après avoir confirmé que l'utilisateur n'existe pas.
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Créez l'utilisateur.
        $user = $this->userRepository->createUser(new User( $firstname, $lastname, $password, $email ));

        if (!$user) {
            return $this->twig->render('register/register.twig', [
                "message" => "Un problème est survenue"
            ]);
        }

        // Ne stockez que les informations utilisateur nécessaires dans la session.
        $_SESSION['user'] = [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'roleLevel' => $user->getRoleLevel()
        ];


        redirect('/home');


    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws ReflectionException
     */
    public function authenticate()
    {

        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));

        $user = $this->userRepository->getUserByMail($email);

        if (!$user) {
            return $this->twig->render('login/login.twig', [
                'message' => "Email incorrect"
            ]);
        }

        if (!password_verify($password, $user->getPassword())) {
            return $this->twig->render('login/login.twig', [
                'message' => "Mot de passe incorrect"
            ]);
        }

        // L'utilisateur est authentifié, vous pouvez enregistrer des informations de session ici
        $_SESSION['user'] = [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'roleLevel' => $user->getRoleLevel()
        ];


        redirect('/home');
    }

    function isAuthenticated() {
        // La session est démarrée si elle n'a pas déjà été démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérification que la clé 'user' existe dans le tableau de session
        // et que le role est supérieur à 0
        if (isset($_SESSION['user']) && $_SESSION['user']['roleLevel'] > 0) {
            return true;
        }

        return false;
    }
}