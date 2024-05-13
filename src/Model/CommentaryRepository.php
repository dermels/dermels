<?php

namespace Model;

use PDO;
use ReflectionException;

class CommentaryRepository extends Hydrator
{

    protected PDO $db;
    public const ORDER_BY = "date_creation";
    public const ORDER = "desc";
    public const LIMIT = 10;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(Commentary $commentary): Commentary|false
    {
        if ($commentary->getId()) {
            return $this->update($commentary);
        }

        return $this->insert($commentary);
    }

    protected function insert(Commentary $commentary): Commentary|false
    {
        $query = 'INSERT INTO commentaires (content, author_id, article_id) VALUES (:content, :author_id, :article_id)';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':content', $commentary->getContent());
        $stmt->bindValue(':author_id', $commentary->getAuthorId());
        $stmt->bindValue(':article_id', $commentary->getArticleId());
        $success = $stmt->execute();
        if (!$success) {
            return false;
        }

        return $commentary->setId($this->db->lastInsertId());

    }

    protected function update(Commentary $commentary): Commentary|false
    {
        $query = 'UPDATE commentaires SET content = :content, author_id = :author_id, article_id = :article_id, is_valid = :is_valid WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':content', $commentary->getContent());
        $stmt->bindValue(':author_id', $commentary->getAuthorId());
        $stmt->bindValue(':article_id', $commentary->getArticleId());
        $stmt->bindValue(':is_valid', $commentary->getIsValid());
        $stmt->bindValue(':id', $commentary->getId());

        $success = $stmt->execute();
        if (!$success) {
            return false;
        }

        return $commentary;
    }

    /**
     * @throws ReflectionException
     */
    public function getCommentary(int $id): Commentary|false
    {
        $stmt = $this->db->prepare("SELECT * FROM commentaires WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        if(!$article)
            return false;
        return $this->hydrate((array) $article, new Commentary());
    }

    /**
     * @throws ReflectionException
     */
    public function getCommentaries(int $idArticle = null, int $page = 1, int $limit = self::LIMIT, array $filters = [], string $search = '', string $orderBy = self::ORDER_BY, string $order = self::ORDER): array|false
    {
        $offset = (max($page, 1) - 1) * $limit;
        $query = "SELECT commentaires.id as comment_id, commentaires.*, utilisateurs.id as user_id, utilisateurs.*  
              FROM commentaires LEFT JOIN utilisateurs ON commentaires.author_id = utilisateurs.id";
        $conditions = [];
        $parameters = [];
        if( !isset($_SESSION['user']['roleLevel']) || $_SESSION['user']['roleLevel'] < 2){
            $filters['is_valid'] = 1;
        }
        if ($idArticle != null) {
            $conditions[] = "article_id = :article_id";
            $parameters[':article_id'] = $idArticle;
        }

        if (!empty($filters)) {
            foreach($filters as $key => $value) {
                $conditions[] = "$key = :$key";
                $parameters[":$key"] = $value;
            }
        }

        if (!empty($search)) {
            $conditions[] = "(content LIKE :search)";
            $parameters[':search'] = '%' . $search . '%';
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= " ORDER BY $orderBy $order";
        $query .= " LIMIT :lim OFFSET :off";
        $parameters[':lim'] = $limit;
        $parameters[':off'] = $offset;

        $stmt = $this->db->prepare($query);

        foreach ($parameters as $key => &$val) {
            if (is_int($val)) {
                $stmt->bindParam($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindParam($key, $val, PDO::PARAM_STR);
            }
        }

        $success = $stmt->execute();

        if (!$success) {
            return false;
        }

        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Hydrate results
        foreach ($comments as $key => $comment) {
            $userData = array_intersect_key($comment, array_flip(['user_id', 'firstname', 'lastname' , 'email']));
            $commentData = array_diff_key($comment, $userData);

            $commentObject = $this->hydrate($commentData, new Commentary());
            $userObject = $this->hydrate($userData, new User());
            $userObject->setid($comment['user_id']);
            $commentObject->setId($comment['comment_id']);
            $commentObject->setAuthor($userObject);


            $comments[$key] = $commentObject;
        }

        return ["data" => $comments, "total" => $this->count($filters, $search)];
    }

    private function count(array $filters, string $search): int
    {
        $query = "SELECT COUNT(*) FROM commentaires";

        if ($filters != null && count($filters) > 0) {
            $query .= " WHERE " . implode(" AND ", array_map(function($v) { return "$v = :$v"; }, array_keys($filters)));
        }

        if (!empty($search)) {
            $query .= (empty($filters) ? " WHERE" : " AND") . " (content LIKE :search)";
        }

        $stmt = $this->db->prepare($query);

        if ($filters != null && count($filters) > 0) {
            foreach ($filters as $key => $val) {
                $stmt->bindValue(":$key", $val);
            }
        }

        if (!empty($search)) {
            $stmt->bindValue(":search", "%" . $search . "%");
        }

        $success = $stmt->execute();
        if (!$success) {
            return false;
        }

        return $stmt->fetchColumn();
    }



}