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
    public function createUser($firstname, $lastname, $password, $email): User|false
    {

        // verifie qu'aucun user ne soit deja enregistré avec cet email
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false;
        }


        $stmt = $this->db->prepare("INSERT INTO utilisateurs (firstname, lastname, password, email) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$firstname, $lastname, $password, $email]);

        if (!$success) {
            return false;
        }

        $user = new User();
        $user->setId($this->db->lastInsertId());
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setPassword($password);
        $user->setEmail($email);
        $user->setRoleLevel(1);

        return $user;
    }

    /**
     * @throws ReflectionException
     */
    public function getUser($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = (array)$stmt->fetch();
        if(!$user)
            return false;
        return $this->hydrator->hydrate($user, new User());
    }

}