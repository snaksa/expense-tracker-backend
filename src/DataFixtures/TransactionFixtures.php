<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Label;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\GraphQL\Types\TransactionType;
use App\Traits\DateUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    use DateUtils;

    public function load(ObjectManager $manager): void
    {
        /** @var Wallet[] $wallets */
        $wallets = [
            $this->getReference('user_demo_wallet_cash'),
            $this->getReference('user_demo_wallet_bank'),
            $this->getReference('user_demo2_wallet_loan')
        ];

        /** @var Category[] $incomeCategories */
        $incomeCategories = [
            $this->getReference('category_income'),
            $this->getReference('category_olx'),
        ];

        /** @var Category[] $categories */
        $categories = [
            $this->getReference('category_food'),
            $this->getReference('category_gaming'),
            $this->getReference('category_fuel'),
            $this->getReference('category_clothes')
        ];

        /** @var Label[] $labels */
        $labels = [
            $this->getReference('label_essentials'),
            $this->getReference('label_spoiling')
        ];

        foreach ($wallets as $wallet) {
            foreach ($incomeCategories as $category) {
                $transaction = new Transaction();
                $transaction->setValue(rand(400, 600));
                $transaction->setDescription('Salary');
                $transaction->setType(TransactionType::INCOME);
                $transaction->setDate($this->getCurrentDateTime()->modify('- 2 day'));
                $transaction->setCategory($category);
                $transaction->setWallet($wallet);
                $manager->persist($transaction);
                $this->setReference(
                    'transaction_' . $wallet->getName() . '_' . $category->getName(),
                    $transaction
                );
            }
        }

        foreach ($wallets as $wallet) {
            foreach ($categories as $key => $category) {
                $count = 0;
                while ($count < 10) {
                    $randomDays = rand(1, 50);
                    $amount = rand(10, 100) / 10;
                    $transaction = new Transaction();
                    $transaction->setValue($amount);
                    $transaction->setDescription('Expense record');
                    $transaction->setType(TransactionType::EXPENSE);
                    $transaction->setDate($this->getCurrentDateTime()->modify("- {$randomDays} day"));
                    $transaction->setCategory($category);
                    $transaction->setWallet($wallet);
                    if ($key % 2) {
                        $transaction->addLabel($labels[0]);
                    } else {
                        $transaction->addLabel($labels[1]);
                    }

                    $manager->persist($transaction);
                    $this->setReference(
                        'transaction_' . $wallet->getName() . '_' . $category->getName() . '_' . $count,
                        $transaction
                    );
                    $count++;
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            CategoryFixtures::class,
            LabelFixtures::class,
            WalletFixtures::class
        );
    }
}
