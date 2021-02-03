<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\LabelFixtures;
use App\DataFixtures\BudgetFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Budget;
use App\Entity\User;
use App\Repository\BudgetRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class BudgetRepositoryTest extends BaseTestCase
{
    /**
     * @var BudgetRepository
     */
    private $repository;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager
            ->getRepository(Budget::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            CategoryFixtures::class,
            LabelFixtures::class,
            BudgetFixtures::class,
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_find_user_budgets(): void
    {
        $fixtureBudgets = $this->filterFixtures(function ($entity) {
            return $entity instanceof Budget
                && $entity->getUserId() === $this->user->getId();
        });

        $budgets = $this->repository->findUserBudgets($this->user);

        $this->assertEquals(count($fixtureBudgets), count($budgets));
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $fixtureBudget = $this->fixtures->getReference('budget_essentials');
        $budget = $this->repository->findOneById($fixtureBudget->getId());

        $this->assertEquals($fixtureBudget->getName(), $budget->getName());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $budget = new Budget();
        $budget->setName('Test');
        $budget->setValue(300);
        $budget->setUser($this->user);
        $this->repository->save($budget);

        $this->assertNotNull($budget->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $fixtureBudget = $this->fixtures->getReference('budget_essentials');
        $id = $fixtureBudget->getId();
        $category = $this->repository->findOneById($id);
        $this->repository->remove($category);

        $category = $this->repository->findOneById($id);
        $this->assertNull($category);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
