<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Builder\UserBuilder;
use App\Entity\User;
use App\Exception\Cognito\CognitoException;
use App\Exception\Cognito\UsernameExistsException;
use App\Exception\GraphQLException;
use App\Exception\PasswordConfirmationException;
use App\GraphQL\Input\User\UserChangePasswordRequest;
use App\GraphQL\Input\User\UserForgotPasswordConfirmationRequest;
use App\GraphQL\Input\User\UserForgotPasswordRequest;
use App\GraphQL\Input\User\UserLoginRequest;
use App\GraphQL\Input\User\UserRegisterRequest;
use App\GraphQL\Input\User\UserRegistrationConfirmationRequest;
use App\GraphQL\Input\User\UserUpdateRequest;
use App\GraphQL\Types\AuthResponse;
use App\Repository\UserRepository;
use App\Services\AuthorizationService;
use App\Services\UserService;
use App\Traits\DateUtils;
use Doctrine\ORM\EntityNotFoundException;
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

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        UserRepository $repository,
        UserBuilder $builder,
        AuthorizationService $authorizationService,
        UserService $userService
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->authService = $authorizationService;
        $this->userService = $userService;
    }

//    /**
//     * @GQL\Query(type="[User]")
//     *
//     * @return User[]
//     */
//    public function users(): array
//    {
//        return $this->repository->findAll();
//    }

    /**
     * @GQL\Query(type="User")
     *
     * @return User
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
     * @return User
     */
    public function registerUser(UserRegisterRequest $input): User
    {
        $auth = null;
        try {
            $user = $this->builder
                ->create()
                ->bind($input)
                ->build();

            // Check if user already exists
            $existingUser = $this->repository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                throw UsernameExistsException::fromString(
                    'User with this email already exists',
                    UsernameExistsException::class
                );
            }

            // Create user in Cognito
            $externalId = $this->userService->createUser($user->getEmail(), $input->password);
            $user->setExternalId($externalId);

            $this->repository->save($user);

            return $user;
        } catch (EntityNotFoundException|PasswordConfirmationException|CognitoException $ex) {
            throw GraphQLException::fromString($ex->getMessage());
        }
    }

    /**
     * @GQL\Mutation(type="Boolean")
     *
     * @param UserRegistrationConfirmationRequest $input
     * @return bool
     */
    public function confirmRegistration(UserRegistrationConfirmationRequest $input): bool
    {
        try {
            $this->userService
                ->confirmRegistration($input->email, $input->code);

            return true;
        } catch (CognitoException $ex) {
            throw GraphQLException::fromString($ex->getMessage());
        }
    }

    /**
     * @GQL\Mutation(type="AuthResponse")
     *
     * @param UserLoginRequest $input
     * @return AuthResponse
     */
    public function loginUser(UserLoginRequest $input): AuthResponse
    {
        $auth = null;
        try {
            $auth = $this->userService->loginUser($input->email, $input->password);
        } catch (CognitoException $ex) {
            throw GraphQLException::fromString($ex->getMessage());
        }

        return $auth;
    }

    /**
     * @GQL\Mutation(type="Boolean")
     *
     * @param UserChangePasswordRequest $input
     * @return bool
     */
    public function changePassword(UserChangePasswordRequest $input): bool
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        try {
            $token = $this->authService->getCurrentUser()->getApiKey();
            $this->userService
                ->changePassword($input->oldPassword, $input->newPassword, $token);

            return true;
        } catch (CognitoException $ex) {
            throw GraphQLException::fromString($ex->getMessage());
        }
    }

    /**
     * @GQL\Mutation(type="Boolean")
     *
     * @param UserForgotPasswordRequest $input
     * @return bool
     */
    public function forgotPassword(UserForgotPasswordRequest $input): bool
    {
        $auth = null;
        try {
            $this->userService->forgotPassword($input->email);

            return true;
        } catch (CognitoException $ex) {
            throw GraphQLException::fromString($ex->getMessage());
        }
    }

    /**
     * @GQL\Mutation(type="Boolean")
     *
     * @param UserForgotPasswordConfirmationRequest $input
     *
     * @return bool
     */
    public function forgotPasswordConfirmation(UserForgotPasswordConfirmationRequest $input): bool
    {
        $auth = null;
        try {
            $this->userService
                ->confirmForgotPassword($input->email, $input->password, $input->code);

            return true;
        } catch (CognitoException $ex) {
            throw GraphQLException::fromString($ex->getMessage());
        }
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
