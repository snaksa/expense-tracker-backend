<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\WalletFixtures;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class WalletRepositoryTest extends BaseTestCase
{
    /**
     * @var WalletRepository
     */
    private $repository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager
            ->getRepository(Wallet::class);

        $this->userRepository = $this->entityManager
            ->getRepository(User::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            WalletFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_find_by_ids(): void
    {
        $fixtureWallets = $this->filterFixtures(function ($entity) {
            return $entity instanceof Wallet
                && $entity->getUserId() === $this->user->getId();
        });

        $ids = array_map(function (Wallet $wallet) {
            return $wallet->getId();
        }, $fixtureWallets);

        $wallets = $this->repository->findByIds($ids);

        $this->assertEquals(count($fixtureWallets), count($wallets));
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $fixtureWallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $wallet = $this->repository->findOneById($fixtureWallet->getId());

        $this->assertEquals($fixtureWallet->getName(), $wallet->getName());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $dbUser = $this->userRepository->findOneById($this->user->getId());

        $wallet = new Wallet();
        $wallet->setName('Test');
        $wallet->setColor('red');
        $wallet->setUserId($dbUser->getId());
        $wallet->setUser($dbUser);
        $this->repository->save($wallet);

        $this->assertNotNull($wallet->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $fixtureWallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $id = $fixtureWallet->getId();
        $wallet = $this->repository->findOneById($id);
        $this->repository->remove($wallet);

        $wallet = $this->repository->findOneById($id);
        $this->assertNull($wallet);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
