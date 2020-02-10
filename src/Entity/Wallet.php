<?php declare (strict_types=1);

namespace App\Entity;

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
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @GQL\Field
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @GQL\Field
     */
    private $color;

    /**
     * @ORM\Column(type="float", nullable=false)
     * @GQL\Field
     */
    private $amount = 0;

    /**
     * @ORM\Column(type="float", nullable=false)
     * @GQL\Field
     */
    private $initial_amount = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="wallets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @GQL\Field(type="User")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="wallet", cascade={"persist", "remove"})
     * @GQL\Field(type="[Transaction]")
     */
    private $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setUser(?User $user): self
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
            // set the owning side to null (unless already changed)
            if ($transaction->getWallet() === $this) {
                $transaction->setWallet(null);
            }
        }

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
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
}
