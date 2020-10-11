<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class CategoryRepositoryTest extends BaseTestCase
{
    /**
     * @var CategoryRepository
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
            ->getRepository(Category::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            CategoryFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_find_user_categories(): void
    {
        $fixtureCategories = $this->filterFixtures(function ($entity) {
            return $entity instanceof Category
                && $entity->getUserId() === $this->user->getId();
        });

        $categories = $this->repository->findUserCategories($this->user);

        $this->assertEquals(count($fixtureCategories), count($categories));
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $fixtureCategory = $this->fixtures->getReference('category_food');
        $category = $this->repository->findOneById($fixtureCategory->getId());

        $this->assertEquals($fixtureCategory->getName(), $category->getName());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $category = new Category();
        $category->setName('Test');
        $category->setColor('red');
        $category->setUser($this->user);
        $this->repository->save($category);

        $this->assertNotNull($category->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $fixtureCategory = $this->fixtures->getReference('category_food');
        $id = $fixtureCategory->getId();
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
