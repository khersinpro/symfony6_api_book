<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "book.get.one",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getbooks")
 * )
 *
 */
#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getbooks'])]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['getbooks'])]
    private ?string $title = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['getbooks'])]
    private ?string $content = null;
    
    #[ORM\ManyToOne(inversedBy: 'books',targetEntity: Author::class)]
    #[Groups(['getbooks'])]
    private ?author $author = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?author
    {
        return $this->author;
    }

    public function setAuthor(?author $author): self
    {
        $this->author = $author;

        return $this;
    }
}
