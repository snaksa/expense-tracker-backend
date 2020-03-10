<?php

namespace App\Tests\Unit\Services;

use App\Exception\Cognito\AliasExistsException;
use App\Exception\Cognito\CodeDeliveryFailureException;
use App\Exception\Cognito\CodeMismatchException;
use App\Exception\Cognito\ExpiredCodeException;
use App\Exception\Cognito\InternalErrorException;
use App\Exception\Cognito\InvalidEmailRoleAccessPolicyException;
use App\Exception\Cognito\InvalidLambdaResponseException;
use App\Exception\Cognito\InvalidParameterException;
use App\Exception\Cognito\InvalidUserPoolConfigurationException;
use App\Exception\Cognito\NotAuthorizedException;
use App\Exception\Cognito\PasswordResetRequiredException;
use App\Exception\Cognito\ResourceNotFoundException;
use App\Exception\Cognito\TooManyRequestsException;
use App\Exception\Cognito\UsernameExistsException;
use App\Exception\Cognito\UserNotConfirmedException;
use App\Exception\Cognito\UserNotFoundException;
use App\Exception\Cognito\InvalidPasswordException;
use App\Services\UserService;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Aws\Command;
use Aws\Result;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function test_get_user_success()
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('getUser')->willReturn(new Result(['Username' => 'test']));

        $service = new UserService('', '', '', $cognitoService);

        $result = $service->getUser('123');
        $this->assertArrayHasKey('Username', $result);
        $this->assertEquals('test', $result['Username']);
    }

    /**
     * @dataProvider provide_get_user_exceptions
     * @param string $awsCode
     * @param string $exceptionClass
     */
    public function test_get_user_exceptions(string $awsCode, string $exceptionClass)
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $awsCommand = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('getUser')->willThrowException(new CognitoIdentityProviderException('', $awsCommand, ['code' => $awsCode]));

        $service = new UserService('', '', '', $cognitoService);

        $this->expectException($exceptionClass);

        $service->getUser('123');
    }

    public function provide_get_user_exceptions()
    {
        return [
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['InvalidParameterException', InvalidParameterException::class],
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['PasswordResetRequiredException', PasswordResetRequiredException::class],
            ['ResourceNotFoundException', ResourceNotFoundException::class],
            ['TooManyRequestsException', TooManyRequestsException::class],
            ['UserNotConfirmedException', UserNotConfirmedException::class],
            ['UserNotFoundException', UserNotFoundException::class],
            ['InternalErrorException', InternalErrorException::class]
        ];
    }

    public function test_login_user_success()
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['adminInitiateAuth'])
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('adminInitiateAuth')->willReturn(new Result([
            'AuthenticationResult' => [
                'AccessToken' => '123',
                'RefreshToken' => '456',
                'ExpiresIn' => 124324
            ]
        ]));

        $service = new UserService('', '', '', $cognitoService);

        /** @var Result $result */
        $result = $service->loginUser('test@gmai.com', 'password');
        $this->assertEquals('123', $result->getAccessToken());
        $this->assertEquals('456', $result->getRefreshToken());
        $this->assertEquals(124324, $result->getExpiresIn());
    }

    /**
     * @dataProvider provide_login_user_exceptions
     * @param string $awsCode
     * @param string $exceptionClass
     */
    public function test_login_user_exceptions(string $awsCode, string $exceptionClass)
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['adminInitiateAuth'])
            ->disableOriginalConstructor()
            ->getMock();

        $awsCommand = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('adminInitiateAuth')->willThrowException(new CognitoIdentityProviderException('', $awsCommand, ['code' => $awsCode]));

        $service = new UserService('', '', '', $cognitoService);

        $this->expectException($exceptionClass);

        $service->loginUser('test@gmail.com', 'password');
    }

    public function provide_login_user_exceptions()
    {
        return [
            ['InternalErrorException', InternalErrorException::class],
            ['InvalidLambdaResponseException', InvalidLambdaResponseException::class],
            ['InvalidParameterException', InvalidParameterException::class],
            ['InvalidUserPoolConfigurationException', InvalidUserPoolConfigurationException::class],
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['PasswordResetRequiredException', PasswordResetRequiredException::class],
            ['ResourceNotFoundException', ResourceNotFoundException::class],
            ['TooManyRequestsException', TooManyRequestsException::class],
            ['UnexpectedLambdaException', InvalidLambdaResponseException::class],
            ['UserLambdaValidationException', InvalidLambdaResponseException::class],
            ['UserNotConfirmedException', UserNotConfirmedException::class],
            ['UserNotFoundException', UserNotFoundException::class]
        ];
    }

    public function test_create_user_success()
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['signUp'])
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('signUp')->willReturn(new Result([
            'UserSub' => '123'
        ]));

        $service = new UserService('', '', '', $cognitoService);

        /** @var Result $result */
        $result = $service->createUser('test@gmai.com', 'password');
        $this->assertEquals('123', $result);
    }

    /**
     * @dataProvider provide_create_user_exceptions
     * @param string $awsCode
     * @param string $exceptionClass
     */
    public function test_create_user_exceptions(string $awsCode, string $exceptionClass)
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['signUp'])
            ->disableOriginalConstructor()
            ->getMock();

        $awsCommand = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('signUp')->willThrowException(new CognitoIdentityProviderException('', $awsCommand, ['code' => $awsCode]));

        $service = new UserService('', '', '', $cognitoService);

        $this->expectException($exceptionClass);

        $service->createUser('test@gmail.com', 'password');
    }

    public function provide_create_user_exceptions()
    {
        return [
            ['CodeDeliveryFailureException', CodeDeliveryFailureException::class],
            ['InternalErrorException', InternalErrorException::class],
            ['InvalidEmailRoleAccessPolicyException', InvalidEmailRoleAccessPolicyException::class],
            ['InvalidLambdaResponseException', InvalidLambdaResponseException::class],
            ['InvalidParameterException', InvalidParameterException::class],
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['ResourceNotFoundException', ResourceNotFoundException::class],
            ['TooManyRequestsException', TooManyRequestsException::class],
            ['UnexpectedLambdaException', InvalidLambdaResponseException::class],
            ['UserLambdaValidationException', InvalidLambdaResponseException::class],
            ['UsernameExistsException', UsernameExistsException::class],
        ];
    }


    public function test_change_password_success()
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['changePassword'])
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('changePassword')->with([
            'PreviousPassword' => 'oldPassword',
            'ProposedPassword' => 'newPassword',
            'AccessToken' => 'accessToken'
        ])->willReturn(true);

        $service = new UserService('', '', '', $cognitoService);

        /** @var Result $result */
        $result = $service->changePassword('oldPassword', 'newPassword', 'accessToken');
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider provide_change_password_exceptions
     * @param string $awsCode
     * @param string $exceptionClass
     */
    public function test_change_password_exceptions(string $awsCode, string $exceptionClass)
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['changePassword'])
            ->disableOriginalConstructor()
            ->getMock();

        $awsCommand = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();


        $cognitoService->method('changePassword')->with([
            'PreviousPassword' => 'oldPassword',
            'ProposedPassword' => 'newPassword',
            'AccessToken' => 'accessToken'
        ])->willThrowException(new CognitoIdentityProviderException('', $awsCommand, ['code' => $awsCode]));

        $service = new UserService('', '', '', $cognitoService);

        $this->expectException($exceptionClass);

        $service->changePassword('oldPassword', 'newPassword', 'accessToken');
    }

    public function provide_change_password_exceptions()
    {
        return [
            ['InternalErrorException', InternalErrorException::class],
            ['InvalidParameterException', InvalidParameterException::class],
            ['InvalidPasswordException', InvalidPasswordException::class],
            ['LimitExceededException', TooManyRequestsException::class],
            ['TooManyRequestsException', TooManyRequestsException::class],
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['PasswordResetRequiredException', PasswordResetRequiredException::class],
            ['ResourceNotFoundException', ResourceNotFoundException::class],
            ['UserNotConfirmedException', UserNotConfirmedException::class],
            ['UserNotFoundException', UserNotFoundException::class],
        ];
    }

    public function test_confirm_user_success()
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['adminConfirmSignUp'])
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('adminConfirmSignUp')->willReturn(true);

        $service = new UserService('', '', '', $cognitoService);

        /** @var Result $result */
        $result = $service->confirmUser('test@gmail.com');
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider provide_confirm_user_exceptions
     * @param string $awsCode
     * @param string $exceptionClass
     */
    public function test_confirm_user_exceptions(string $awsCode, string $exceptionClass)
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['adminConfirmSignUp'])
            ->disableOriginalConstructor()
            ->getMock();

        $awsCommand = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();


        $cognitoService->method('adminConfirmSignUp')->willThrowException(new CognitoIdentityProviderException('', $awsCommand, ['code' => $awsCode]));

        $service = new UserService('', '', '', $cognitoService);

        $this->expectException($exceptionClass);

        $service->confirmUser('test@gmail.com');
    }

    public function provide_confirm_user_exceptions()
    {
        return [
            ['InternalErrorException', InternalErrorException::class],
            ['InvalidLambdaResponseException', InvalidLambdaResponseException::class],
            ['InvalidParameterException', InvalidParameterException::class],
            ['LimitExceededException', TooManyRequestsException::class],
            ['TooManyFailedAttemptsException', TooManyRequestsException::class],
            ['TooManyRequestsException', TooManyRequestsException::class],
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['ResourceNotFoundException', ResourceNotFoundException::class],
            ['UnexpectedLambdaException', InvalidLambdaResponseException::class],
            ['UserLambdaValidationException', InvalidLambdaResponseException::class],
            ['UserNotFoundException', UserNotFoundException::class],
        ];
    }

    public function test_confirm_registration_success()
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['confirmSignUp'])
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('confirmSignUp')->willReturn(true);

        $service = new UserService('', '', '', $cognitoService);

        /** @var Result $result */
        $result = $service->confirmRegistration('test@gmail.com', '123456');
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider provide_confirm_registration_exceptions
     * @param string $awsCode
     * @param string $exceptionClass
     */
    public function test_confirm_registration_exceptions(string $awsCode, string $exceptionClass)
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['confirmSignUp'])
            ->disableOriginalConstructor()
            ->getMock();

        $awsCommand = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();


        $cognitoService->method('confirmSignUp')->willThrowException(new CognitoIdentityProviderException('', $awsCommand, ['code' => $awsCode]));

        $service = new UserService('', '', '', $cognitoService);

        $this->expectException($exceptionClass);

        $service->confirmRegistration('test@gmail.com', '123456');
    }

    public function provide_confirm_registration_exceptions()
    {
        return [
            ['InternalErrorException', InternalErrorException::class],
            ['AliasExistsException', AliasExistsException::class],
            ['CodeMismatchException', CodeMismatchException::class],
            ['ExpiredCodeException', ExpiredCodeException::class],
            ['InvalidLambdaResponseException', InvalidLambdaResponseException::class],
            ['UnexpectedLambdaException', InvalidLambdaResponseException::class],
            ['UserLambdaValidationException', InvalidLambdaResponseException::class],
            ['InvalidParameterException', InvalidParameterException::class],
            ['LimitExceededException', TooManyRequestsException::class],
            ['TooManyFailedAttemptsException', TooManyRequestsException::class],
            ['TooManyRequestsException', TooManyRequestsException::class],
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['ResourceNotFoundException', ResourceNotFoundException::class],
            ['UserNotFoundException', UserNotFoundException::class]
        ];
    }

    public function test_forgot_password_success()
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['forgotPassword'])
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('forgotPassword')->willReturn(true);

        $service = new UserService('', '', '', $cognitoService);

        /** @var Result $result */
        $result = $service->forgotPassword('test@gmail.com');
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider provide_forgot_password_exceptions
     * @param string $awsCode
     * @param string $exceptionClass
     */
    public function test_forgot_password_exceptions(string $awsCode, string $exceptionClass)
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['forgotPassword'])
            ->disableOriginalConstructor()
            ->getMock();

        $awsCommand = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();


        $cognitoService->method('forgotPassword')->willThrowException(new CognitoIdentityProviderException('', $awsCommand, ['code' => $awsCode]));

        $service = new UserService('', '', '', $cognitoService);

        $this->expectException($exceptionClass);

        $service->forgotPassword('test@gmail.com');
    }

    public function provide_forgot_password_exceptions()
    {
        return [
            ['InternalErrorException', InternalErrorException::class],
            ['CodeDeliveryFailureException', CodeDeliveryFailureException::class],
            ['InvalidEmailRoleAccessPolicyException', InvalidEmailRoleAccessPolicyException::class],
            ['InvalidLambdaResponseException', InvalidLambdaResponseException::class],
            ['UnexpectedLambdaException', InvalidLambdaResponseException::class],
            ['UserLambdaValidationException', InvalidLambdaResponseException::class],
            ['InvalidParameterException', InvalidParameterException::class],
            ['LimitExceededException', TooManyRequestsException::class],
            ['TooManyRequestsException', TooManyRequestsException::class],
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['ResourceNotFoundException', ResourceNotFoundException::class],
            ['UserNotConfirmedException', UserNotConfirmedException::class],
            ['UserNotFoundException', UserNotFoundException::class]
        ];
    }

    public function test_confirm_forgot_password_success()
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['confirmForgotPassword'])
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('confirmForgotPassword')->willReturn(true);

        $service = new UserService('', '', '', $cognitoService);

        /** @var Result $result */
        $result = $service->confirmForgotPassword('test@gmail.com', 'password', '123456');
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider provide_confirm_forgot_password_exceptions
     * @param string $awsCode
     * @param string $exceptionClass
     */
    public function test_confirm_forgot_password_exceptions(string $awsCode, string $exceptionClass)
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['confirmForgotPassword'])
            ->disableOriginalConstructor()
            ->getMock();

        $awsCommand = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();


        $cognitoService->method('confirmForgotPassword')->willThrowException(new CognitoIdentityProviderException('', $awsCommand, ['code' => $awsCode]));

        $service = new UserService('', '', '', $cognitoService);

        $this->expectException($exceptionClass);

        $service->confirmForgotPassword('test@gmail.com', 'password', '123456');
    }

    public function provide_confirm_forgot_password_exceptions()
    {
        return [
            ['InternalErrorException', InternalErrorException::class],
            ['CodeMismatchException', CodeMismatchException::class],
            ['ExpiredCodeException', ExpiredCodeException::class],
            ['InvalidLambdaResponseException', InvalidLambdaResponseException::class],
            ['UnexpectedLambdaException', InvalidLambdaResponseException::class],
            ['UserLambdaValidationException', InvalidLambdaResponseException::class],
            ['InvalidParameterException', InvalidParameterException::class],
            ['InvalidPasswordException', InvalidPasswordException::class],
            ['LimitExceededException', TooManyRequestsException::class],
            ['TooManyFailedAttemptsException', TooManyRequestsException::class],
            ['TooManyRequestsException', TooManyRequestsException::class],
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['ResourceNotFoundException', ResourceNotFoundException::class],
            ['UserNotConfirmedException', UserNotConfirmedException::class],
            ['UserNotFoundException', UserNotFoundException::class],
        ];
    }

    public function test_reset_password_success()
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['adminResetUserPassword'])
            ->disableOriginalConstructor()
            ->getMock();

        $cognitoService->method('adminResetUserPassword')->willReturn(true);

        $service = new UserService('', '', '', $cognitoService);

        /** @var Result $result */
        $result = $service->resetPassword('test@gmail.com');
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider provide_reset_password_exceptions
     * @param string $awsCode
     * @param string $exceptionClass
     */
    public function test_reset_password_exceptions(string $awsCode, string $exceptionClass)
    {
        $cognitoService = $this->getMockBuilder(CognitoIdentityProviderClient::class)
            ->setMethods(['adminResetUserPassword'])
            ->disableOriginalConstructor()
            ->getMock();

        $awsCommand = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();


        $cognitoService->method('adminResetUserPassword')->willThrowException(new CognitoIdentityProviderException('', $awsCommand, ['code' => $awsCode]));

        $service = new UserService('', '', '', $cognitoService);

        $this->expectException($exceptionClass);

        $service->resetPassword('test@gmail.com');
    }

    public function provide_reset_password_exceptions()
    {
        return [
            ['InternalErrorException', InternalErrorException::class],
            ['InvalidEmailRoleAccessPolicyException', InvalidEmailRoleAccessPolicyException::class],
            ['InvalidLambdaResponseException', InvalidLambdaResponseException::class],
            ['UnexpectedLambdaException', InvalidLambdaResponseException::class],
            ['UserLambdaValidationException', InvalidLambdaResponseException::class],
            ['LimitExceededException', TooManyRequestsException::class],
            ['TooManyRequestsException', TooManyRequestsException::class],
            ['NotAuthorizedException', NotAuthorizedException::class],
            ['ResourceNotFoundException', ResourceNotFoundException::class],
            ['UserNotFoundException', UserNotFoundException::class]
        ];
    }
}
