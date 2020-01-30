<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $category = new Category();
        $category->setName('Food');
        $category->setColor('#F98F83');
        $category->setIcon(1);
        $this->setReference('category_food', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Clothes');
        $category->setColor('#A8B892');
        $category->setIcon(2);
        $this->setReference('category_clothes', $category);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Income');
        $category->setColor('#738F92');
        $category->setIcon(2);
        $this->setReference('category_income', $category);
        $manager->persist($category);

        $manager->flush();
    }
}
