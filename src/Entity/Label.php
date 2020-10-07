<?php declare (strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 * @ORM\Table(name="label")
 * @ORM\Entity(repositoryClass="App\Repository\LabelRepository")
 */
class Label
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @GQL\Field
     */
    private ?int $id = null;

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
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="labels")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @GQL\Field(type="User")
     */
    private User $user;
    
    /**
     * @ORM\ManyToMany(targetEntity="Transaction", mappedBy="labels")
     * @GQL\Field(type="[Transaction]")
     */
    private Collection $transactions;

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

    public function getColor(): ?string
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
