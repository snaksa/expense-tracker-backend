<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $category = new Category();
        $category->setName('Food');
        $category->setColor('#F98F83');
        $category->setIcon(1);
        $category->setUser($this->getReference('user_demo'));
        $this->setReference('category_food', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Clothes');
        $category->setColor('#A8B892');
        $category->setIcon(2);
        $category->setUser($this->getReference('user_demo'));
        $this->setReference('category_clothes', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Gaming');
        $category->setColor('#C3B892');
        $category->setIcon(2);
        $category->setUser($this->getReference('user_demo'));
        $this->setReference('category_gaming', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Fuel');
        $category->setColor('#A84322');
        $category->setIcon(2);
        $category->setUser($this->getReference('user_demo'));
        $this->setReference('category_fuel', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Income');
        $category->setColor('#738F92');
        $category->setIcon(2);
        $category->setUser($this->getReference('user_demo'));
        $this->setReference('category_income', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('OLX');
        $category->setColor('#A33492');
        $category->setIcon(2);
        $category->setUser($this->getReference('user_demo'));
        $this->setReference('category_olx', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Food');
        $category->setColor('#A33492');
        $category->setIcon(2);
        $category->setUser($this->getReference('user_demo2'));
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
