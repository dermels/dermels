<?php

namespace Model;

class Article
{
    private ?int $id;
    private ?string $title;
    private ?string $chapo;
    private ?string $content;
    private ?int $author_id;

    private ?string $dateCreation;

    private ?string $dateMaj;

    public function __construct($id = null, $title = '', $chapo = '', $content = '', $author_id = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->chapo = $chapo;
        $this->content = $content;
        $this->author_id = $author_id;
    }



    // Getter and Setter for Id
    public function getId(): int|null
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    // Getter and Setter for Title
    public function getTitle() : string|null
    {
        return $this->title;
    }

    public function setTitle($title) : self
    {
        $this->title = $title;

        return $this;
    }

    // Getter and Setter for Chapo
    public function getChapo() : string|null
    {
        return $this->chapo;
    }

    public function setChapo($chapo) : self
    {
        $this->chapo = $chapo;

        return $this;
    }

    // Getter and Setter for Content
    public function getContent() : string|null
    {
        return $this->content;
    }

    public function setContent($content) : self
    {
        $this->content = $content;

        return $this;
    }

    // Getter and Setter for Author Id
    public function getAuthorId() : int|null
    {
        return $this->author_id;
    }

    public function setAuthorId($author_id) : self
    {
        $this->author_id = $author_id;

        return $this;
    }

    // Getter and Setter for Date Creation
    public function getDateCreation() : string|null
    {
        return $this->dateCreation;
    }

    public function setDateCreation($dateCreation) : self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    // Getter and Setter for Date Maj
    public function getDateMaj() : string|null
    {
        return $this->dateMaj;
    }

    public function setDateMaj($dateMaj) : self
    {
        $this->dateMaj = $dateMaj;

        return $this;
    }


}