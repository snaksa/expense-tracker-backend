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

    public function update(Transaction $transaction)
    {
        $wallet = $transaction->getWallet();

        $totalAmount = 0;
        foreach ($wallet->getTransactions() as $trans) {
            $totalAmount += ($trans->getType() === TransactionType::EXPENSE ? -1 : 1) * $trans->getValue();
        }

        $wallet->setAmount($totalAmount);
        $this->walletRepository->save($wallet);
    }
}
