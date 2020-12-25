<?php declare (strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 * @ORM\Table(name="budget")
 * @ORM\Entity(repositoryClass="App\Repository\BudgetRepository")
 */
class Budget
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
     * @ORM\Column(type="float", nullable=false)
     * @GQL\Field
     */
    private float $value;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="budgets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @GQL\Field(type="User")
     */
    private User $user;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @GQL\Field(type="DateTime")
     */
    private \DateTimeInterface $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @GQL\Field(type="DateTime")
     */
    private \DateTimeInterface $endDate;

    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="budgets")
     * @GQL\Field(type="[Category]")
     */
    private Collection $categories;

    /**
     * @ORM\ManyToMany(targetEntity="Label", inversedBy="budgets")
     * @GQL\Field(type="[Label]")
     */
    private Collection $labels;

    public function __construct()
    {
        $this->startDate = new \DateTime();
        $this->endDate = new \DateTime();
        $this->categories = new ArrayCollection();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getUserId(): int
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

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $date): self
    {
        $this->startDate = $date;

        return $this;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $date): self
    {
        $this->endDate = $date;

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

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }
}
