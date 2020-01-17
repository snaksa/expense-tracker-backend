<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\TransactionBuilder;
use App\Entity\Transaction;
use App\Exception\GraphQLException;
use App\GraphQL\Input\Transaction\TransactionCreateRequest;
use App\GraphQL\Input\Transaction\TransactionDeleteRequest;
use App\GraphQL\Input\Transaction\TransactionUpdateRequest;
use App\Repository\TransactionRepository;
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
     * @var TransactionBuilder
     */
    private $builder;

    public function __construct(TransactionRepository $repository, TransactionBuilder $builder)
    {
        $this->repository = $repository;
        $this->builder = $builder;
    }

    /**
     * @GQL\Query(type="[Transaction]")
     *
     * @param int $walletId
     * @return Transaction[]
     */
    public function transactions(int $walletId): array
    {
        return $this->repository->findBy(['wallet_id' => $walletId]);
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
        return $this->repository->findOneById($id);
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
        $transaction = $this->builder
            ->bind($input)
            ->build();

        $this->repository->save($transaction);

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
        $transaction = $this->repository->findOneById($input->id);

        $clone = clone $transaction;

        $this->repository->remove($transaction);

        return $clone;
    }
}
