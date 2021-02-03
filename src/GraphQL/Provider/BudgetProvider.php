<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\BudgetBuilder;
use App\Entity\Budget;
use App\Exception\GraphQLException;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Budget\BudgetCreateRequest;
use App\GraphQL\Input\Budget\BudgetDeleteRequest;
use App\GraphQL\Input\Budget\BudgetUpdateRequest;
use App\Repository\BudgetRepository;
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
class BudgetProvider
{
    /**
     * @var BudgetRepository
     */
    private $repository;

    /**
     * @var BudgetBuilder
     */
    private $builder;

    /**
     * @var AuthorizationService
     */
    private $authService;

    public function __construct(
        BudgetRepository $repository,
        BudgetBuilder $builder,
        AuthorizationService $authService
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->authService = $authService;
    }

    /**
     * @GQL\Query(type="[Budget]")
     *
     * @return Budget[]
     */
    public function budgets(): array
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $this->repository->findUserBudgets($this->authService->getCurrentUser());
    }

    /**
     * @GQL\Query(type="Budget")
     *
     * @param int $id
     *
     * @return Budget
     * @throws NonUniqueResultException
     */
    public function budget(int $id): Budget
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        $budget = $this->repository->findOneById($id);

        if (!$budget) {
            throw GraphQLException::fromString('Budget not found!');
        }

        if ($budget->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $budget;
    }

    /**
     * @GQL\Mutation(type="Budget")
     *
     * @param BudgetCreateRequest $input
     *
     * @return Budget
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createBudget(BudgetCreateRequest $input): Budget
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        $budget = $this->builder
            ->create()
            ->bind($input)
            ->build();

        $this->repository->save($budget);

        return $budget;
    }

    /**
     * @GQL\Mutation(type="Budget")
     *
     * @param BudgetUpdateRequest $input
     *
     * @return Budget
     * @throws EntityNotFoundException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateBudget(BudgetUpdateRequest $input): Budget
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        try {
            $budget = $this->builder
                ->bind($input)
                ->build();

            $this->repository->save($budget);
        } catch (UnauthorizedOperationException $ex) {
            throw GraphQLException::fromString('Unauthorized operation!');
        }

        return $budget;
    }

    /**
     * @GQL\Mutation(type="Budget")
     *
     * @param BudgetDeleteRequest $input
     *
     * @return Budget
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteBudget(BudgetDeleteRequest $input): Budget
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        $budget = $this->repository->findOneById($input->id);
        if (!$budget) {
            throw GraphQLException::fromString("Budget with ID {$input->id} not found!");
        }

        if ($budget->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized operation!');
        }

        $clone = clone $budget;

        $this->repository->remove($budget);

        return $clone;
    }
}
