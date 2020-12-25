<?php

namespace App\DataFixtures;

use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\Label;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BudgetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User $demoUser */
        $demoUser = $this->getReference('user_demo');

        /** @var User $demoUser2 */
        $demoUser2 = $this->getReference('user_demo2');

        /** @var Category $category */
        $category = $this->getReference('category_food');

        /** @var Category $categoryUser2 */
        $categoryUser2 = $this->getReference('category_food_2');

        /** @var Label $label */
        $label = $this->getReference('label_essentials');

        /** @var Label $labelSpoiling */
        $labelSpoiling = $this->getReference('label_spoiling');

        /** @var Label $labelUser2 */
        $labelUser2 = $this->getReference('label_essentials_2');

        $budget = new Budget();
        $budget->setName('Essentials');
        $budget->setValue(300);
        $budget->setUser($demoUser);
        $budget->addCategory($category);
        $budget->addLabel($label);
        $this->setReference('budget_essentials', $budget);
        $manager->persist($budget);

        $budget = new Budget();
        $budget->setName('Spoiling');
        $budget->setValue(200);
        $budget->setUser($demoUser);
        $budget->addCategory($category);
        $budget->addLabel($labelSpoiling);
        $this->setReference('budget_spoiling', $budget);
        $manager->persist($budget);

        $budget = new Budget();
        $budget->setName('Food');
        $budget->setValue(500);
        $budget->setUser($demoUser);
        $budget->addCategory($category);
        $budget->addLabel($label);
        $this->setReference('budget_food', $budget);
        $manager->persist($budget);

        $budget = new Budget();
        $budget->setName('Essentials');
        $budget->setValue(500);
        $budget->setUser($demoUser2);
        $budget->addCategory($categoryUser2);
        $budget->addLabel($labelUser2);
        $this->setReference('budget_essentials_2', $budget);
        $manager->persist($budget);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
            LabelFixtures::class,
        ];
    }
}
