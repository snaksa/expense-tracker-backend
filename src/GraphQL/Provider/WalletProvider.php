<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\WalletBuilder;
use App\Entity\Wallet;
use App\Exception\GraphQLException;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Wallet\WalletCreateRequest;
use App\GraphQL\Input\Wallet\WalletDeleteRequest;
use App\GraphQL\Input\Wallet\WalletUpdateRequest;
use App\Repository\WalletRepository;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Provider
 *
 * @package App\GraphQL\Provider
 */
class WalletProvider
{
    /**
     * @var WalletRepository
     */
    private $repository;

    /**
     * @var WalletBuilder
     */
    private $builder;

    /**
     * @var AuthorizationService
     */
    private $authService;

    public function __construct(
        WalletRepository $repository,
        WalletBuilder $builder,
        AuthorizationService $authService
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->authService = $authService;
    }

    /**
     * @GQL\Query(type="[Wallet]")
     *
     * @return Wallet[]
     */
    public function wallets(): array
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $this->repository->findBy(['user_id' => $this->authService->getCurrentUser()->getId()]);
    }

    /**
     * @GQL\Query(type="Wallet")
     *
     * @param int $id
     *
     * @return Wallet
     * @throws NonUniqueResultException
     */
    public function wallet(int $id): Wallet
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        $wallet = $this->repository->findOneById($id);

        if (!$wallet) {
            throw GraphQLException::fromString('Wallet not found!');
        }

        if ($wallet->getUserId() && $wallet->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $wallet;
    }

    /**
     * @GQL\Mutation(type="Wallet")
     *
     * @param WalletCreateRequest $input
     *
     * @return Wallet
     * @throws EntityNotFoundException
     */
    public function createWallet(WalletCreateRequest $input): Wallet
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        $wallet = $this->builder
            ->create()
            ->bind($input)
            ->build();

        $this->repository->save($wallet);

        return $wallet;
    }

    /**
     * @GQL\Mutation(type="Wallet")
     *
     * @param WalletUpdateRequest $input
     *
     * @return Wallet
     * @throws EntityNotFoundException
     */
    public function updateWallet(WalletUpdateRequest $input): Wallet
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        try {
            $wallet = $this->builder
                ->bind($input)
                ->build();

            $this->repository->save($wallet);
        } catch (UnauthorizedOperationException $ex) {
            throw GraphQLException::fromString('Unauthorized operation!');
        }

        return $wallet;
    }

    /**
     * @GQL\Mutation(type="Wallet")
     *
     * @param WalletDeleteRequest $input
     *
     * @return Wallet
     * @throws NonUniqueResultException
     */
    public function deleteWallet(WalletDeleteRequest $input): Wallet
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /**@var Wallet $wallet */
        $wallet = $this->repository->findOneById($input->id);

        if ($wallet->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized operation!');
        }

        $clone = clone $wallet;

        $this->repository->remove($wallet);

        return $clone;
    }
}
