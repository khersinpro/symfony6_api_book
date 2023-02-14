<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {   
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création des users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();

            if ($i === 0) {
                $user->setEmail('admin@gmail.com');
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setEmail($faker->email());
                $user->setRoles(['ROLE_USER']);
            }

            $user->setPassword($this->hasher->hashPassword($user, "password"));
            $manager->persist($user);
        }

        // Création des authors
        $authors = [];
        for ($i=0; $i < 20; $i++) {
            $author = new Author();
            $author->setName($faker->lastName());
            $manager->persist($author);
            $authors[] = $author;
        }

        // Création des livres
        for ($i=0; $i < 40; $i++) {
            $book = new Book();
            $book->setTitle($faker->name());
            $book->setContent(...$faker->paragraphs(2));
            $book->setAuthor($authors[array_rand($authors)]);
            $manager->persist($book);
        }
        
        $manager->flush();
    }
}
