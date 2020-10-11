<?php declare (strict_types=1);

namespace App\Entity;

use App\GraphQL\Types\TransactionType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 * @ORM\Entity(repositoryClass="App\Repository\WalletRepository")
 */
class Wallet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @GQL\Field
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @GQL\Field
     */
    private string $name;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @GQL\Field
     */
    private string $color;

    /**
     * @ORM\Column(type="float", nullable=false)
     * @GQL\Field
     */
    private float $amount = 0;

    /**
     * @ORM\Column(type="float", nullable=false)
     * @GQL\Field
     */
    private float $initial_amount = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="wallets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @GQL\Field(type="User")
     */
    private User $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="wallet")
     * @GQL\Field(type="[Transaction]")
     * @var Collection<Transaction>
     */
    private Collection $transactions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="wallet_receiver")
     * @GQL\Field(type="[Transaction]")
     * @var Collection<Transaction>
     */
    private Collection $transferInTransactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->transferInTransactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return isset($this->id) ? $this->id : null;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setWallet($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
        }

        return $this;
    }

    public function getAmount(): float
    {
        $total = 0;
        /**@var Transaction[] $transactions */
        $transactions = $this->getTransactions()->toArray();
        foreach ($transactions as $transaction) {
            $total += (
                in_array(
                    $transaction->getType(),
                    [TransactionType::EXPENSE, TransactionType::TRANSFER]
                ) ? -1 : 1) * $transaction->getValue();
        }

        foreach ($this->getTransferInTransactions() as $transaction) {
            $total += $transaction->getValue();
        }

        return $this->getInitialAmount() + $total;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getInitialAmount(): float
    {
        return $this->initial_amount;
    }

    public function setInitialAmount(float $initialAmount): self
    {
        $this->initial_amount = $initialAmount;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransferInTransactions(): Collection
    {
        return $this->transferInTransactions;
    }

    public function addTransferInTransaction(Transaction $transferInTransaction): self
    {
        if (!$this->transferInTransactions->contains($transferInTransaction)) {
            $this->transferInTransactions[] = $transferInTransaction;
            $transferInTransaction->setWalletReceiver($this);
        }

        return $this;
    }

    public function removeTransferInTransaction(Transaction $transferInTransaction): self
    {
        if ($this->transferInTransactions->contains($transferInTransaction)) {
            $this->transferInTransactions->removeElement($transferInTransaction);
            // set the owning side to null (unless already changed)
            if ($transferInTransaction->getWalletReceiver() === $this) {
                $transferInTransaction->setWalletReceiver(null);
            }
        }

        return $this;
    }
}
