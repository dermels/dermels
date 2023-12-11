<?php

namespace Model;

use PDO;
use ReflectionException;

class UserRepository
{
    private PDO $db;
    private Hydrator $hydrator;

    public function __construct(PDO $db)
    {
        $this->hydrator = new Hydrator();
        $this->db = $db;
    }

    /**
     * @throws ReflectionException
     */
    public function createUser($firstname, $lastname, $password, $email)
    {

        // verifie qu'aucun user ne soit deja enregistré avec cet email
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            return false;
        }


        $stmt = $this->db->prepare("INSERT INTO utilisateurs (firstname, lastname, password, email, role) VALUES (?, ?, ?, ?, 1)");

        return $this->hydrator->hydrate((array)$stmt->execute([$firstname, $lastname, $password, $email]), new User());
    }

    /**
     * @throws ReflectionException
     */
    public function getUser($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        return $this->hydrator->hydrate((array)$stmt->fetch(), new User());
    }

}