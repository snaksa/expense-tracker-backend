<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\UserBuilder;
use App\Entity\User;
use App\Exception\GraphQLException;
use App\Exception\InvalidPasswordException;
use App\Exception\UserAlreadyExistsException;
use App\GraphQL\Input\User\UserLoginRequest;
use App\GraphQL\Input\User\UserRegisterRequest;
use App\GraphQL\Input\User\UserUpdateRequest;
use App\Repository\UserRepository;
use App\Services\AuthorizationService;
use App\Traits\DateUtils;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Provider
 *
 * @package App\GraphQL\Provider
 */
class UserProvider
{
    use DateUtils;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var UserBuilder
     */
    private $builder;

    /**
     * @var AuthorizationService
     */
    private $authService;

    public function __construct(
        UserRepository $repository,
        UserBuilder $builder,
        AuthorizationService $authorizationService
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->authService = $authorizationService;
    }

    /**
     * @GQL\Query(type="User")
     *
     * @return User
     * @throws GraphQLException
     */
    public function me(): User
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        return $this->authService->getCurrentUser();
    }

    /**
     * @GQL\Mutation(type="User")
     *
     * @param UserRegisterRequest $input
     *
     * @return User
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function registerUser(UserRegisterRequest $input): User
    {
        try {
            $user = $this->builder
                ->create()
                ->bind($input)
                ->build();

            $this->repository->save($user);
        } catch (UserAlreadyExistsException $ex) {
            throw GraphQLException::fromString($ex->getMessage());
        }

        return $user;
    }

    /**
     * @GQL\Mutation(type="String")
     *
     * @param UserLoginRequest $input
     *
     * @return string
     * @throws \Exception
     */
    public function loginUser(UserLoginRequest $input): string
    {
        $user = $this->repository->findOneBy([
            'email' => $input->email
        ]);

        if (!$user) {
            throw GraphQLException::fromString('Wrong credentials!');
        }

        try {
            $this->authService->isPasswordValid($user, $input->password);
        } catch (InvalidPasswordException $ex) {
            throw GraphQLException::fromString('Wrong credentials!');
        }

        return $this->builder
            ->setUser($user)
            ->withApiKey();
    }

    /**
     * @GQL\Mutation(type="User")
     *
     * @param UserUpdateRequest $input
     *
     * @return User
     * @throws \Exception
     */
    public function updateUser(UserUpdateRequest $input): User
    {
        $user = $this->authService->getCurrentUser();

        $user = $this->builder
            ->setUser($user)
            ->bind($input)
            ->build();

        $this->repository->save($user);

        return $user;
    }
}
