<?php declare (strict_types=1);

namespace App\Entity;

use App\GraphQL\Types\TransactionType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @GQL\Field
     */
    private ?int $id;

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
     * @ORM\Column(type="integer", nullable=true)
     * @GQL\Field
     */
    private ?int $icon;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="categories")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * @GQL\Field(type="User")
     */
    private ?User $user;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Transaction",
     *     mappedBy="category",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @GQL\Field(type="[Transaction]")
     */
    private Collection $transactions;

    /**
     * @GQL\Field(type="Int", resolve="value.getTransactionsCount()")
     */
    private int $transactionsCount;

    /**
     * @GQL\Field(type="Float", resolve="value.getBalance()")
     */
    private float $balance;

    public function getTransactionsCount(): int
    {
        return $this->getTransactions()->count();
    }

    public function getBalance(): float
    {
        $total = 0;
        /**@var Transaction[] $transactions */
        $transactions = $this->getTransactions()->toArray();
        foreach ($transactions as $transaction) {
            $total += ($transaction->getType() === TransactionType::EXPENSE ? -1 : 1) * $transaction->getValue();
        }

        return $total;
    }

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

    public function getName(): ?string
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

    public function getIcon(): ?int
    {
        return $this->icon;
    }

    public function setIcon(?int $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setCategory($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getCategory() === $this) {
                $transaction->setCategory(null);
            }
        }

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): self
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
}
