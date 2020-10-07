<?php

namespace App\DataFixtures;

use App\Entity\Label;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LabelFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User $demoUser  */
        $demoUser = $this->getReference('user_demo');

        /** @var User $demoUser2  */
        $demoUser2 = $this->getReference('user_demo2');

        $label = new Label();
        $label->setName('Essentials');
        $label->setColor('#f98f83');
        $label->setUser($demoUser);
        $label->setUserId($demoUser->getId());
        $this->setReference('label_essentials', $label);
        $manager->persist($label);

        $label = new Label();
        $label->setName('Spoiling');
        $label->setColor('#f98f83');
        $label->setUser($demoUser);
        $label->setUserId($demoUser->getId());
        $this->setReference('label_spoiling', $label);
        $manager->persist($label);

        $label = new Label();
        $label->setName('Essentials');
        $label->setColor('#f98f83');
        $label->setUser($demoUser2);
        $label->setUserId($demoUser2->getId());
        $this->setReference('label_essentials_2', $label);
        $manager->persist($label);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
