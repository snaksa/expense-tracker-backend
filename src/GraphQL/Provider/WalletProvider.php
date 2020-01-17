<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\WalletBuilder;
use App\Entity\Wallet;
use App\GraphQL\Input\Wallet\WalletCreateRequest;
use App\GraphQL\Input\Wallet\WalletDeleteRequest;
use App\GraphQL\Input\Wallet\WalletUpdateRequest;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityNotFoundException;
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

    public function __construct(WalletRepository $repository, WalletBuilder $builder)
    {
        $this->repository = $repository;
        $this->builder = $builder;
    }

    /**
     * @GQL\Query(type="[Wallet]")
     *
     * @return Wallet[]
     */
    public function wallets(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @GQL\Query(type="Wallet")
     *
     * @param int $id
     *
     * @return Wallet
     */
    public function wallet(int $id): Wallet
    {
        return $this->repository->findOneById($id);
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
        $wallet = $this->builder
            ->bind($input)
            ->build();

        $this->repository->save($wallet);

        return $wallet;
    }

    /**
     * @GQL\Mutation(type="Wallet")
     *
     * @param WalletDeleteRequest $input
     *
     * @return Wallet
     */
    public function deleteWallet(WalletDeleteRequest $input): Wallet
    {
        $wallet = $this->repository->findOneById($input->id);

        $clone = clone $wallet;

        $this->repository->remove($wallet);

        return $clone;
    }
}
