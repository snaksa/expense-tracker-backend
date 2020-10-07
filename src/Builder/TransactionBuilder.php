<?php declare (strict_types=1);

namespace App\Builder;

use App\Entity\Category;
use App\Entity\Label;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Exception\RequiredEntityException;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Transaction\TransactionRequest;
use App\GraphQL\Types\TransactionType;
use App\Services\AuthorizationService;
use App\Traits\DateUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class TransactionBuilder extends BaseBuilder
{
    use DateUtils;

    private Transaction $transaction;
    private AuthorizationService $authorizationService;

    public function __construct(EntityManagerInterface $entityManager, AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;

        parent::__construct($entityManager);
    }

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

        if ($input->date !== null) {
            $date = $this->createFromFormat($input->date, $this->dateTimeFormat);
            if ($date) {
                $this->withDate($date);
            }
        }

        if ($input->description !== null) {
            $this->withDescription($input->description);
        }

        if ($input->value !== null) {
            $this->withValue($input->value);
        }

        if ($input->type !== null) {
            $this->withType($input->type->value);
        }

        if ($input->categoryId !== null) {
            $this->withCategory($this->findEntity($input->categoryId, Category::class));
        }

        if ($input->walletId !== null) {
            $this->withWallet($this->findEntity($input->walletId, Wallet::class));
        }

        if ($input->walletReceiverId !== null) {
            $walletReceiver = $this->findEntity($input->walletReceiverId, Wallet::class);
            if ($walletReceiver) {
                $this->withWalletReceiver($walletReceiver);

                $userId = $walletReceiver->getUserId();
                if ($userId !== $this->authorizationService->getCurrentUser()->getId()) {
                    throw new UnauthorizedOperationException();
                }
            }
        }

        if ($input->labelIds !== null) {
            $newIds = $input->labelIds;
            $oldIds = $this->transaction->getLabels()->map(function (Label $label) {
                return $label->getId();
            })->toArray();

            $toRemoveIds = array_diff($oldIds, $newIds);

            foreach ($newIds as $labelId) {
                if (!in_array($labelId, $oldIds)) {
                    $label = $this->findEntity($labelId, Label::class);
                    $this->addLabel($label);
                }
            }

            foreach ($toRemoveIds as $labelId) {
                $label = $this->findEntity($labelId, Label::class);
                if ($label) {
                    $this->removeLabel($label);
                }
            }
        }

        $wallet = $this->transaction->getWallet();
        if ($wallet && $wallet->getUserId() !== $this->authorizationService->getCurrentUser()->getId()) {
            throw new UnauthorizedOperationException();
        }

        if ($this->transaction->getType() !== TransactionType::TRANSFER && !$this->transaction->getCategory()) {
            throw new RequiredEntityException('Category is required for EXPENSE/INCOME records');
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

    public function withDate(\DateTime $date): self
    {
        $this->transaction->setDate($date);

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

    public function withWalletReceiver(Wallet $wallet): self
    {
        $this->transaction->setWalletReceiver($wallet);

        return $this;
    }

    public function addLabel(Label $label): self
    {
        $this->transaction->addLabel($label);

        return $this;
    }

    public function removeLabel(Label $label): self
    {
        $this->transaction->removeLabel($label);

        return $this;
    }

    public function build(): Transaction
    {
        return $this->transaction;
    }
}
