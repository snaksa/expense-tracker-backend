<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User $demoUser  */
        $demoUser = $this->getReference('user_demo');

        /** @var User $demoUser2  */
        $demoUser2 = $this->getReference('user_demo2');

        $category = new Category();
        $category->setName('Food');
        $category->setColor('#f98f83');
        $category->setIcon(1);
        $category->setUser($demoUser);
        $this->setReference('category_food', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Clothes');
        $category->setColor('#0099ff');
        $category->setIcon(2);
        $category->setUser($demoUser);
        $this->setReference('category_clothes', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Gaming');
        $category->setColor('#00ff00');
        $category->setIcon(2);
        $category->setUser($demoUser);
        $this->setReference('category_gaming', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Fuel');
        $category->setColor('#996633');
        $category->setIcon(2);
        $category->setUser($demoUser);
        $this->setReference('category_fuel', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Income');
        $category->setColor('#00ffff');
        $category->setIcon(2);
        $category->setUser($demoUser);
        $this->setReference('category_income', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('OLX');
        $category->setColor('#DE60D4');
        $category->setIcon(2);
        $category->setUser($demoUser);
        $this->setReference('category_olx', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Food');
        $category->setColor('#f98f83');
        $category->setIcon(2);
        $category->setUser($demoUser2);
        $this->setReference('category_food_2', $category);
        $manager->persist($category);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
