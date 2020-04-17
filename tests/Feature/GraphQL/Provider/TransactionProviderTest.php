<?php

namespace App\Tests\Feature\GraphQL\Provider;

use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\TransactionFixtures;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\WalletFixtures;
use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use App\Services\AuthorizationService;
use App\Tests\Feature\BaseTestCase;
use App\Tests\Types\IntegerArrayType;
use App\Traits\DateUtils;
use KunicMarko\GraphQLTest\Type\EnumType;

class TransactionProviderTest extends BaseTestCase
{
    use DateUtils;

    /**
     * @var User
     */
    private $user;

    /**
     * @var User
     */
    private $secondUser;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Wallet
     */
    private $wallet;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            WalletFixtures::class,
            CategoryFixtures::class,
            TransactionFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
        $this->secondUser = $this->fixtures->getReference('user_demo2');
        $this->category = $this->fixtures->getReference('category_food');
        $this->wallet = $this->fixtures->getReference('user_demo_wallet_cash');

        $this->client = $this->makeClient();
    }

    /**
     * @test
     */
    public function can_retrieve_transactions_by_wallet(): void
    {
        $transactions = $this->filterFixtures(function ($entity) {
            return $entity instanceof Transaction
                && $entity->getWalletId() === $this->wallet->getId();
        });

        usort($transactions, function (Transaction $a, Transaction $b) {
            return $a->getDate()->format('Y-m-d H:m:s') === $b->getDate()->format('Y-m-d H:m:s') ? $a->getId() > $b->getId() : $a->getDate() < $b->getDate();
        });

        $transactions = array_slice($transactions, 0, 10);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transactions',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()])
                ]
            ],
            ['data' => ['id', 'date']]
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('transactions', $content);
        $this->assertArrayHasKey('data', $content['transactions']);

        $expected = array_map(function (Transaction $transaction) {
            return [
                'id' => $transaction->getId(),
                'date' => $transaction->getDate()->format('Y-m-d H:i:s')
            ];
        }, $transactions);

        $this->assertEquals($expected, $content['transactions']['data']);
    }

    /**
     * @test
     */
    public function can_retrieve_transactions_by_back_date(): void
    {
        $date = $this->getCurrentDateTime()->modify('- 2 day')->setTime(0, 0, 0, 0);
        $transactions = $this->filterFixtures(function ($entity) use ($date) {
            return $entity instanceof Transaction
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getDate() >= $date;
        });

        usort($transactions, function (Transaction $a, Transaction $b) {
            return $a->getDate()->format('Y-m-d H:m:s') === $b->getDate()->format('Y-m-d H:m:s') ? $a->getId() > $b->getId() : $a->getDate() < $b->getDate();
        });

        $transactions = array_slice($transactions, 0, 10);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transactions',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'date' => $date->format('Y-m-d')
                ]
            ],
            ['data' => ['id', 'date']]
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('transactions', $content);
        $this->assertArrayHasKey('data', $content['transactions']);

        $expected = array_map(function (Transaction $transaction) {
            return [
                'id' => $transaction->getId(),
                'date' => $transaction->getDate()->format('Y-m-d H:i:s')
            ];
        }, $transactions);

        $this->assertEquals($expected, $content['transactions']['data']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_transactions_if_wallet_not_possessed_by_current_user(): void
    {
        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->secondUser);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transactions',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()])
                ]
            ],
            ['data' => ['id']]
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('transactions', $content);
        $this->assertArrayHasKey('data', $content['transactions']);
        $this->assertEquals([], $content['transactions']['data']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_transactions_if_not_logged(): void
    {
        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transactions',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()])
                ]
            ],
            ['data' => ['id']]
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_retrieve_single_transaction(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $transaction = $wallet->getTransactions()->first();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->once())->method('findOneById')->with($transaction->getId())->willReturn($transaction);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transaction',
            ['id' => $transaction->getId()],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('transaction', $content);

        $expected = [
            'id' => $wallet->getId()
        ];

        $this->assertEquals($expected, $content['transaction']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_transaction_if_not_current_user(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo2_wallet_loan');
        $transaction = $wallet->getTransactions()->first();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->once())->method('findOneById')->with($transaction->getId())->willReturn($transaction);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transaction',
            ['id' => $transaction->getId()],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_transaction_if_not_logged(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $transaction = $wallet->getTransactions()->first();

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transaction',
            ['id' => $transaction->getId()],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_transaction_if_does_not_exist(): void
    {
        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->once())->method('findOneById')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transaction',
            ['id' => -1],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Transaction not found!', $response);
    }

    /**
     * @test
     */
    public function can_create_transaction(): void
    {
        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'description' => 'test',
            'value' => 10,
            'type' => new EnumType('EXPENSE'),
            'categoryId' => $this->category->getId(),
            'walletId' => $this->wallet->getId(),
            'date' => '2019-12-12 12:12:12'
        ];

        $this->mutation(
            'createTransaction',
            [
                'input' => $inputParams
            ],
            [
                'description',
                'value',
                'date',
                'category' => ['id'],
                'wallet' => ['id']
            ]
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('createTransaction', $content);

        $expected = [
            'description' => 'test',
            'value' => 10,
            'category' => ['id' => $this->category->getId()],
            'wallet' => ['id' => $this->wallet->getId()],
            'date' => '2019-12-12 12:12:12'
        ];

        $this->assertEquals($expected, $content['createTransaction']);
    }

    /**
     * @test
     */
    public function can_not_create_transaction_without_description(): void
    {
        $inputParams = [
            'description' => '',
            'value' => 10,
            'type' => new EnumType('EXPENSE'),
            'categoryId' => $this->category->getId(),
            'walletId' => $this->wallet->getId(),
            'date' => '2019-12-12 12:12:12'
        ];

        $this->mutation(
            'createTransaction',
            [
                'input' => $inputParams
            ],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Description should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_transaction_without_existing_category(): void
    {
        $inputParams = [
            'description' => 'test',
            'value' => 10,
            'type' => new EnumType('EXPENSE'),
            'categoryId' => -1,
            'walletId' => $this->wallet->getId(),
            'date' => '2019-12-12 12:12:12'
        ];

        $this->mutation(
            'createTransaction',
            [
                'input' => $inputParams
            ],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Category not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_transaction_without_existing_wallet(): void
    {
        $inputParams = [
            'description' => 'test',
            'value' => 10,
            'type' => new EnumType('EXPENSE'),
            'categoryId' => $this->category->getId(),
            'walletId' => -1,
            'date' => '2019-12-12 12:12:12'
        ];

        $this->mutation(
            'createTransaction',
            [
                'input' => $inputParams
            ],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Wallet not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_transaction_if_not_logged(): void
    {
        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'description' => 'test',
            'value' => 10,
            'type' => new EnumType('EXPENSE'),
            'categoryId' => $this->category->getId(),
            'walletId' => $this->wallet->getId(),
            'date' => '2019-12-12 12:12:12'
        ];

        $this->mutation(
            'createTransaction',
            [
                'input' => $inputParams
            ],
            ['id'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_transaction_if_not_wallet_not_possessed_by_current_user(): void
    {
        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->secondUser);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'description' => 'test',
            'value' => 10,
            'type' => new EnumType('EXPENSE'),
            'categoryId' => $this->category->getId(),
            'walletId' => $this->wallet->getId(),
            'date' => '2019-12-12 12:12:12'
        ];

        $this->mutation(
            'createTransaction',
            [
                'input' => $inputParams
            ],
            ['id'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_update_transaction(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $transaction = $wallet->getTransactions()->first();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->exactly(2))->method('find')->willReturn($transaction);
        $transactionRepository->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $transaction->getId(),
            'description' => 'test',
            'value' => 10,
            'type' => new EnumType('EXPENSE'),
            'categoryId' => $this->category->getId(),
            'walletId' => $this->wallet->getId(),
            'date' => '2019-12-12 12:12:12'
        ];

        $this->mutation(
            'updateTransaction',
            [
                'input' => $inputParams
            ],
            [
                'id',
                'description',
                'value',
                'date',
                'category' => ['id'],
                'wallet' => ['id']
            ]
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('updateTransaction', $content);

        $expected = [
            'id' => $transaction->getId(),
            'description' => 'test',
            'value' => 10,
            'category' => ['id' => $this->category->getId()],
            'wallet' => ['id' => $this->wallet->getId()],
            'date' => '2019-12-12 12:12:12'
        ];

        $this->assertEquals($expected, $content['updateTransaction']);
    }

    /**
     * @test
     */
    public function can_not_update_transaction_if_does_not_exist(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $transaction = $wallet->getTransactions()->first();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->once())->method('find')->willReturn(null);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $inputParams = [
            'id' => -1
        ];

        $this->mutation(
            'updateTransaction',
            [
                'input' => $inputParams
            ],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Transaction not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_transaction_if_not_possession_of_current_user(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $transaction = $wallet->getTransactions()->first();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->exactly(2))->method('find')->willReturn($transaction);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->secondUser);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $transaction->getId()
        ];

        $this->mutation(
            'updateTransaction',
            [
                'input' => $inputParams
            ],
            ['id'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_transaction_if_not_logged(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $transaction = $wallet->getTransactions()->first();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->once())->method('find')->willReturn($transaction);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $transaction->getId()
        ];

        $this->mutation(
            'updateTransaction',
            [
                'input' => $inputParams
            ],
            ['id'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_delete_transaction(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $transaction = $wallet->getTransactions()->first();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->once())->method('find')->with($transaction->getId())->willReturn($transaction);
        $transactionRepository->expects($this->once())->method('findOneById')->with($transaction->getId())->willReturn($transaction);
        $transactionRepository->expects($this->once())->method('remove')->willReturn($transaction);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $wallet->getId()
        ];

        $this->mutation(
            'deleteTransaction',
            [
                'input' => $inputParams
            ],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('deleteTransaction', $content);

        $expected = [
            'id' => $wallet->getId()
        ];

        $this->assertEquals($expected, $content['deleteTransaction']);
    }

    /**
     * @test
     */
    public function can_not_delete_transaction_if_does_not_exists(): void
    {
        $transaction = $this->createMock(TransactionRepository::class);
        $transaction->expects($this->once())->method('find')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(TransactionRepository::class, $transaction);

        $inputParams = [
            'id' => -1
        ];

        $this->mutation(
            'deleteTransaction',
            [
                'input' => $inputParams
            ],
            ['id'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Transaction not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_transaction_if_not_possession_of_current_user(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $transaction = $wallet->getTransactions()->first();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->once())->method('find')->with($transaction->getId())->willReturn($transaction);
        $transactionRepository->expects($this->once())->method('findOneById')->with($transaction->getId())->willReturn($transaction);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->secondUser);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $wallet->getId()
        ];

        $this->mutation(
            'deleteTransaction',
            [
                'input' => $inputParams
            ],
            ['id'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_transaction_if_not_logged(): void
    {
        /** @var Wallet $wallet */
        $wallet = $this->fixtures->getReference('user_demo_wallet_cash');
        $transaction = $wallet->getTransactions()->first();

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->expects($this->once())->method('find')->with($transaction->getId())->willReturn($transaction);
        $this->client->getContainer()->set(TransactionRepository::class, $transactionRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $wallet->getId()
        ];

        $this->mutation(
            'deleteTransaction',
            [
                'input' => $inputParams
            ],
            ['id'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }
}
