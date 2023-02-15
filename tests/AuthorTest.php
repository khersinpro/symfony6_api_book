<?php

namespace App\Tests;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AuthorTest extends KernelTestCase
{
    public function getEntity(): Author
    {
        return new Author();
    }

    public function assertCountErrors(Author $author, int $nbOfErrors)
    {
        $validator = static::getContainer()->get('validator');
        $errors = $validator->validate($author);

        $errorMessages = [];

        foreach ($errors as $error) {
            $errorMessages[] = $error->getPropertyPath(). ' => ' . $error->getMessage();
        }

        $this->assertCount($nbOfErrors, $errors, implode(',', $errorMessages));
    }

    public function testValidAuthor(): void
    {
        self::bootKernel();
        $author = $this->getEntity();
        $author->setName('Nom Valide');

        $this->assertCountErrors($author, 0);
    }

    public function testInvalidAuthorName(): void
    {
        self::bootKernel();
        $author = $this->getEntity();
        
        $this->assertCountErrors($author->setName('a'), 1);
        $this->assertCountErrors($author->setName(''), 2);
        $this->assertCountErrors($author->setName('Ce message est trop long pour être un nom d\'autheur...'), 1);
    }
}
