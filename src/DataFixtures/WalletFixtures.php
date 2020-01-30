<?php

namespace App\DataFixtures;

use App\Entity\Wallet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class WalletFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $wallet = new Wallet();
        $wallet->setName('Cash');
        $wallet->setColor('#F0FF0F');
        $wallet->setUser($this->getReference('user_demo'));
        $manager->persist($wallet);
        $this->setReference('user_demo_wallet_cash', $wallet);

        $wallet = new Wallet();
        $wallet->setName('Bank');
        $wallet->setColor('#FF000F');
        $wallet->setUser($this->getReference('user_demo'));
        $manager->persist($wallet);
        $this->setReference('user_demo_wallet_bank', $wallet);

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }
}
