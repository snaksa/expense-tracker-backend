<?php declare (strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 * @ORM\Table(name="transaction_record")
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction
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
    private string $description;

    /**
     * @ORM\Column(type="float", nullable=false)
     * @GQL\Field
     */
    private float $value;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @GQL\Field(type="TransactionType!")
     */
    private int $type;

    /**     *
     * @ORM\Column(type="datetime", nullable=false)
     * @GQL\Field(type="DateTime")
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $wallet_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet", inversedBy="transactions")
     * @ORM\JoinColumn(name="wallet_id", referencedColumnName="id", nullable=false)
     * @GQL\Field(type="Wallet")
     */
    private Wallet $wallet;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $wallet_receiver_id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet", inversedBy="transactions")
     * @ORM\JoinColumn(name="wallet_receiver_id", referencedColumnName="id", nullable=true)
     * @GQL\Field(type="Wallet", name="walletReceiver", resolve="value.getWalletReceiver()")
     */
    private ?Wallet $wallet_receiver;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $category_id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="transactions")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true)
     * @GQL\Field(type="Category", resolve="@=service('category_loader').load(value.getCategoryId())")
     */
    private ?Category $category = null;

    /**
     * @ORM\ManyToMany(targetEntity="Label", inversedBy="transactions")
     * @GQL\Field(type="[Label]")
     */
    private Collection $labels;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->labels = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getWalletId(): int
    {
        return $this->wallet_id;
    }

    public function setWalletId(int $wallet_id): self
    {
        $this->wallet_id = $wallet_id;

        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->category_id;
    }

    public function setCategoryId(int $category_id): self
    {
        $this->category_id = $category_id;

        return $this;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function setWallet(Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getWalletReceiverId(): ?int
    {
        return $this->wallet_receiver_id;
    }

    public function setWalletReceiverId(int $wallet_receiver_id): self
    {
        $this->wallet_receiver_id = $wallet_receiver_id;

        return $this;
    }

    public function getWalletReceiver(): ?Wallet
    {
        return $this->wallet_receiver ?? null;
    }

    public function setWalletReceiver(?Wallet $wallet_receiver): self
    {
        $this->wallet_receiver = $wallet_receiver;

        return $this;
    }

    /**
     * @return Collection|Label[]
     */
    public function getLabels(): Collection
    {
        return $this->labels;
    }

    public function addLabel(Label $label): self
    {
        if (!$this->labels->contains($label)) {
            $this->labels[] = $label;
        }

        return $this;
    }

    public function removeLabel(Label $label): self
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }
}
