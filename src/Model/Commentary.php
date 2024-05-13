<?php

namespace Model;

class Commentary
{


    private ?int $id;

    private ?string $content;

    private ?int $author_id;

    private ?int $article_id;

    private ?string $dateCreation;

    private ?bool $isValid;

    // constructeur
    public function __construct($id = null, $content = '', $author_id = null, $article_id = null, $isValid = false, $author = null)
    {
        $this->id = $id;
        $this->content = $content;
        $this->author_id = $author_id;
        $this->article_id = $article_id;
        $this->isValid = $isValid;
        $this->author = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;

    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;

    }

    public function getAuthorId(): ?int
    {
        return $this->author_id;
    }

    public function setAuthorId(?int $author_id): self
    {
        $this->author_id = $author_id;
        return $this;

    }

    public function getArticleId(): ?int
    {
        return $this->article_id;
    }

    public function setArticleId(?int $article_id): self
    {
        $this->article_id = $article_id;
        return $this;

    }

    public function getDateCreation(): ?string
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?string $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getIsValid(): ?bool
    {
        return $this->isValid;
    }

    public function setIsValid(?bool $isValid): self
    {
        $this->isValid = $isValid;
        return $this;
    }

    public function changeValid(): self
    {
        $this->isValid = !$this->isValid;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

}