<?php

namespace Model;

class User
{
    private ?int $id = null;
    private string $firstname;
    private string $lastname;
    private string $password;
    private string $email;
    private string $roleLevel;

    public function __construct()
    {
        // Définissez des valeurs par défaut pour les propriétés
        $this->firstname = '';
        $this->lastname = '';
        $this->password = '';
        $this->email = '';
        $this->roleLevel = 0;
    }

    // getters et setters pour chaque propriété

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRoleLevel(): string
    {
        return $this->roleLevel;
    }

    public function setRoleLevel(string $roleLevel): self
    {
        $this->roleLevel = $roleLevel;
        return $this;
    }
}