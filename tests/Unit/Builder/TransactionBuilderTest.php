<?php

namespace App\Tests\Unit\Entity;

use App\Builder\TransactionBuilder;
use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Transaction\TransactionCreateRequest;
use App\GraphQL\Input\Transaction\TransactionUpdateRequest;
use App\GraphQL\Types\TransactionType;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class TransactionBuilderTest extends TestCase
{
    public function test_transaction_builder_create()
    {
        $user = (new User())->setId(1);
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $entityManagerMock = $this->createMock(EntityManager::class);
        $service = new TransactionBuilder($entityManagerMock, $authServiceMock);

        /**@var Transaction $transaction*/
        $transaction = $service->create()->build();

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    public function test_transaction_builder_bind_create_request()
    {
        $user = (new User())->setId(1);
        $category = (new Category())->setId(1)->setUserId($user->getId())->setName('Category');
        $wallet = (new Wallet())->setId(1)->setUserId($user->getId())->setName('Wallet');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->method('find')
            ->willReturn($category);

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->method('find')
            ->willReturn($wallet);

        $entityManagerMock = $this->createMock(EntityManager::class);

        $entityManagerMock
            ->expects($this->at(0))
            ->method('getRepository')
            ->willReturn($categoryRepository);

        $entityManagerMock
            ->expects($this->at(1))
            ->method('getRepository')
            ->willReturn($walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $type = new TransactionType();
        $type->value = TransactionType::INCOME;
        $request = new TransactionCreateRequest();
        $request->type = $type;
        $request->description = 'Description';
        $request->value = 10;
        $request->walletId = $wallet->getId();
        $request->categoryId = $category->getId();

        $service = new TransactionBuilder($entityManagerMock, $authServiceMock);

        /**@var Transaction $transaction*/
        $transaction = $service->create()->bind($request)->build();

        $this->assertEquals(TransactionType::INCOME, $transaction->getType());
        $this->assertEquals(10, $transaction->getValue());
        $this->assertEquals($wallet, $transaction->getWallet());
        $this->assertEquals($category, $transaction->getCategory());
        $this->assertEquals('Description', $transaction->getDescription());
    }

    public function test_wallet_builder_bind_update_request()
    {
        $user = (new User())->setId(1);
        $category = (new Category())->setId(1)->setUserId($user->getId())->setName('Category');
        $wallet = (new Wallet())->setId(1)->setUserId($user->getId())->setName('Wallet');
        $transaction = (new Transaction())->setValue(10);

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->method('find')
            ->willReturn($transaction);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->method('find')
            ->willReturn($category);

        $walletRepository = $this->createMock(TransactionRepository::class);
        $walletRepository->method('find')
            ->willReturn($wallet);

        $entityManagerMock = $this->createMock(EntityManager::class);

        $entityManagerMock
            ->expects($this->at(0))
            ->method('getRepository')
            ->willReturn($transactionRepository);

        $entityManagerMock
            ->expects($this->at(1))
            ->method('getRepository')
            ->willReturn($categoryRepository);

        $entityManagerMock
            ->expects($this->at(2))
            ->method('getRepository')
            ->willReturn($walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $type = new TransactionType();
        $type->value = TransactionType::INCOME;
        $request = new TransactionUpdateRequest();
        $request->id = 1;
        $request->type = $type;
        $request->description = 'Description';
        $request->value = 10;
        $request->walletId = $wallet->getId();
        $request->categoryId = $category->getId();

        $service = new TransactionBuilder($entityManagerMock, $authServiceMock);

        /**@var Transaction $transaction*/
        $transaction = $service->bind($request)->build();

        $this->assertEquals(TransactionType::INCOME, $transaction->getType());
        $this->assertEquals(10, $transaction->getValue());
        $this->assertEquals($wallet, $transaction->getWallet());
        $this->assertEquals($category, $transaction->getCategory());
        $this->assertEquals('Description', $transaction->getDescription());
    }

    public function test_wallet_builder_bind_update_request_exception()
    {
        $user = (new User())->setId(1);
        $category = (new Category())->setId(1)->setUserId($user->getId())->setName('Category');
        $wallet = (new Wallet())->setId(1)->setUserId(2)->setName('Wallet');
        $transaction = (new Transaction())->setValue(10);

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->method('find')
            ->willReturn($transaction);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->method('find')
            ->willReturn($category);

        $walletRepository = $this->createMock(TransactionRepository::class);
        $walletRepository->method('find')
            ->willReturn($wallet);

        $entityManagerMock = $this->createMock(EntityManager::class);

        $entityManagerMock
            ->expects($this->at(0))
            ->method('getRepository')
            ->willReturn($transactionRepository);

        $entityManagerMock
            ->expects($this->at(1))
            ->method('getRepository')
            ->willReturn($categoryRepository);

        $entityManagerMock
            ->expects($this->at(2))
            ->method('getRepository')
            ->willReturn($walletRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $type = new TransactionType();
        $type->value = TransactionType::INCOME;
        $request = new TransactionUpdateRequest();
        $request->id = 1;
        $request->type = $type;
        $request->description = 'Description';
        $request->value = 10;
        $request->walletId = $wallet->getId();
        $request->categoryId = $category->getId();

        $service = new TransactionBuilder($entityManagerMock, $authServiceMock);

        $this->expectException(UnauthorizedOperationException::class);

        $service->bind($request)->build();
    }
}
