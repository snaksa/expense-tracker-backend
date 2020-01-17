<?php

declare (strict_types=1);

namespace App\Builder;

use App\Entity\Wallet;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\WalletRequest;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class WalletBuilder extends BaseBuilder
{
    /**
     * @var Wallet
     */
    private $wallet;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    public function __construct(EntityManagerInterface $entityManager, AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;

        parent::__construct($entityManager);
    }

    public function create(): self
    {
        $this->wallet = new Wallet();
        $this->wallet->setUserId($this->authorizationService->getCurrentUser()->getId());
        $this->wallet->setUser($this->authorizationService->getCurrentUser());

        return $this;
    }

    /**
     * @param WalletRequest $input
     * @return WalletBuilder
     * @throws EntityNotFoundException
     */
    public function bind(WalletRequest $input): self
    {
        if ($input->id !== null) {
            $this->setWallet($this->findEntity($input->id, Wallet::class));
        }

        if ($this->authorizationService->getCurrentUser()->getId() !== $this->wallet->getUserId()) {
            throw new UnauthorizedOperationException();
        }

        if ($input->name !== null) {
            $this->withName($input->name);
        }

        if ($input->color !== null) {
            $this->withColor($input->color);
        }

        return $this;
    }

    public function setWallet(Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->wallet->setName($name);

        return $this;
    }

    public function withColor(string $color): self
    {
        $this->wallet->setColor($color);

        return $this;
    }

    public function build(): Wallet
    {
        return $this->wallet;
    }
}
