<?php

namespace App\Twig;

use Kint;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PathExtension extends AbstractExtension
{
    private $basepath;

    public function __construct($basepath, )
    {
        $this->basepath = $basepath;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', [$this, 'path']),
            new TwigFunction('asset', [$this, 'asset']),
            new TwigFunction('isConnected', [$this, 'isConnected']),
            new TwigFunction('isAdmin', [$this, 'isAdmin']),
            new TwigFunction('dump', [$this, 'dump']),
        ];
    }

    public function asset($path): string
    {
        return $this->basepath . '/' . $path;
    }

    public function path($route): string
    {
        // Ajoute "index.php" au chemin généré par la fonction path uniquement en mode "dev"
        return $this->basepath . ($this->isDevMode() ? '/index.php/' : '/') . $route;

    }
    private function isDevMode(): bool
    {
        return MODE === 'dev';
    }

    public function isConnected(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user']) && $_SESSION['user']['roleLevel'] > 0;

    }

    public function isAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user']) && $_SESSION['user']['roleLevel'] > 1;
    }

    public function dump($var): void
    {
        Kint::dump($var); // Output est automatiquement formaté et compressible
    }
}