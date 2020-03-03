<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\GraphQL\Types\TransactionType;
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
            ->setId(1)
            ->setName('Test')
            ->setColor('#FF00FF')
            ->setInitialAmount(10)
            ->setUserId(1)
            ->setUser($user);

        $this->assertEquals(1, $wallet->getId());
        $this->assertEquals('Test', $wallet->getName());
        $this->assertEquals('#FF00FF', $wallet->getColor());
        $this->assertEquals(1, $wallet->getUserId());
        $this->assertEquals($user, $wallet->getUser());

        $transaction = (new Transaction())->setDescription('Description')->setValue(10)->setType(TransactionType::INCOME);
        $wallet->addTransaction($transaction);
        $this->assertEquals(1, $wallet->getTransactions()->count());

        $this->assertEquals(20, $wallet->getAmount());
        $wallet->removeTransaction($transaction);

        $transaction = (new Transaction())->setValue(10)->setType(TransactionType::EXPENSE);
        $wallet->addTransaction($transaction);
        $this->assertEquals(0, $wallet->getAmount());
        $wallet->removeTransaction($transaction);

        $transaction = (new Transaction())->setValue(10)->setType(TransactionType::TRANSFER)->setWallet($wallet);
        $wallet->addTransaction($transaction);
        $this->assertEquals(0, $wallet->getAmount());
        $wallet->removeTransaction($transaction);

        $transaction = (new Transaction())->setValue(10)->setType(TransactionType::TRANSFER)->setWalletReceiver($wallet);
        $wallet->addTransferInTransaction($transaction);
        $this->assertEquals(20, $wallet->getAmount());
        $wallet->removeTransferInTransaction($transaction);
    }
}
