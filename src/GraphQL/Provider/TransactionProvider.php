<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\TransactionBuilder;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Exception\GraphQLException;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Transaction\TransactionCreateRequest;
use App\GraphQL\Input\Transaction\TransactionDeleteRequest;
use App\GraphQL\Input\Transaction\TransactionRecordsRequest;
use App\GraphQL\Input\Transaction\TransactionUpdateRequest;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityNotFoundException;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Provider
 *
 * @package App\GraphQL\Provider
 */
class TransactionProvider
{
    /**
     * @var TransactionRepository
     */
    private $repository;

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * @var TransactionBuilder
     */
    private $builder;

    /**
     * @var AuthorizationService
     */
    private $authService;

    public function __construct(
        TransactionRepository $repository,
        WalletRepository $walletRepository,
        TransactionBuilder $builder,
        AuthorizationService $authService
    ) {
        $this->repository = $repository;
        $this->walletRepository = $walletRepository;
        $this->builder = $builder;
        $this->authService = $authService;
    }

    /**
     * @GQL\Query(type="[Transaction]")
     *
     * @param TransactionRecordsRequest $input
     * @return Transaction[]
     */
    public function transactions(TransactionRecordsRequest $input): array
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /**@var Wallet[] $wallets */
        $wallets = $this->walletRepository->findByIds($input->walletIds);

        foreach ($wallets as $wallet) {
            if ($wallet->getUserId() !== $this->authService->getCurrentUser()->getId()) {
                throw GraphQLException::fromString('Unauthorized access!');
            }
        }

        return $this->repository->findByWalletIds($input->walletIds);
    }

    /**
     * @GQL\Query(type="Transaction")
     *
     * @param int $id
     *
     * @return Transaction
     */
    public function transaction(int $id): Transaction
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /**@var Transaction $transaction */
        $transaction = $this->repository->findOneById($id);

        if (!$transaction) {
            throw GraphQLException::fromString('Transaction not found!');
        }

        if ($transaction->getWallet()->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $transaction;
    }

    /**
     * @GQL\Mutation(type="Transaction")
     *
     * @param TransactionCreateRequest $input
     *
     * @return Transaction
     * @throws EntityNotFoundException
     */
    public function createTransaction(TransactionCreateRequest $input): Transaction
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        $transaction = $this->builder
            ->create()
            ->bind($input)
            ->build();

        $this->repository->save($transaction);

        return $transaction;
    }

    /**
     * @GQL\Mutation(type="Transaction")
     *
     * @param TransactionUpdateRequest $input
     *
     * @return Transaction
     * @throws EntityNotFoundException
     */
    public function updateTransaction(TransactionUpdateRequest $input): Transaction
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        try {
            $transaction = $this->builder
                ->bind($input)
                ->build();

            $this->repository->save($transaction);
        } catch (UnauthorizedOperationException $ex) {
            throw GraphQLException::fromString('Unauthorized operation!');
        }

        return $transaction;
    }

    /**
     * @GQL\Mutation(type="Transaction")
     *
     * @param TransactionDeleteRequest $input
     *
     * @return Transaction
     */
    public function deleteTransaction(TransactionDeleteRequest $input): Transaction
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /**@var Transaction $transaction */
        $transaction = $this->repository->findOneById($input->id);

        if ($transaction->getWallet()->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        $clone = clone $transaction;

        $this->repository->remove($transaction);

        return $clone;
    }
}
