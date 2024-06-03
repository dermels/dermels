<?php

use Controller\ArticleController;
use Controller\AuthController;
use Controller\CommentaryController;
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
        '/' => ['methode' => 'handleHome', 'roleLevel' => 0],
        '/home' => ['methode' => 'handleHome', 'roleLevel' => 1],
        '/send/mail' => ['methode' => 'sendMail', 'roleLevel' => 0],
        '/article/edit' => ['methode' => 'handleArticleForm', 'roleLevel' => 2],
        '/article/list' => ['methode' => 'handleArticleList', 'roleLevel' => 0],
        '/article/show' => ['methode' => 'handleArticleShow', 'roleLevel' => 0],
        '/article/delete' => ['methode' => 'handleArticleDelete', 'roleLevel' => 2],
        '/comment/create' => ['methode' => 'handleCommentForm', 'roleLevel' => 1],
        '/admin/comment/validation' => ['methode' => 'handleCommentValidationAdmin', 'roleLevel' => 2],
        '/comment/validation' => ['methode' => 'handleCommentValidation', 'roleLevel' => 2],
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
            print $this->handleNotFound();
            return;
        }

        $routeDetails = $this->routes[$path];
        $methodName = $routeDetails['methode'];
        $requiredRoleLevel = $routeDetails['roleLevel'];

        //verifies que la methode existe
        if (!method_exists($this, $methodName)) {
            print $this->handleNotFound();
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
                if($currentUserLevel == 0)
                    redirect('/login');
                print $this->handleAccessDenied();
                return;
            }
        }



        // sinon, appeler la méthode appropriée
        print $this->$methodName($_SERVER['REQUEST_METHOD'] ?? 'handleNotFound');

    }

    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function handleCommentForm($methode): string
    {
        $controller = new CommentaryController($this->twig, $this->db);
        return match ($methode) {
            'POST' => $controller->submitCommentForm(),
            default => $this->handleNotFound(),
        };
    }

    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function handleCommentValidation($methode): string
    {
        $controller = new CommentaryController($this->twig, $this->db);
        return match ($methode) {
            'GET' => $controller->validateComment(),
            default => $this->handleNotFound(),
        };
    }

    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function handleCommentValidationAdmin($methode): string
    {
        $controller = new CommentaryController($this->twig, $this->db);
        return match ($methode) {
            'GET' => $controller->validateCommentAdmin(),
            default => $this->handleNotFound(),
        };
    }

    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function handleArticleForm($methode): string
    {
        $controller = new ArticleController($this->twig, $this->db);

        return match ($methode) {
            'GET' => $controller->showArticleForm(),
            'POST' => $controller->submitArticleForm(),
            default => $this->handleNotFound(),
        };
    }

    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function handleArticleDelete($methode): string
    {
        $controller = new ArticleController($this->twig, $this->db);

        return match ($methode) {
            'GET' => $controller->deleteArticle(),
            default => $this->handleNotFound(),
        };
    }

    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function handleArticleList($methode): string
    {
        $controller = new ArticleController($this->twig, $this->db);

        return match ($methode) {
            'GET' => $controller->showArticleList(),
            default => $this->handleNotFound(),
        };
    }

    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function handleArticleShow($methode): string
    {
        $controller = new ArticleController($this->twig, $this->db);

        return match ($methode) {
            'GET' => $controller->showArticle(),
            default => $this->handleNotFound(),
        };
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    private function handleHome($methode): string
    {
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
     */
    private function sendMail($methode): string
    {
        $controller = new HomeController($this->twig, $this->db);

        return match ($methode) {
            'POST' => $controller->sendMail(),
            default => $this->handleNotFound(),
        };
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws ReflectionException
     */
    private function handleRegister($methode): string|null
    {
        // Instancier et appeler le contrôleur approprié
        return match ($methode) {
            'GET' => $this->twig->render('register/register.twig'),
            'POST' => $this->authController->register(),
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
            'POST' => $this->authController->authenticate(),
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
}