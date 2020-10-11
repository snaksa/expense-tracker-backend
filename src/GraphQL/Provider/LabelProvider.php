<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\LabelBuilder;
use App\Entity\Label;
use App\Exception\GraphQLException;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Label\LabelCreateRequest;
use App\GraphQL\Input\Label\LabelDeleteRequest;
use App\GraphQL\Input\Label\LabelUpdateRequest;
use App\Repository\LabelRepository;
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
class LabelProvider
{
    private LabelRepository $repository;
    private LabelBuilder $builder;
    private AuthorizationService $authService;

    public function __construct(
        LabelRepository $repository,
        LabelBuilder $builder,
        AuthorizationService $authService
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->authService = $authService;
    }

    /**
     * @GQL\Query(type="[Label]")
     *
     * @return Label[]
     */
    public function labels(): array
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $this->repository->findUserLabels($this->authService->getCurrentUser());
    }

    /**
     * @GQL\Query(type="Label")
     *
     * @param int $id
     *
     * @return Label
     * @throws NonUniqueResultException
     */
    public function label(int $id): Label
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /**@var Label $label */
        $label = $this->repository->findOneById($id);

        if (!$label) {
            throw GraphQLException::fromString('Label not found!');
        }

        if ($label->getUserId() && $label->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $label;
    }

    /**
     * @GQL\Mutation(type="Label")
     *
     * @param LabelCreateRequest $input
     *
     * @return Label
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createLabel(LabelCreateRequest $input): Label
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        $label = $this->builder
            ->create()
            ->bind($input)
            ->build();

        $this->repository->save($label);

        return $label;
    }

    /**
     * @GQL\Mutation(type="Label")
     *
     * @param LabelUpdateRequest $input
     *
     * @return Label
     * @throws EntityNotFoundException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateLabel(LabelUpdateRequest $input): Label
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        try {
            $label = $this->builder
                ->bind($input)
                ->build();

            $this->repository->save($label);
        } catch (UnauthorizedOperationException $ex) {
            throw GraphQLException::fromString('Unauthorized operation!');
        }

        return $label;
    }

    /**
     * @GQL\Mutation(type="Label")
     *
     * @param LabelDeleteRequest $input
     *
     * @return Label
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteLabel(LabelDeleteRequest $input): Label
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /**@var Label $label */
        $label = $this->repository->findOneById($input->id);
        if (!$label) {
            throw GraphQLException::fromString("Label with ID {$input->id} not found!");
        }

        if ($label->getUserId() !== $this->authService->getCurrentUser()->getId()) {
            throw GraphQLException::fromString('Unauthorized operation!');
        }

        $clone = clone $label;

        $this->repository->remove($label);

        return $clone;
    }
}
