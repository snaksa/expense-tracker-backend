<?php declare (strict_types=1);

namespace App\Builder;

use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\Label;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Budget\BudgetRequest;
use App\Services\AuthorizationService;
use App\Traits\DateUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class BudgetBuilder extends BaseBuilder
{
    use DateUtils;

    /**
     * @var Budget
     */
    private $budget;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    public function __construct(EntityManagerInterface $entityManager, AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;

        parent::__construct($entityManager);
    }

    public function create(): self
    {
        $this->budget = new Budget();
        $userId = $this->authorizationService->getCurrentUser()->getId();
        if ($userId) {
            $this->budget->setUserId($userId);
            $this->budget->setUser($this->authorizationService->getCurrentUser());
        }

        return $this;
    }

    /**
     * @param BudgetRequest $input
     * @return BudgetBuilder
     * @throws EntityNotFoundException
     */
    public function bind(BudgetRequest $input): self
    {
        if ($input->id !== null) {
            $this->setBudget($this->findEntity($input->id, Budget::class));
        }

        if ($this->budget->getUserId() !== $this->authorizationService->getCurrentUser()->getId()) {
            throw new UnauthorizedOperationException();
        }

        if ($input->name !== null) {
            $this->withName($input->name);
        }

        if ($input->value !== null) {
            $this->withValue($input->value);
        }

        if ($input->startDate !== null) {
            $date = $this->createFromFormat($input->startDate, $this->dateTimeFormat);
            if ($date) {
                $this->withStartDate($date);
            }
        }

        if ($input->endDate !== null) {
            $date = $this->createFromFormat($input->endDate, $this->dateTimeFormat);
            if ($date) {
                $this->withEndDate($date);
            }
        }

        if ($input->categoryIds !== null) {
            $newIds = $input->categoryIds;
            $oldIds = $this->budget->getCategories()->map(function (Category $category) {
                return $category->getId();
            })->toArray();

            $toRemoveIds = array_diff($oldIds, $newIds);

            foreach ($newIds as $categoryId) {
                if (!in_array($categoryId, $oldIds)) {
                    $category = $this->findEntity($categoryId, Category::class);
                    $this->addCategory($category);
                }
            }

            foreach ($toRemoveIds as $categoryId) {
                $category = $this->findEntity($categoryId, Category::class);
                if ($category) {
                    $this->removeCategory($category);
                }
            }
        }

        if ($input->labelIds !== null) {
            $newIds = $input->labelIds;
            $oldIds = $this->budget->getLabels()->map(function (Label $label) {
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

        return $this;
    }

    public function setBudget(Budget $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->budget->setName($name);

        return $this;
    }

    public function withValue(float $value): self
    {
        $this->budget->setValue($value);

        return $this;
    }

    public function withStartDate(\DateTime $startDate): self
    {
        $this->budget->setStartDate($startDate);

        return $this;
    }

    public function withEndDate(\DateTime $end): self
    {
        $this->budget->setEndDate($end);

        return $this;
    }

    public function addLabel(Label $label): self
    {
        $this->budget->addLabel($label);

        return $this;
    }

    public function removeLabel(Label $label): self
    {
        $this->budget->removeLabel($label);

        return $this;
    }

    public function addCategory(Category $category): self
    {
        $this->budget->addCategory($category);

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->budget->removeCategory($category);

        return $this;
    }

    public function build(): Budget
    {
        return $this->budget;
    }
}
