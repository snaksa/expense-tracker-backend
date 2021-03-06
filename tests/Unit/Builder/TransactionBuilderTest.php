<?php

namespace App\Tests\Unit\Builder;

use App\Builder\TransactionBuilder;
use App\Entity\Category;
use App\Entity\Label;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Exception\RequiredEntityException;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Transaction\TransactionCreateRequest;
use App\GraphQL\Input\Transaction\TransactionUpdateRequest;
use App\GraphQL\Types\TransactionType;
use App\Repository\CategoryRepository;
use App\Repository\LabelRepository;
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

        /**@var Transaction $transaction */
        $transaction = $service->create()->build();

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    public function test_transaction_builder_bind_create_request()
    {
        $user = (new User())->setId(1);
        $category = (new Category())->setId(1)->setUserId($user->getId())->setName('Category');
        $wallet = (new Wallet())->setId(1)->setUserId($user->getId())->setName('Wallet');
        $walletReceiver = (new Wallet())->setId(2)->setUserId($user->getId())->setName('Cash');
        $label = (new Label())->setId(1)->setUserId($user->getId())->setName('Essentials');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->method('find')
            ->willReturn($category);

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->method('find')
            ->willReturn($label);

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository
            ->method('find')
            ->will($this->onConsecutiveCalls($wallet, $walletReceiver));

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock
            ->method('getRepository')
            ->willReturn($categoryRepository)
            ->will($this->onConsecutiveCalls($categoryRepository, $walletRepository, $walletRepository, $labelRepository));

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
        $request->walletReceiverId = $walletReceiver->getId();
        $request->categoryId = $category->getId();
        $request->labelIds = [$label->getId()];

        $service = new TransactionBuilder($entityManagerMock, $authServiceMock);

        /**@var Transaction $transaction */
        $transaction = $service->create()->bind($request)->build();

        $this->assertEquals(TransactionType::INCOME, $transaction->getType());
        $this->assertEquals(10, $transaction->getValue());
        $this->assertEquals($wallet, $transaction->getWallet());
        $this->assertEquals($walletReceiver, $transaction->getWalletReceiver());
        $this->assertEquals($category, $transaction->getCategory());
        $this->assertEquals('Description', $transaction->getDescription());
        $this->assertEquals(1, count($transaction->getLabels()));
    }

    public function test_transaction_builder_bind_update_request()
    {
        $user = (new User())->setId(1);
        $category = (new Category())->setId(1)->setUserId($user->getId())->setName('Category');
        $wallet = (new Wallet())->setId(1)->setUserId($user->getId())->setName('Wallet');
        $transaction = (new Transaction())->setValue(10);
        $label = (new Label())->setId(1)->setUserId($user->getId())->setName('Essentials');
        $label2 = (new Label())->setId(2)->setUserId($user->getId())->setName('Essentials2');

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->method('find')
            ->willReturn($transaction);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->method('find')
            ->willReturn($category);

        $walletRepository = $this->createMock(TransactionRepository::class);
        $walletRepository->method('find')
            ->willReturn($wallet);

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository
            ->method('find')
            ->will($this->onConsecutiveCalls($label2, $label));

        $entityManagerMock = $this->createMock(EntityManager::class);

        $entityManagerMock
            ->method('getRepository')
            ->will($this->onConsecutiveCalls($transactionRepository, $categoryRepository, $walletRepository, $labelRepository, $labelRepository));

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock
            ->method('getCurrentUser')
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
        $request->labelIds = [$label2->getId()];

        $service = new TransactionBuilder($entityManagerMock, $authServiceMock);
        $transaction = $service->bind($request)->build();

        $this->assertEquals(TransactionType::INCOME, $transaction->getType());
        $this->assertEquals(10, $transaction->getValue());
        $this->assertEquals($wallet, $transaction->getWallet());
        $this->assertEquals($category, $transaction->getCategory());
        $this->assertEquals('Description', $transaction->getDescription());
        $this->assertEquals(2, $transaction->getLabels()->first()->getId());
    }

    public function test_transaction_builder_bind_update_request_exception()
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

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository->method('find')
            ->willReturn($wallet);

        $entityManagerMock = $this->createMock(EntityManager::class);

        $entityManagerMock
            ->method('getRepository')
            ->will($this->onConsecutiveCalls($transactionRepository, $categoryRepository, $walletRepository));

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

    public function test_transaction_builder_bind_update_request_wallet_possession_exception()
    {
        $user = (new User())->setId(1);
        $category = (new Category())->setId(1)->setUserId($user->getId())->setName('Category');
        $wallet = (new Wallet())->setId(1)->setUserId($user->getId())->setName('Wallet');
        $walletReceiver = (new Wallet())->setId(2)->setUserId(3)->setName('Cash');
        $transaction = (new Transaction())->setValue(10);

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->method('find')
            ->willReturn($transaction);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->method('find')
            ->willReturn($category);

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository
            ->method('find')
            ->will($this->onConsecutiveCalls($wallet, $walletReceiver));

        $entityManagerMock = $this->createMock(EntityManager::class);

        $entityManagerMock
            ->method('getRepository')
            ->will($this->onConsecutiveCalls($transactionRepository, $categoryRepository, $walletRepository, $walletRepository));

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
        $request->walletReceiverId = $walletReceiver->getId();
        $request->categoryId = $category->getId();

        $service = new TransactionBuilder($entityManagerMock, $authServiceMock);

        $this->expectException(UnauthorizedOperationException::class);

        $service->bind($request)->build();
    }

    public function test_transaction_builder_bind_update_request_required_category_exception()
    {
        $user = (new User())->setId(1);
        $wallet = (new Wallet())->setId(1)->setUserId($user->getId())->setName('Wallet');
        $transaction = (new Transaction())->setValue(10);

        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionRepository->method('find')
            ->willReturn($transaction);

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository
            ->method('find')
            ->with($wallet->getId())
            ->willReturn($wallet);

        $entityManagerMock = $this->createMock(EntityManager::class);

        $entityManagerMock
            ->method('getRepository')
            ->will($this->onConsecutiveCalls($transactionRepository, $walletRepository));

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

        $service = new TransactionBuilder($entityManagerMock, $authServiceMock);

        $this->expectException(RequiredEntityException::class);

        $service->bind($request)->build();
    }
}
