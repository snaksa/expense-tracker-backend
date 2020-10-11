<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Label;
use App\Entity\Transaction;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class LabelTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setId(1)
            ->setEmail('test@gmail.com')
            ->setPassword('password')
            ->setRoles(['role1', 'role2']);

        $label = (new Label())
            ->setId(1)
            ->setName('Test')
            ->setColor('#FF00FF')
            ->setUser($user)
            ->setUserId($user->getId());

        $this->assertEquals(1, $label->getId());
        $this->assertEquals('Test', $label->getName());
        $this->assertEquals('#FF00FF', $label->getColor());
        $this->assertEquals(1, $label->getUserId());
        $this->assertEquals($user, $label->getUser());

        $transaction = (new Transaction())->setDescription('Description');
        $label->addTransaction($transaction);

        $this->assertEquals(1, $label->getTransactions()->count());

        $label->removeTransaction($transaction);
        $this->assertEquals(0, $label->getTransactions()->count());
    }
}
