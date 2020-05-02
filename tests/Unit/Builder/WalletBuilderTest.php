<?php

namespace App\Tests\Unit\Builder;

use App\Builder\WalletBuilder;
use App\Entity\User;
use App\Entity\Wallet;
use App\GraphQL\Input\Wallet\WalletCreateRequest;
use App\GraphQL\Input\Wallet\WalletUpdateRequest;
use App\Repository\WalletRepository;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class WalletBuilderTest extends TestCase
{
    public function test_wallet_builder_create()
    {
        $user = (new User())->setId(1);
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $entityManagerMock = $this->createMock(EntityManager::class);
        $service = new WalletBuilder($entityManagerMock, $authServiceMock);

        /**@var Wallet $wallet*/
        $wallet = $service->create()->build();

        $this->assertEquals(1, $wallet->getUserId());
    }

    public function test_wallet_builder_bind_create_request()
    {
        $user = (new User())->setId(1);

        $entityManagerMock = $this->createMock(EntityManager::class);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $request = new WalletCreateRequest();
        $request->name = 'Name';
        $request->color = '#FF00FF';

        $service = new WalletBuilder($entityManagerMock, $authServiceMock);

        /**@var Wallet $wallet*/
        $wallet = $service->create()->bind($request)->build();

        $this->assertEquals(1, $wallet->getUserId());
        $this->assertEquals('Name', $wallet->getName());
        $this->assertEquals('#FF00FF', $wallet->getColor());
    }

    public function test_wallet_builder_bind_update_request()
    {
        $user = (new User())->setId(1);

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->method('find')
            ->willReturn((new Wallet())->setUserId($user->getId()));

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock->method('getRepository')
            ->willReturn($walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $request = new WalletUpdateRequest();
        $request->id = 1;
        $request->name = 'Name';
        $request->color = '#FF00FF';

        $service = new WalletBuilder($entityManagerMock, $authServiceMock);

        /**@var Wallet $wallet*/
        $wallet = $service->create()->bind($request)->build();

        $this->assertEquals(1, $wallet->getUserId());
        $this->assertEquals('Name', $wallet->getName());
        $this->assertEquals('#FF00FF', $wallet->getColor());
    }
}
