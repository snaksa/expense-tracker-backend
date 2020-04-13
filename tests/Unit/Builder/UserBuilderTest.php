<?php

namespace App\Tests\Unit\Entity;

use App\Builder\UserBuilder;
use App\Entity\User;
use App\Exception\PasswordConfirmationException;
use App\GraphQL\Input\User\UserRegisterRequest;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserBuilderTest extends TestCase
{
    public function test_user_builder_create()
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);
        $jwtManagerMock = $this->createMock(JWTManager::class);
        $service = new UserBuilder($entityManagerMock, $encoderMock, $jwtManagerMock);

        /**@var User $user*/
        $user = $service->create()->build();

        $this->assertInstanceOf(User::class, $user);
    }

    public function test_user_builder_bind_create_request()
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);
        $encoderMock->method('encodePassword')
            ->willReturn('hashed-password');

        $request = new UserRegisterRequest();
        $request->email = 'test@gmail.com';
        $request->password = '123';
        $request->confirmPassword = '123';

        $jwtManagerMock = $this->createMock(JWTManager::class);
        $service = new UserBuilder($entityManagerMock, $encoderMock, $jwtManagerMock);

        /**@var User $user*/
        $user = $service->create()->bind($request)->build();

        $this->assertEquals('test@gmail.com', $user->getEmail());
        $this->assertEquals('hashed-password', $user->getPassword());
    }

    public function test_user_builder_bind_create_request_exception()
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);
        $encoderMock->method('encodePassword')
            ->willReturn('hashed-password');

        $request = new UserRegisterRequest();
        $request->email = 'test@gmail.com';
        $request->password = '123';
        $request->confirmPassword = '1234';

        $jwtManagerMock = $this->createMock(JWTManager::class);
        $service = new UserBuilder($entityManagerMock, $encoderMock, $jwtManagerMock);

        $this->expectException(PasswordConfirmationException::class);

        $service->create()->bind($request)->build();
    }

    public function test_user_builder_with_api_key()
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);

        $jwtManagerMock = $this->createMock(JWTManager::class);
        $jwtManagerMock->method('setUserIdentityField')->willReturn(null);
        $jwtManagerMock->method('create')->willReturn('123');

        $service = new UserBuilder($entityManagerMock, $encoderMock, $jwtManagerMock);

        /**@var string $apiKey*/
        $apiKey = $service->create()->withApiKey();

        $this->assertIsString($apiKey);
    }
}
