<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setEmail('test@gmail.com')
            ->setPassword('password')
            ->setRoles(['role1', 'role2']);

        $category = (new Category())
            ->setName('Test')
            ->setColor('#FF00FF')
            ->setIcon(1)
            ->setUserId(1)
            ->setUser($user);

        $this->assertEquals(null, $category->getId());
        $this->assertEquals('Test', $category->getName());
        $this->assertEquals('#FF00FF', $category->getColor());
        $this->assertEquals(1, $category->getIcon());
        $this->assertEquals(1, $category->getUserId());
        $this->assertEquals($user, $category->getUser());
    }
}
