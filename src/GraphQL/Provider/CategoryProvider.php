<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\CategoryBuilder;
use App\Entity\Category;
use App\GraphQL\Input\CategoryCreateRequest;
use App\GraphQL\Input\CategoryUpdateRequest;
use App\Repository\CategoryRepository;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Provider
 *
 * @package App\GraphQL\Provider
 */
class CategoryProvider
{
    /**
     * @var CategoryRepository
     */
    private $repository;

    /**
     * @var CategoryBuilder
     */
    private $builder;

    public function __construct(CategoryRepository $repository, CategoryBuilder $builder)
    {
        $this->repository = $repository;
        $this->builder = $builder;
    }

    /**
     * @GQL\Query(type="[Category]")
     *
     * @return Category[]
     */
    public function categories(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @GQL\Query(type="Category")
     *
     * @param int $id
     *
     * @return Category
     */
    public function category(int $id): Category
    {
        return $this->repository->findOneById($id);
    }

    /**
     * @GQL\Mutation(type="Category")
     *
     * @param CategoryCreateRequest $input
     *
     * @return Category
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function createCategory(CategoryCreateRequest $input): Category
    {
        $category = $this->builder
            ->create()
            ->bind($input)
            ->build();

        $this->repository->save($category);

        return $category;
    }

    /**
     * @GQL\Mutation(type="Category")
     *
     * @param CategoryUpdateRequest $input
     *
     * @return Category
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function updateCategory(CategoryUpdateRequest $input): Category
    {
        $category = $this->builder
            ->bind($input)
            ->build();

        $this->repository->save($category);

        return $category;
    }
}
