<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\Transaction;
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
            ->setId(1)
            ->setName('Test')
            ->setColor('#FF00FF')
            ->setIcon(1)
            ->setUserId(1)
            ->setUser($user);

        $this->assertEquals(1, $category->getId());
        $this->assertEquals('Test', $category->getName());
        $this->assertEquals('#FF00FF', $category->getColor());
        $this->assertEquals(1, $category->getIcon());
        $this->assertEquals(1, $category->getUserId());
        $this->assertEquals($user, $category->getUser());

        $transaction = (new Transaction())->setDescription('Description');
        $category->addTransaction($transaction);

        $this->assertEquals(1, $category->getTransactions()->count());

        $category->removeTransaction($transaction);
        $this->assertEquals(0, $category->getTransactions()->count());
    }
}
