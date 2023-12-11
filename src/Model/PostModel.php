<?php

namespace Model;

use PDO;

class PostModel
{
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): bool|array
    {
        // Récupérer tous les articles de blog depuis la base de données
        $query = $this->db->query("SELECT * FROM posts ORDER BY date_creation DESC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($postId) {
        // Récupérer un article de blog par son identifiant
        $query = $this->db->prepare("SELECT * FROM posts WHERE id = :id");
        $query->bindParam(':id', $postId);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}