<?php

namespace Controller;

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

    public function __construct(PDO $db , Environment $twig)
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
    public function register(array $user)
    {
        // Utilisez trim pour supprimer les espaces avant et après les chaînes.
        $firstname = htmlspecialchars(trim($user['firstname']));
        $lastname = htmlspecialchars(trim($user['lastname']));
        $password = htmlspecialchars(trim($user['password']));
        $email = htmlspecialchars(trim($user['email']));

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
        $user = $this->userRepository->getUser($email);
        if ($user) {
            return $this->twig->render('register/register.twig', [
                "message" => "Email déjà utilisé"
            ]);
        }

        // Hash mot de passe après avoir confirmé que l'utilisateur n'existe pas.
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Créez l'utilisateur.
        $user = $this->userRepository->createUser( $firstname, $lastname, $password, $email );

        // Ne stockez que les informations utilisateur nécessaires dans la session.
        $_SESSION['user'] = [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()
        ];

        redirect('/home');


    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws ReflectionException
     */
    public function authenticate(array $userData)
    {
        $email = htmlspecialchars(trim($userData['email']));
        $password = htmlspecialchars(trim($userData['password']));

        $user = $this->userRepository->getUser($email);
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
            'role' => $user->getRole()
        ];

        redirect('/home');
    }

    public function isAuthenticated(): bool
    {
        // Retourne true si l'utilisateur est authentifié, sinon false
        return isset($_SESSION['user']);
    }
}