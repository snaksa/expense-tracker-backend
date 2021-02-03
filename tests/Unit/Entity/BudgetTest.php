<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\Label;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class BudgetTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setId(1)
            ->setEmail('test@gmail.com')
            ->setPassword('password')
            ->setRoles(['role1', 'role2']);

        $startDate = new \DateTime();
        $endDate = (new \DateTime())->modify('+ 2 day');

        $budget = (new Budget())
            ->setId(1)
            ->setName('Test')
            ->setValue(300)
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setUser($user)
            ->setUserId($user->getId());

        $this->assertEquals(1, $budget->getId());
        $this->assertEquals('Test', $budget->getName());
        $this->assertEquals(300, $budget->getValue());
        $this->assertEquals(1, $budget->getUserId());
        $this->assertEquals($user, $budget->getUser());

        $category = (new Category())->setName('Test');
        $budget->addCategory($category);

        $this->assertEquals(1, $budget->getCategories()->count());

        $budget->removeCategory($category);
        $this->assertEquals(0, $budget->getCategories()->count());

        $label = (new Label())->setName('Test');
        $budget->addLabel($label);

        $this->assertEquals(1, $budget->getLabels()->count());

        $budget->removeLabel($label);
        $this->assertEquals(0, $budget->getLabels()->count());
    }
}
