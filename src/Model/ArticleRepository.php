<?php

namespace Model;

use PDO;
use ReflectionException;

class ArticleRepository extends Hydrator
{
    protected PDO $db;

    public const ORDER_BY = "date_creation";
    public const ORDER = "desc";
    public const LIMIT = 10;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @throws ReflectionException
     */
    public function save(Article $article): Article|false
    {
        if ($article->getId()) {
            return $this->update($article);
        }

        return $this->insert($article);
    }

    protected function insert(Article $article): Article|false
    {
        $query = 'INSERT INTO articles (title, chapo, content, author_id) VALUES (:title, :chapo, :content, :author_id)';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':title', $article->getTitle());
        $stmt->bindValue(':chapo', $article->getChapo());
        $stmt->bindValue(':content', $article->getContent());
        $stmt->bindValue(':author_id', $article->getAuthorId());

        $success = $stmt->execute();
        if (!$success) {
            return false;
        }

        return $article->setId($this->db->lastInsertId());

    }

    /**
     * @throws ReflectionException
     */
    protected function update(Article $article): Article|false
    {
        $verif = $this->getArticle($article->getId());
        if(!$verif)
            return false;
        $query = 'UPDATE articles SET title = :title, chapo = :chapo, content = :content, author_id = :author_id WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':title', $article->getTitle());
        $stmt->bindValue(':chapo', $article->getChapo());
        $stmt->bindValue(':content', $article->getContent());
        $stmt->bindValue(':author_id', $article->getAuthorId());
        $stmt->bindValue(':id', $article->getId());

        $success = $stmt->execute();
        if (!$success) {
            return false;
        }
        return $article;

    }

    /**
     * @throws ReflectionException
     */
    public function getArticle($id): Article|false
    {
        $stmt = $this->db->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        if(!$article)
            return false;
        return $this->hydrate((array) $article, new Article());
    }

    /**
     * @throws ReflectionException
     */
    public function getArticlesList(int $page = 1, int $limit = self::LIMIT, array $filters = [], string $search = '', string $orderBy = self::ORDER_BY, string $order = self::ORDER): array
    {
        // Calculer l'offset pour la requête SQL
        $offset = (max($page, 1) - 1) * $limit;

        // Construire la requête SQL
        $query = "SELECT * FROM articles";

        // Ajouter les conditions de recherche et de filtrage
        if (!empty($filters)) {
            $query .= " WHERE " . implode(" AND ", array_map(function($v) { return "$v = :$v"; }, array_keys($filters)));
        }
        if (!empty($search)) {
            $query .= (empty($filters) ? " WHERE" : " AND") . " (commentaires LIKE :search)";
        }
        // Ajouter l'ordre de tri et le type d'ordre
        $query .= " ORDER BY $orderBy " . (($order === 'asc') ? 'ASC' : 'DESC');
        $query .= " LIMIT :limit OFFSET :offset";

        // Préparer et exécuter la requête
        $statement = $this->db->prepare($query);
        if (!empty($filters)) {
            foreach ($filters as $key => $val) {
                $statement->bindValue(":$key", $val);
            }
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);

        if (!empty($search)) {
            $statement->bindValue(':search', "%$search%");
        }

        foreach ($filters as $column => $value) {
            $statement->bindValue(":$column", $value);
        }

        $statement->execute();
        // Récupérer tous les résultats
        $articles = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Hydrater les résultats
        foreach ($articles as $key => $article) {
            $articles[$key] = $this->hydrate($article, new Article());
        }


        return ["data"=>$articles, "total" => $this->count($filters, $search)];
    }

    private function count(array $filters, string $search)
    {
        // Construire la requête SQL
        $query = "SELECT COUNT(*) FROM articles";

        // Ajouter les conditions de recherche et de filtrage
        $conditions = [];
        if (!empty($search)) {
            $conditions[] = "title LIKE :search";
        }
        foreach ($filters as $column => $value) {
            $conditions[] = "$column = :$column";
        }

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        // Préparer et exécuter la requête
        $statement = $this->db->prepare($query);

        if (!empty($search)) {
            $statement->bindValue(':search', "%$search%");
        }

        foreach ($filters as $column => $value) {
            $statement->bindValue(":$column", $value);
        }

        $statement->execute();

        return $statement->fetchColumn();
    }
}