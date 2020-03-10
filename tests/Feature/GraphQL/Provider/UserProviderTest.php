<?php

namespace App\Tests\Feature\GraphQL\Provider;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Exception\Cognito\CognitoException;
use App\GraphQL\Types\AuthResponse;
use App\Repository\UserRepository;
use App\Services\AuthorizationService;
use App\Services\UserService;
use App\Tests\Feature\BaseTestCase;

class UserProviderTest extends BaseTestCase
{
    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');

        $this->client = $this->makeClient();
    }

    /**
     * @test
     */
    public function can_retrieve_logged_user(): void
    {
        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'me',
            [],
            ['email', 'firstName', 'lastName']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('me', $content);

        $expected = [
            'email' => $this->user->getEmail(),
            'firstName' => $this->user->getFirstName(),
            'lastName' => $this->user->getLastName()
        ];

        $this->assertEquals($expected, $content['me']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_non_logged_user(): void
    {
        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'me',
            [],
            ['email', 'firstName', 'lastName']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_register_user(): void
    {
        $email = 'test@gmail.com';
        $password = '123456';

        $userRepositoryMock = $this->getServiceMockBuilder(UserRepository::class)->getMock();
        $userRepositoryMock->expects($this->once())->method('findOneBy')->with(['email' => $email])->willReturn(null);
        $userRepositoryMock->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock->expects($this->once())->method('createUser')->with($email, $password)->willReturn('externalId');
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'registerUser',
            [
                'input' => [
                    'email' => $email,
                    'password' => $password,
                    'confirmPassword' => $password
                ]
            ],
            ['email', 'firstName', 'lastName']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('registerUser', $content);

        $expected = [
            'email' => $email,
            'firstName' => null,
            'lastName' => null
        ];

        $this->assertEquals($expected, $content['registerUser']);
    }

    /**
     * @test
     */
    public function can_not_register_user_with_not_matching_passwords(): void
    {
        $email = 'test@gmail.com';
        $password = '123456';

        $this->mutation(
            'registerUser',
            [
                'input' => [
                    'email' => $email,
                    'password' => $password,
                    'confirmPassword' => 'different-password'
                ]
            ],
            ['email', 'firstName', 'lastName']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Passwords do not match', $response);
    }

    /**
     * @test
     */
    public function can_not_register_user_with_existing_email(): void
    {
        $email = 'test@gmail.com';
        $password = '123456';

        $userRepositoryMock = $this->getServiceMockBuilder(UserRepository::class)->getMock();
        $userRepositoryMock->expects($this->once())->method('findOneBy')->with(['email' => $email])->willReturn(new User());
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->mutation(
            'registerUser',
            [
                'input' => [
                    'email' => $email,
                    'password' => $password,
                    'confirmPassword' => $password
                ]
            ],
            ['email', 'firstName', 'lastName']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('User with this email already exists', $response);
    }

    /**
     * @test
     */
    public function can_not_register_user_cognito_exception(): void
    {
        $email = 'test@gmail.com';
        $password = '123456';

        $userRepositoryMock = $this->getServiceMockBuilder(UserRepository::class)->getMock();
        $userRepositoryMock->expects($this->once())->method('findOneBy')->with(['email' => $email])->willReturn(null);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock->expects($this->once())->method('createUser')
            ->with($email, $password)
            ->willThrowException(CognitoException::fromString('Error message'));
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'registerUser',
            [
                'input' => [
                    'email' => $email,
                    'password' => $password,
                    'confirmPassword' => $password
                ]
            ],
            ['email', 'firstName', 'lastName']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Error message', $response);
    }

    /**
     * @test
     */
    public function can_confirm_registration(): void
    {
        $email = 'test@gmail.com';
        $code = '123456';

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock->expects($this->once())->method('confirmRegistration')->with($email, $code)->willReturn(true);
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'confirmRegistration',
            [
                'input' => [
                    'email' => $email,
                    'code' => $code
                ]
            ],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('confirmRegistration', $content);

        $expected = true;

        $this->assertEquals($expected, $content['confirmRegistration']);
    }

    /**
     * @test
     */
    public function can_not_confirm_user_cognito_exception(): void
    {
        $email = 'test@gmail.com';
        $code = 'code';

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock->expects($this->once())->method('confirmRegistration')
            ->with($email, $code)
            ->willThrowException(CognitoException::fromString('Error message'));
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'confirmRegistration',
            [
                'input' => [
                    'email' => $email,
                    'code' => $code
                ]
            ],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Error message', $response);
    }

    /**
     * @test
     */
    public function can_login_user(): void
    {
        $email = 'test@gmail.com';
        $password = '123456';

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock
            ->expects($this->once())
            ->method('loginUser')
            ->with($email, $password)
            ->willReturn((new AuthResponse())->setAccessToken('123'));
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'loginUser',
            [
                'input' => [
                    'email' => $email,
                    'password' => $password
                ]
            ],
            ['accessToken']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('loginUser', $content);

        $expected = [
            'accessToken' => '123'
        ];

        $this->assertEquals($expected, $content['loginUser']);
    }

    /**
     * @test
     */
    public function can_not_login_user_cognito_exception(): void
    {
        $email = 'test@gmail.com';
        $password = 'code';

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock
            ->expects($this->once())
            ->method('loginUser')
            ->with($email, $password)
            ->willThrowException(CognitoException::fromString('Error message'));
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'loginUser',
            [
                'input' => [
                    'email' => $email,
                    'password' => $password
                ]
            ],
            ['accessToken']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Error message', $response);
    }

    /**
     * @test
     */
    public function can_change_password(): void
    {
        $oldPassword = '123456';
        $newPassword = '123123';

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock
            ->expects($this->once())
            ->method('changePassword')
            ->with($oldPassword, $newPassword, $this->user->getApiKey())
            ->willReturn(true);
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'changePassword',
            [
                'input' => [
                    'oldPassword' => $oldPassword,
                    'newPassword' => $newPassword
                ]
            ],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('changePassword', $content);

        $expected = true;

        $this->assertEquals($expected, $content['changePassword']);
    }

    /**
     * @test
     */
    public function can_not_change_password_if_not_logged(): void
    {
        $oldPassword = '123456';
        $newPassword = '123123';

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->mutation(
            'changePassword',
            [
                'input' => [
                    'oldPassword' => $oldPassword,
                    'newPassword' => $newPassword
                ]
            ],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_change_password_cognito_exception(): void
    {
        $oldPassword = '123456';
        $newPassword = '123123';

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock
            ->expects($this->once())
            ->method('changePassword')
            ->with($oldPassword, $newPassword, $this->user->getApiKey())
            ->willThrowException(CognitoException::fromString('Error message'));
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'changePassword',
            [
                'input' => [
                    'oldPassword' => $oldPassword,
                    'newPassword' => $newPassword
                ]
            ],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Error message', $response);
    }

    /**
     * @test
     */
    public function can_forgot_password(): void
    {
        $email = 'test@gmail.com';

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock
            ->expects($this->once())
            ->method('forgotPassword')
            ->with($email)
            ->willReturn(true);
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'forgotPassword',
            [
                'input' => [
                    'email' => $email,
                ]
            ],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('forgotPassword', $content);

        $expected = true;

        $this->assertEquals($expected, $content['forgotPassword']);
    }

    /**
     * @test
     */
    public function can_not_forgot_password_cognito_exception(): void
    {
        $email = 'test@gmail.com';

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock
            ->expects($this->once())
            ->method('forgotPassword')
            ->with($email)
            ->willThrowException(CognitoException::fromString('Error message'));
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'forgotPassword',
            [
                'input' => [
                    'email' => $email,
                ]
            ],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Error message', $response);
    }

    /**
     * @test
     */
    public function can_confirm_forgot_password(): void
    {
        $email = 'test@gmail.com';
        $password = 'password';
        $code = 'code';

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock
            ->expects($this->once())
            ->method('confirmForgotPassword')
            ->with($email)
            ->willReturn(true);
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'forgotPasswordConfirmation',
            [
                'input' => [
                    'email' => $email,
                    'password' => $password,
                    'code' => $code,
                ]
            ],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('forgotPasswordConfirmation', $content);

        $expected = true;

        $this->assertEquals($expected, $content['forgotPasswordConfirmation']);
    }

    /**
     * @test
     */
    public function can_not_confirm_forgot_password_cognito_exception(): void
    {
        $email = 'test@gmail.com';
        $password = 'password';
        $code = 'code';

        $userServiceMock = $this->getServiceMockBuilder(UserService::class)->getMock();
        $userServiceMock
            ->expects($this->once())
            ->method('confirmForgotPassword')
            ->with($email)
            ->willThrowException(CognitoException::fromString('Error message'));
        $this->client->getContainer()->set(UserService::class, $userServiceMock);

        $this->mutation(
            'forgotPasswordConfirmation',
            [
                'input' => [
                    'email' => $email,
                    'password' => $password,
                    'code' => $code,
                ]
            ],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Error message', $response);
    }
}
