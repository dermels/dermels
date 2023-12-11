<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PathExtension extends AbstractExtension
{
    private $basepath;

    public function __construct($basepath, )
    {
        $this->basepath = $basepath;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('path', [$this, 'path']),
        ];
    }

    public function path($route)
    {
        // Ajoute "index.php" au chemin généré par la fonction path uniquement en mode "dev"
        return $this->basepath . ($this->isDevMode() ? '/index.php/' : '/') . $route;

    }
    private function isDevMode(){
        return MODE === 'dev';
    }

}