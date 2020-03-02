<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\CategoryBuilder;
use App\Entity\Category;
use App\Exception\GraphQLException;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Category\CategoryCreateRequest;
use App\GraphQL\Input\Category\CategoryDeleteRequest;
use App\GraphQL\Input\Category\CategoryUpdateRequest;
use App\Repository\CategoryRepository;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

    /**
     * @var AuthorizationService
     */
    private $authService;

    public function __construct(
        CategoryRepository $repository,
        CategoryBuilder $builder,
        AuthorizationService $authService
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->authService = $authService;
    }

    /**
     * @GQL\Query(type="[Category]")
     *
     * @return Category[]
     */
    public function categories(): array
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $this->repository->findUserCategories($this->authService->getCurrentUser());
    }

    /**
     * @GQL\Query(type="Category")
     *
     * @param int $id
     *
     * @return Category
     * @throws NonUniqueResultException
     */
    public function category(int $id): Category
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /**@var Category $category */
        $category = $this->repository->findOneById($id);

        if (!$category) {
            throw GraphQLException::fromString('Category not found!');
        }

        if ($category->getUserId() && $category->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $category;
    }

    /**
     * @GQL\Mutation(type="Category")
     *
     * @param CategoryCreateRequest $input
     *
     * @return Category
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createCategory(CategoryCreateRequest $input): Category
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

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
     * @throws EntityNotFoundException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateCategory(CategoryUpdateRequest $input): Category
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        try {
            $category = $this->builder
                ->bind($input)
                ->build();

            $this->repository->save($category);
        } catch (UnauthorizedOperationException $ex) {
            throw GraphQLException::fromString('Unauthorized operation!');
        }

        return $category;
    }

    /**
     * @GQL\Mutation(type="Category")
     *
     * @param CategoryDeleteRequest $input
     *
     * @return Category
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteCategory(CategoryDeleteRequest $input): Category
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /**@var Category $category */
        $category = $this->repository->findOneById($input->id);

        if ($category->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized operation!');
        }

        $clone = clone $category;

        $this->repository->remove($category);

        return $clone;
    }
}
