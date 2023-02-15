<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function getEntity(): User
    {
        return new User();
    }

    public function assertCountErrors(User $user, int $nbOfErrors)
    {
        $validator = static::getContainer()->get('validator');
        $errors = $validator->validate($user);

        $errorMessages = [];

        foreach ($errors as $error) {
            $errorMessages[] = $error->getPropertyPath(). ' => ' . $error->getMessage();
        }

        $this->assertCount($nbOfErrors, $errors, implode(',', $errorMessages));
    }

    public function testValidUser(): void
    {
        self::bootKernel();
        $user = $this->getEntity();
        $user->setEmail('email@valide.com');
        $user->setPassword('monSuperPassword123');

        $this->assertCountErrors($user, 0);
    }

    public function testUserInvalidEmail()
    {
        $user = $this->getEntity();
        $user->setPassword('monSuperPassword123');

        $this->assertCountErrors($user->setEmail('https://email.com'), 1);
        $this->assertCountErrors($user->setEmail(''), 1);
        $this->assertCountErrors($user->setEmail(123456), 1);
    }
    
    public function testUserInvalidPassword()
    {
        $user = $this->getEntity();
        $user->setEmail('email@valide.com');

        $this->assertCountErrors($user->setPassword('petit'), 1);
        $this->assertCountErrors($user->setPassword('monSuperPassword123BienTropLong'), 1);
        $this->assertCountErrors($user->setPassword(''), 2);
    }
}
