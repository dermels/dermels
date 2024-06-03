<?php

namespace Controller;

use Model\Article;
use Model\ArticleRepository;
use Model\Commentary;
use Model\CommentaryRepository;
use Model\UserRepository;
use ReflectionException;
use Twig\Environment;
use PDO;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CommentaryController
{
    private Environment $twig;
    private ArticleRepository $articleRepository;
    private CommentaryRepository $commentaryRepository;
    private UserRepository $userRepository;

    public function __construct(Environment $twig, PDO $db)
    {
        $this->twig = $twig;
        $this->articleRepository = new ArticleRepository($db);
        $this->userRepository = new UserRepository($db);
        $this->commentaryRepository = new CommentaryRepository($db);
    }

    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function validateComment(): string
    {
        // inverse le statut de validation du commentaire
        $commentId = filter_input(INPUT_GET, 'commentId', FILTER_SANITIZE_NUMBER_INT) ?: null;        if ($commentId) {
            $comment = $this->commentaryRepository->getCommentary((int) $commentId);
        } else {
            return $this->twig->render('article/articleList.twig', [
                "message" => "Le commentaire demandé n'existe pas."
            ]);
        }
        $comment->setIsValid(!$comment->getIsValid());
        $this->commentaryRepository->save($comment);

        return json_encode(['code' => 200]);
    }

    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function submitCommentForm(): string
    {
        $articleId = filter_input(INPUT_GET, 'articleId', FILTER_SANITIZE_NUMBER_INT);
        $article = $this->articleRepository->getArticle($articleId);
        if (!$article) {
            return $this->twig->render('article/articleList.twig', [
                "message" => "L'article demandé n'existe pas."
            ]);
        }
        $comment = new Commentary();
        $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
        if (empty($content)) {
            return $this->twig->render('article/articleShow.twig', [
                "article" => $article,
                "message" => "Le commentaire ne peut pas être vide."
            ]);
        }
        //enleve uniquement les balises <script> et <style> et html du contenu
        $comment->setContent(strip_tags($content));
        $comment->setAuthorId($_SESSION['user']['id']);
        $comment->setArticleId($articleId);
        $this->commentaryRepository->save($comment);
        header('Location: ' . (MODE === 'dev' ? '/index.php/' : '/') . 'article/show?id=' . $articleId);
        return  '';
    }


    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function validateCommentAdmin(): string
    {
        $current_page = filter_input(INPUT_GET, 'current_page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
        $limit = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT) ?: 10;

        $commentaries = $this->commentaryRepository->getCommentaries(null, $current_page, $limit);

        return $this->twig->render('commentaries/commentaryList.twig', [
            'commentaries' => $commentaries['data'],
            'total' => $commentaries['total'],
            'current_page' => $current_page,
            'limit' => $limit
        ]);
    }

}