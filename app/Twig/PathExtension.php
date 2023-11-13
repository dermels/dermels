<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PathExtension extends AbstractExtension
{
    private $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('path', [$this, 'generatePath']),
        ];
    }

    public function generatePath($route)
    {
        // Ajoutez ici votre logique pour générer le chemin vers la route
        // Dans cet exemple, nous ajoutons simplement la route à la base path
        return $this->basePath . '?page=' . $route;
    }
}