<?php

namespace App\Tests;

use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookTest extends KernelTestCase
{
    public function getEntity(): Book
    {
        return new Book();
    }

    public function assertCountErrors(Book $book, int $nbOfErrors)
    {
        $validator = static::getContainer()->get('validator');
        $errors = $validator->validate($book);

        $errorMessages = [];

        foreach ($errors as $error) {
            $errorMessages[] = $error->getPropertyPath(). ' => ' . $error->getMessage();
        }

        $this->assertCount($nbOfErrors, $errors, implode(',', $errorMessages));
    }

    public function testValidBook(): void
    {
        self::bootKernel();
        $book = $this->getEntity();

        $book->setTitle('Titre valide');
        $book-> setContent('Contenu valide');
        $book->setComment('Commentaire valide');

        $this->assertCountErrors($book, 0);
    }

    public function testBookInvalidTitle()
    {
        $book = $this->getEntity();
        $book-> setContent('Contenu valide');
        $book->setComment('Commentaire valide');

        $this->assertCountErrors($book->setTitle('a'), 1);
        $this->assertCountErrors($book->setTitle(''), 2);
    }
    
    public function testBookInvalidContent()
    {
        $book = $this->getEntity();
        $book->setTitle('Titre valide');
        $book->setComment('Commentaire valide');

        $this->assertCountErrors($book->setContent(''), 2);
        $this->assertCountErrors($book->setContent('a'), 1);
    }
}
