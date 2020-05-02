<?php

namespace App\Tests\Unit\Builder;

use App\Builder\UserBuilder;
use App\Entity\User;
use App\Exception\PasswordConfirmationException;
use App\Exception\UserAlreadyExistsException;
use App\GraphQL\Input\User\UserRegisterRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserBuilderTest extends TestCase
{
    public function test_user_builder_create()
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);
        $jwtManagerMock = $this->createMock(JWTManager::class);
        $service = new UserBuilder($entityManagerMock, $userRepositoryMock, $encoderMock, $jwtManagerMock);

        $user = $service->create()->build();

        $this->assertInstanceOf(User::class, $user);
    }

    public function test_user_builder_bind_create_request()
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->method('findOneBy')->willReturn(null);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);
        $encoderMock->method('encodePassword')
            ->willReturn('hashed-password');

        $request = new UserRegisterRequest();
        $request->email = 'test@gmail.com';
        $request->password = '123';
        $request->confirmPassword = '123';

        $jwtManagerMock = $this->createMock(JWTManager::class);
        $service = new UserBuilder($entityManagerMock, $userRepositoryMock, $encoderMock, $jwtManagerMock);

        $user = $service->create()->bind($request)->build();

        $this->assertEquals('test@gmail.com', $user->getEmail());
        $this->assertEquals('hashed-password', $user->getPassword());
    }

    public function test_user_builder_bind_create_request_exception()
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->method('findOneBy')->willReturn(null);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);
        $encoderMock->method('encodePassword')
            ->willReturn('hashed-password');

        $request = new UserRegisterRequest();
        $request->email = 'test@gmail.com';
        $request->password = '123';
        $request->confirmPassword = '1234';

        $jwtManagerMock = $this->createMock(JWTManager::class);
        $service = new UserBuilder($entityManagerMock, $userRepositoryMock, $encoderMock, $jwtManagerMock);

        $this->expectException(PasswordConfirmationException::class);

        $service->create()->bind($request)->build();
    }

    public function test_user_builder_bind_create_request_user_exists_exception()
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->method('findOneBy')->willReturn((new User())->setId(0));
        $encoderMock = $this->createMock(UserPasswordEncoder::class);
        $encoderMock->method('encodePassword')
            ->willReturn('hashed-password');

        $request = new UserRegisterRequest();
        $request->email = 'test@gmail.com';
        $request->password = '1234';
        $request->confirmPassword = '1234';

        $jwtManagerMock = $this->createMock(JWTManager::class);
        $service = new UserBuilder($entityManagerMock, $userRepositoryMock, $encoderMock, $jwtManagerMock);

        $this->expectException(UserAlreadyExistsException::class);

        $service->create()->bind($request)->build();
    }

    public function test_user_builder_with_api_key()
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->method('findOneBy')->willReturn(null);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);

        $jwtManagerMock = $this->createMock(JWTManager::class);
        $jwtManagerMock->method('setUserIdentityField')->willReturn(null);
        $jwtManagerMock->method('create')->willReturn('123');

        $service = new UserBuilder($entityManagerMock, $userRepositoryMock, $encoderMock, $jwtManagerMock);

        $apiKey = $service->create()->withApiKey();

        $this->assertIsString($apiKey);
    }
}
