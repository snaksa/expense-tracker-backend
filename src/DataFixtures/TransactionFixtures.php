<?php

namespace App\DataFixtures;

use App\Entity\Transaction;
use App\GraphQL\Types\TransactionType;
use App\Traits\DateUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    use DateUtils;

    public function load(ObjectManager $manager)
    {
        $transaction = new Transaction();
        $transaction->setValue(12.54);
        $transaction->setDescription('Meat for dinner');
        $transaction->setType(TransactionType::EXPENSE);
        $transaction->setDate($this->getCurrentDateTime()->modify('- 2 day'));
        $transaction->setCategory($this->getReference('category_food'));
        $transaction->setWallet($this->getReference('user_demo_wallet_cash'));
        $manager->persist($transaction);

        $transaction = new Transaction();
        $transaction->setValue(25);
        $transaction->setDescription('Friends loans');
        $transaction->setType(TransactionType::INCOME);
        $transaction->setDate($this->getCurrentDateTime()->modify('- 2 hour'));
        $transaction->setCategory($this->getReference('category_income'));
        $transaction->setWallet($this->getReference('user_demo_wallet_cash'));
        $manager->persist($transaction);

        $transaction = new Transaction();
        $transaction->setValue(32.99);
        $transaction->setDescription('New jeans');
        $transaction->setType(TransactionType::EXPENSE);
        $transaction->setDate($this->getCurrentDateTime()->modify('- 1 day'));
        $transaction->setCategory($this->getReference('category_clothes'));
        $transaction->setWallet($this->getReference('user_demo_wallet_bank'));
        $manager->persist($transaction);

        $transaction = new Transaction();
        $transaction->setValue(800);
        $transaction->setDescription('Salary');
        $transaction->setType(TransactionType::INCOME);
        $transaction->setDate($this->getCurrentDateTime()->modify('- 2 minute'));
        $transaction->setCategory($this->getReference('category_income'));
        $transaction->setWallet($this->getReference('user_demo_wallet_bank'));
        $manager->persist($transaction);

        $transaction = new Transaction();
        $transaction->setValue(8);
        $transaction->setDescription('New t-shirt');
        $transaction->setType(TransactionType::EXPENSE);
        $transaction->setDate($this->getCurrentDateTime()->modify('- 2 day'));
        $transaction->setCategory($this->getReference('category_clothes'));
        $transaction->setWallet($this->getReference('user_demo_wallet_bank'));
        $manager->persist($transaction);

        $transaction = new Transaction();
        $transaction->setValue(23.49);
        $transaction->setDescription('Bottle of wine');
        $transaction->setType(TransactionType::EXPENSE);
        $transaction->setDate($this->getCurrentDateTime());
        $transaction->setCategory($this->getReference('category_food'));
        $transaction->setWallet($this->getReference('user_demo_wallet_cash'));
        $manager->persist($transaction);

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            CategoryFixtures::class,
            WalletFixtures::class
        );
    }
}
