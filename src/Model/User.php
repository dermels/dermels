<?php

namespace Model;

class User
{
    private ?int $id = null;
    private ?string $firstname;
    private ?string $lastname;
    private ?string $password;
    private ?string $email;
    private ?int $roleLevel;

    public function __construct( $firstname = '', $lastname = '', $password = '', $email = '', $roleLevel = 1)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->password = $password;
        $this->email = $email;
        $this->roleLevel = $roleLevel;
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