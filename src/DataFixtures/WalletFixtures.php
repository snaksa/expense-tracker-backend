<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class WalletFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User $demoUser */
        $demoUser = $this->getReference('user_demo');

        /** @var User $demoUser2 */
        $demoUser2 = $this->getReference('user_demo2');

        $wallet = new Wallet();
        $wallet->setName('Cash');
        $wallet->setColor('#ffff4d');
        $wallet->setUser($demoUser);
        $manager->persist($wallet);
        $this->setReference('user_demo_wallet_cash', $wallet);

        $wallet = new Wallet();
        $wallet->setName('Bank');
        $wallet->setColor('#ff0000');
        $wallet->setUser($demoUser);
        $manager->persist($wallet);
        $this->setReference('user_demo_wallet_bank', $wallet);

        $wallet = new Wallet();
        $wallet->setName('Loan');
        $wallet->setColor('#0099ff');
        $wallet->setUser($demoUser2);
        $manager->persist($wallet);
        $this->setReference('user_demo2_wallet_loan', $wallet);

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }
}
