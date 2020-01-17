<?php

declare (strict_types=1);

namespace App\Builder;

use App\Entity\Wallet;
use App\GraphQL\Input\WalletRequest;
use Doctrine\ORM\EntityNotFoundException;

class WalletBuilder extends BaseBuilder
{
    /**
     * @var Wallet
     */
    private $wallet;

    public function create(): self
    {
        $this->wallet = new Wallet();

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

        // TODO: check if category belongs to the logged user

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
