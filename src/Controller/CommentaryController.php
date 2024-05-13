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
        $commentId = $_GET['commentId'] ?? null;
        if ($commentId) {
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
        $articleId = $_GET['articleId'];
        $article = $this->articleRepository->getArticle($articleId);
        if (!$article) {
            return $this->twig->render('article/articleList.twig', [
                "message" => "L'article demandé n'existe pas."
            ]);
        }
        $comment = new Commentary();
        $content = $_POST['content'];
        if (empty($content)) {
            return $this->twig->render('article/articleShow.twig', [
                "article" => $article,
                "message" => "Le commentaire ne peut pas être vide."
            ]);
        }
        //enleve uniquement les balises <script> et <style> et html du contenu
        $comment->setContent(strip_tags($_POST['content']));
        $comment->setAuthorId($_SESSION['user']['id']);
        $comment->setArticleId($articleId);
        $this->commentaryRepository->save($comment);
        header('Location: ' . (MODE === 'dev' ? '/index.php/' : '/') . 'article/show?id=' . $articleId);
        exit();
    }


    /**
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function validateCommentAdmin(): string
    {
        $current_page = $_GET['current_page'] ?? 1;
        $limit = $_GET['limit'] ?? 10;

        $commentaries = $this->commentaryRepository->getCommentaries(null, $current_page, $limit);

        return $this->twig->render('commentaries/commentaryList.twig', [
            'commentaries' => $commentaries['data'],
            'total' => $commentaries['total'],
            'current_page' => $current_page,
            'limit' => $limit
        ]);
    }

}