<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route("book.get.one", parameters = { "id" = "expr(object.getId())" }),
 *      exclusion = @Hateoas\Exclusion(groups="getbooks")
 * )
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route("book.update.one", parameters = { "id" = "expr(object.getId())" }),
 *      exclusion = @Hateoas\Exclusion(groups="getbooks")
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route("book.delete.one", parameters = { "id" = "expr(object.getId())" }),
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
    #[Assert\NotBlank(message: 'Ce champ ne doit pas être vide.')]
    #[Assert\Length(min: 2, max: 255, 
        minMessage: 'Ce champ doit contenir au minimum {{ limit }} caractères.', 
        maxMessage: 'Ce champ ne doit pas contenir plus de {{ limit }} caractères.'
    )]
    private ?string $title = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['getbooks'])]
    #[Assert\NotBlank(message: 'Ce champ ne doit pas être vide.')]
    #[Assert\Length(min: 2, max: 255, 
        minMessage: 'Ce champ doit contenir au minimum {{ limit }} caractères.', 
        maxMessage: 'Ce champ ne doit pas contenir plus de {{ limit }} caractères.'
    )]
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
