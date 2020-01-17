<?php

declare (strict_types=1);

namespace App\Builder;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\GraphQL\Input\TransactionRequest;
use Doctrine\ORM\EntityNotFoundException;

class TransactionBuilder extends BaseBuilder
{
    /**
     * @var Transaction
     */
    private $transaction;

    public function create(): self
    {
        $this->transaction = new Transaction();

        return $this;
    }

    /**
     * @param TransactionRequest $input
     * @return TransactionBuilder
     * @throws EntityNotFoundException
     */
    public function bind(TransactionRequest $input): self
    {
        if ($input->id !== null) {
            $this->setTransaction($this->findEntity($input->id, Transaction::class));
        }

        if ($input->description !== null) {
            $this->withDescription($input->description);
        }

        if ($input->value !== null) {
            $this->withValue($input->value);
        }

        if ($input->type !== null) {
            $this->withType($input->type);
        }

        if ($input->categoryId !== null) {
            $this->withCategory($this->findEntity($input->categoryId, Category::class));
        }

        if ($input->walletId !== null) {
            $this->withWallet($this->findEntity($input->walletId, Wallet::class));
        }

        return $this;
    }

    public function setTransaction(Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->transaction->setDescription($description);

        return $this;
    }

    public function withValue(float $value): self
    {
        $this->transaction->setValue($value);

        return $this;
    }

    public function withType(int $type): self
    {
        $this->transaction->setType($type);

        return $this;
    }

    public function withCategory(Category $category): self
    {
        $this->transaction->setCategory($category);

        return $this;
    }

    public function withWallet(Wallet $wallet): self
    {
        $this->transaction->setWallet($wallet);

        return $this;
    }

    public function build(): Transaction
    {
        return $this->transaction;
    }
}
