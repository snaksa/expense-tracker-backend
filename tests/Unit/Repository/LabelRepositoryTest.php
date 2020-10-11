<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\LabelFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Label;
use App\Entity\User;
use App\Repository\LabelRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class LabelRepositoryTest extends BaseTestCase
{
    /**
     * @var LabelRepository $repository
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
            ->getRepository(Label::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            LabelFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_find_user_labels(): void
    {
        $fixtureLabels = $this->filterFixtures(function ($entity) {
            return $entity instanceof Label
                && $entity->getUserId() === $this->user->getId();
        });

        $labels = $this->repository->findUserLabels($this->user);

        $this->assertEquals(count($fixtureLabels), count($labels));
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $fixtureLabel = $this->fixtures->getReference('label_essentials');
        $label = $this->repository->findOneById($fixtureLabel->getId());

        $this->assertEquals($fixtureLabel->getName(), $label->getName());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $label = new Label();
        $label->setName('Test');
        $label->setColor('red');
        $label->setUser($this->user);
        $this->repository->save($label);

        $this->assertNotNull($label->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $fixtureLabel = $this->fixtures->getReference('label_essentials');
        $id = $fixtureLabel->getId();
        $label = $this->repository->findOneById($id);
        $this->repository->remove($label);

        $label = $this->repository->findOneById($id);
        $this->assertNull($label);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
