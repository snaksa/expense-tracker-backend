<?php

namespace App\Tests\Feature\GraphQL\Provider;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\WalletFixtures;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use App\Services\AuthorizationService;
use App\Tests\Feature\BaseTestCase;

class WalletProviderTest extends BaseTestCase
{
    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            WalletFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_retrieve_user_wallets(): void
    {
        $wallets = $this->filterFixtures(function ($entity) {
            return $entity instanceof Wallet
                && $entity->getUserId() === $this->user->getId();
        });

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('findBy')->with(['user_id' => $this->user->getId()])->willReturn($wallets);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'wallets',
            [],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('wallets', $content);

        $expected = array_map(function (Wallet $wallet) {
            return ['id' => $wallet->getId()];
        }, $wallets);

        $this->assertEquals($expected, $content['wallets']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_user_wallets_if_not_logged(): void
    {
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'wallets',
            [],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_retrieve_single_wallet(): void
    {
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('findOneById')->with($wallet->getId())->willReturn($wallet);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'wallet',
            ['id' => $wallet->getId()],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('wallet', $content);

        $expected = [
            'id' => $wallet->getId(),
            'name' => $wallet->getName(),
            'color' => $wallet->getColor(),
        ];

        $this->assertEquals($expected, $content['wallet']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_wallet_if_not_current_user(): void
    {
        $wallet = $this->fixtures->getReference('user_demo2_wallet_loan');

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('findOneById')->with($wallet->getId())->willReturn($wallet);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'wallet',
            ['id' => $wallet->getId()],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_wallet_if_not_logged(): void
    {
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'wallet',
            ['id' => $wallet->getId()],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_wallet_if_does_not_exist(): void
    {
        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('findOneById')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'wallet',
            ['id' => -1],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Wallet not found!', $response);
    }

    /**
     * @test
     */
    public function can_create_wallet(): void
    {
        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('save');
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->exactly(2))->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'createWallet',
            [
                'input' => $inputParams
            ],
            ['name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('createWallet', $content);

        $expected = $inputParams;

        $this->assertEquals($expected, $content['createWallet']);
    }

    /**
     * @test
     */
    public function can_not_create_wallet_without_name(): void
    {

        $inputParams = [
            'name' => '',
            'color' => 'red'
        ];

        $this->mutation(
            'createWallet',
            [
                'input' => $inputParams
            ],
            ['name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Name should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_wallet_without_color(): void
    {
        $inputParams = [
            'name' => 'test',
            'color' => ''
        ];

        $this->mutation(
            'createWallet',
            [
                'input' => $inputParams
            ],
            ['name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Color should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_wallet_if_not_logged(): void
    {
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'createWallet',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_update_wallet(): void
    {
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->exactly(2))->method('find')->with($wallet->getId())->willReturn($wallet);
        $walletRepository->expects($this->once())->method('save');
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $wallet->getId(),
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateWallet',
            [
                'input' => $inputParams
            ],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('updateWallet', $content);

        $expected = $inputParams;

        $this->assertEquals($expected, $content['updateWallet']);
    }

    /**
     * @test
     */
    public function can_not_update_wallet_if_does_not_exist(): void
    {
        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('find')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $inputParams = [
            'id' => -1,
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateWallet',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Wallet not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_wallet_if_not_possession_of_current_user(): void
    {
        $wallet = $this->fixtures->getReference('user_demo2_wallet_loan');

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->exactly(2))->method('find')->with($wallet->getId())->willReturn($wallet);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $wallet->getId(),
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateWallet',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_wallet_if_not_logged(): void
    {
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('find')->with($wallet->getId())->willReturn($wallet);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $wallet->getId(),
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateWallet',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_delete_wallet(): void
    {
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('find')->with($wallet->getId())->willReturn($wallet);
        $walletRepository->expects($this->once())->method('findOneById')->with($wallet->getId())->willReturn($wallet);
        $walletRepository->expects($this->once())->method('remove');
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $wallet->getId()
        ];

        $this->mutation(
            'deleteWallet',
            [
                'input' => $inputParams
            ],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('deleteWallet', $content);

        $expected = [
            'id' => $wallet->getId(),
            'name' => $wallet->getName(),
            'color' => $wallet->getColor()
        ];

        $this->assertEquals($expected, $content['deleteWallet']);
    }

    /**
     * @test
     */
    public function can_not_delete_wallet_if_does_not_exists(): void
    {
        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('find')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $inputParams = [
            'id' => -1
        ];

        $this->mutation(
            'deleteWallet',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Wallet not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_wallet_if_not_possession_of_current_user(): void
    {
        $wallet = $this->fixtures->getReference('user_demo2_wallet_loan');

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('find')->with($wallet->getId())->willReturn($wallet);
        $walletRepository->expects($this->once())->method('findOneById')->with($wallet->getId())->willReturn($wallet);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $wallet->getId()
        ];

        $this->mutation(
            'deleteWallet',
            [
                'input' => $inputParams
            ],
            ['id', 'name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_wallet_if_not_logged(): void
    {
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->expects($this->once())->method('find')->with($wallet->getId())->willReturn($wallet);
        $this->client->getContainer()->set(WalletRepository::class, $walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $wallet->getId()
        ];

        $this->mutation(
            'deleteWallet',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }
}
