<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\Label;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\GraphQL\Types\TransactionType;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $wallet = (new Wallet())->setName('Wallet');
        $category = (new Category())->setName('Category');

        $transaction = (new Transaction())
            ->setId(1)
            ->setCategoryId(1)
            ->setCategory($category)
            ->setWalletId(1)
            ->setWallet($wallet)
            ->setDescription('Description')
            ->setValue(10)
            ->setType(TransactionType::INCOME);

        $this->assertEquals(1, $transaction->getId());
        $this->assertEquals(1, $transaction->getCategoryId());
        $this->assertEquals($category, $transaction->getCategory());
        $this->assertEquals(1, $transaction->getWalletId());
        $this->assertEquals($wallet, $transaction->getWallet());
        $this->assertEquals('Description', $transaction->getDescription());
        $this->assertEquals(10, $transaction->getValue());
        $this->assertEquals(TransactionType::INCOME, $transaction->getType());


        $label = (new Label())->setName('Description');
        $transaction->addLabel($label);

        $this->assertEquals(1, $transaction->getLabels()->count());

        $transaction->removeLabel($label);
        $this->assertEquals(0, $transaction->getLabels()->count());
    }
}
