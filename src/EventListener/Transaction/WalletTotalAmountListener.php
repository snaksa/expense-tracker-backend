<?php

namespace App\EventListener\Transaction;

use App\Entity\Transaction;
use App\GraphQL\Types\TransactionType;
use App\Repository\WalletRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class WalletTotalAmountListener
{
    /**
     * @var WalletRepository
     */
    private $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function postPersist(Transaction $transaction, LifecycleEventArgs $args)
    {
        $this->update($transaction);
    }

    public function postUpdate(Transaction $transaction, LifecycleEventArgs $args)
    {
        $this->update($transaction);
    }

    public function postRemove(Transaction $transaction, LifecycleEventArgs $args)
    {
        $this->update($transaction);
    }

    public function update(Transaction $transaction)
    {
        $wallet = $transaction->getWallet();

        $amount = 0;
        foreach ($wallet->getTransactions() as $t) {
            $amount += ($t->getType() === TransactionType::EXPENSE ? -1 : 1) * $t->getValue();
        }

        $wallet->setAmount($wallet->getInitialAmount() + $amount);
        $this->walletRepository->save($wallet);
    }
}
