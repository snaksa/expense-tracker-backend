<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\Wallet;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setEmail('test@gmail.com')
            ->setPassword('password')
            ->setRoles(['role1', 'role2']);

        $wallet = (new Wallet())
            ->setName('Test')
            ->setColor('#FF00FF')
            ->setUserId(1)
            ->setUser($user);

        $this->assertEquals(null, $wallet->getId());
        $this->assertEquals('Test', $wallet->getName());
        $this->assertEquals('#FF00FF', $wallet->getColor());
        $this->assertEquals(1, $wallet->getUserId());
        $this->assertEquals($user, $wallet->getUser());
    }
}
