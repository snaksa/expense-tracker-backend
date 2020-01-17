<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\UserBuilder;
use App\Entity\User;
use App\Exception\GraphQLException;
use App\GraphQL\Input\UserLoginRequest;
use App\GraphQL\Input\UserRegisterRequest;
use App\Repository\UserRepository;
use App\Services\AuthorizationService;
use App\Traits\DateUtils;
use Doctrine\ORM\EntityNotFoundException;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    private $authorizationService;

    public function __construct(
        UserRepository $repository,
        UserBuilder $builder,
        AuthorizationService $authorizationService
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @GQL\Query(type="[User]")
     *
     * @return User[]
     */
    public function users(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @GQL\Query(type="User")
     *
     * @return User
     */
    public function me(): User
    {
        return $this->authorizationService->getCurrentUser();
    }

    /**
     * @GQL\Mutation(type="User")
     *
     * @param UserRegisterRequest $input
     *
     * @return User
     * @throws EntityNotFoundException
     */
    public function registerUser(UserRegisterRequest $input): User
    {
        $user = $this->builder
            ->create()
            ->bind($input)
            ->build();

        $this->repository->save($user);

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

        $this->authorizationService->isPasswordValid($user, $input->password);

        $user = $this->builder
            ->setUser($user)
            ->withApiKey()
            ->build();

        $this->repository->save($user);

        return $user->getApiKey();
    }
}