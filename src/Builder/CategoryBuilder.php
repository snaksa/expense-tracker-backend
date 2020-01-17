<?php declare (strict_types=1);

namespace App\Builder;

use App\Entity\Category;
use App\GraphQL\Input\Category\CategoryRequest;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class CategoryBuilder extends BaseBuilder
{
    /**
     * @var Category
     */
    private $category;

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
        $this->category = new Category();
        $this->category->setUserId($this->authorizationService->getCurrentUser()->getId());
        $this->category->setUser($this->authorizationService->getCurrentUser());

        return $this;
    }

    /**
     * @param CategoryRequest $input
     * @return CategoryBuilder
     * @throws EntityNotFoundException
     */
    public function bind(CategoryRequest $input): self
    {
        if ($input->id !== null) {
            $this->setCategory($this->findEntity($input->id, Category::class));
        }

        if ($input->name !== null) {
            $this->withName($input->name);
        }

        if ($input->color !== null) {
            $this->withColor($input->color);
        }

        if ($input->icon !== null) {
            $this->withIcon($input->icon);
        }

        return $this;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->category->setName($name);

        return $this;
    }

    public function withColor(string $color): self
    {
        $this->category->setColor($color);

        return $this;
    }

    public function withIcon(int $icon): self
    {
        $this->category->setIcon($icon);

        return $this;
    }

    public function build(): Category
    {
        return $this->category;
    }
}
