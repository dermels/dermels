<?php

namespace Controller;


use Mailjet\Client;
use Mailjet\Resources;
use Model\PostModel;
use PDO;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController
{
    private Environment $twig;
    private PostModel $postModel;

    public function __construct(Environment $twig, PDO $db) {
        $this->twig = $twig;
        $this->postModel = new PostModel($db);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(): string
    {
        // Utiliser le modèle pour récupérer tous les articles de blog
        $posts = $this->postModel->getAll();

        // Utiliser Twig pour afficher la page d'accueil
        return $this->twig->render('home/home.twig', [
            'page_title' => 'Mon Blog',
            'posts' => $posts,
        ]);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function sendMail(): string
    {
        $nom = $_POST['nom'];
        $email = $_POST['email'];
        $telephone = $_POST['telephone'];
        $message = $_POST['message'];

        $mj = new Client(MAILJET_API_KEY, MAILJET_API_SECRET,true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "gomezfelix@hotmail.fr",
                        'Name' => "Blog Openclassroom"
                    ],
                    'To' => [
                        [
                            'Email' => "gomezfelix.fac@hotmail.com",
                            'Name' => "Felix Gomez"
                        ]
                    ],
                    'Subject' => "BLOG: $nom vous a envoyé un message",
                    'TextPart' => "Nouveau message du site web",
                    'HTMLPart' => "<b>Nom et prénom :</b> {$nom} <br>".
                        "<b>Email :</b> {$email} <br>".
                        "<b>Téléphone :</b> {$telephone} <br>".
                        "<b>Message :</b><br/> {$message}",
                ]
            ]
        ];

        $response = $mj->post(Resources::$Email, ['body' => $body]);

        if($response->success()){
            return json_encode(['code' => 200]);
        } else {
            return json_encode(['code' => 500]);
        }


    }
}