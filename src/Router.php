<?php

use Controller\AuthController;
use Controller\HomeController;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class Router
{
    private Environment $twig;
    private AuthController $authController;
    private PDO $db;


    /**
     * roleLevel 0 = non authentifié
     * roleLevel 1 = utilisateur authentifié
     * roleLevel 2 = administrateur
     */
    private array $routes = [
        '/' => ['methode' => 'handleHome', 'roleLevel' => 1],
        '/home' => ['methode' => 'handleHome', 'roleLevel' => 1],
        '/register' => ['methode' => 'handleRegister', 'roleLevel' => 0],
        '/login' => ['methode' => 'handleLogin', 'roleLevel' => 0],
        '/logout' => ['methode' => 'handleLogout', 'roleLevel' => 1]
    ];
    public function __construct(Environment $twig, PDO $db)
    {
        $this->twig = $twig;
        $this->db = $db;
        $this->authController = new AuthController($db, $twig);
    }

    public function route(): void
    {

        $path = $_SERVER['PATH_INFO'] ?? '/';

        // Vérifiez si la route existe dans le tableau
        if (!array_key_exists($path, $this->routes)) {
            echo $this->handleNotFound();
            return;
        }

        $routeDetails = $this->routes[$path];
        $methodName = $routeDetails['methode'];
        $requiredRoleLevel = $routeDetails['roleLevel'] ?? 0;

        //verifies que la methode existe
        if (!method_exists($this, $methodName)) {
            echo $this->handleNotFound();
            return;
        }

        // Si la route nécessite une authentification...
        if ($requiredRoleLevel > 0) {

            // Et l'utilisateur n'est pas authentifié...
            if (!$this->authController->isAuthenticated()) {
                redirect('/login');
            }

            // Ou si le niveau de rôle de l'utilisateur est inférieur au niveau requis...
            $currentUserLevel = $_SESSION['user']['roleLevel'];
            if ($currentUserLevel < $requiredRoleLevel) {
                echo $this->handleAccessDenied();
                return;
            }
        }



        // sinon, appeler la méthode appropriée
        echo $this->$methodName($_SERVER['REQUEST_METHOD']);

    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    private function handleHome($methode): string
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$this->authController->isAuthenticated()) {
            // Si l'utilisateur n'est pas authentifié, rediriger vers la page d'inscription
            redirect('/register');
        }
        $controller = new HomeController($this->twig, $this->db);

        return match ($methode) {
            'GET' => $controller->index(),
            default => $this->handleNotFound(),
        };
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws ReflectionException
     */
    private function handleRegister($methode): string
    {
        // Instancier et appeler le contrôleur approprié
        return match ($methode) {
            'GET' => $this->twig->render('register/register.twig'),
            'POST' => $this->authController->register($_POST),
            default => $this->handleNotFound(),
        };

    }


    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws ReflectionException
     */
    private function handleLogin($methode): string
    {
        // Instancier et appeler le contrôleur approprié
        return match ($methode) {
            'GET' => $this->twig->render('login/login.twig'),
            'POST' => $this->authController->authenticate($_POST),
            default => $this->handleNotFound(),
        };

    }

    #[NoReturn]
    private function handleLogout(): void
    {
        // Afficher le formulaire de connexion
        session_destroy();
        redirect('/login');
    }

    private function handleNotFound(): string
    {
        return 'Page non trouvée';
    }

    private function handleAccessDenied(): string
    {
        // affichez un message d'erreur adéquat ou redirigez l'utilisateur vers une autre page
        return "Accès non autorisé.";
    }

}

#[NoReturn]
function redirect($path): void
{
    header('Location:' . (MODE === 'dev' ? "/index.php" : "") . $path);
    exit();
}