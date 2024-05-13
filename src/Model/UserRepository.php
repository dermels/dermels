<?php

namespace Model;

use PDO;
use ReflectionException;

class UserRepository extends Hydrator
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createUser(User $user): User|false
    {

        // verifie qu'aucun user ne soit deja enregistré avec cet email
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$user->getEmail()]);
        if ($stmt->fetch()) {
            return false;
        }


        $stmt = $this->db->prepare("INSERT INTO utilisateurs (firstname, lastname, password, email) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$user->getFirstname(), $user->getLastname(), $user->getPassword(), $user->getEmail()]);

        if (!$success) {
            return false;
        }

        return $user->setId($this->db->lastInsertId())->setRoleLevel(1);
    }

    /**
     * @throws ReflectionException
     */
    public function getUserByMail($email) : User|false
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = (array)$stmt->fetch();
        if(!$user)
            return false;

        return $this->hydrate($user, new User());
    }

    /**
     * @throws ReflectionException
     */
    public function getUser($id) : User|false
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if(!$user)
            return false;
        return $this->hydrate((array)$user, new User());
    }

}