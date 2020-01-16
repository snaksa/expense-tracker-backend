<?php

declare (strict_types=1);

namespace App\Builder;

use App\Entity\Category;
use App\GraphQL\Input\CategoryRequest;

class CategoryBuilder extends BaseBuilder
{
    /**
     * @var Category
     */
    private $category;

    public function create(): self
    {
        $this->category = new Category();

        return $this;
    }

    /**
     * @param CategoryRequest $input
     * @return CategoryBuilder
     * @throws \Doctrine\ORM\EntityNotFoundException
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

    public function withColor(int $color): self
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
