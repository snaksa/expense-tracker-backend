<?php declare (strict_types=1);

namespace App\Services;

use App\Exception\Cognito\AliasExistsException;
use App\Exception\Cognito\CodeDeliveryFailureException;
use App\Exception\Cognito\CodeMismatchException;
use App\Exception\Cognito\CognitoException;
use App\Exception\Cognito\ExpiredCodeException;
use App\Exception\Cognito\InternalErrorException;
use App\Exception\Cognito\InvalidEmailRoleAccessPolicyException;
use App\Exception\Cognito\InvalidLambdaResponseException;
use App\Exception\Cognito\InvalidParameterException;
use App\Exception\Cognito\InvalidPasswordException;
use App\Exception\Cognito\InvalidUserPoolConfigurationException;
use App\Exception\Cognito\NotAuthorizedException;
use App\Exception\Cognito\PasswordResetRequiredException;
use App\Exception\Cognito\ResourceNotFoundException;
use App\Exception\Cognito\TooManyRequestsException;
use App\Exception\Cognito\UsernameExistsException;
use App\Exception\Cognito\UserNotConfirmedException;
use App\Exception\Cognito\UserNotFoundException;
use App\GraphQL\Types\AuthResponse;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

class UserService
{
    /**
     * @var CognitoIdentityProviderClient
     */
    protected $cognitoClient;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $poolId;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * UserService constructor.
     *
     * @param string $clientId
     * @param string $secretKey
     * @param string $poolId
     * @param CognitoIdentityProviderClient $cognitoClient
     */
    public function __construct(
        string $clientId,
        string $secretKey,
        string $poolId,
        CognitoIdentityProviderClient $cognitoClient
    ) {
        $this->clientId = $clientId;
        $this->cognitoClient = $cognitoClient;
        $this->poolId = $poolId;
        $this->secretKey = $secretKey;
    }

    /**
     * @param string $accessToken
     * @return \Aws\Result
     */
    public function getUser(string $accessToken): \Aws\Result
    {
        try {
            return $this->cognitoClient->getUser([
                'AccessToken' => $accessToken
            ]);
        } catch (CognitoIdentityProviderException $ex) {
            $this->handleCognitoException($ex, 'getUser');
        }

        return null;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return AuthResponse
     */
    public function loginUser(string $email, string $password): AuthResponse
    {
        $email = strtolower($email);
        $result = new AuthResponse();
        try {
            $response = $this->cognitoClient->adminInitiateAuth([
                'AuthParameters' => [
                    'USERNAME' => $email,
                    'PASSWORD' => $password,
                    'SECRET_HASH' => $this->cognitoSecretHash($email),
                ],
                'ClientId' => $this->clientId,
                'UserPoolId' => $this->poolId,
                'AuthFlow' => 'ADMIN_NO_SRP_AUTH'
            ]);

            if (!$response->hasKey('AuthenticationResult')) {
                throw new CognitoException('AuthenticationResult is not present in the response');
            }

            $auth = $response->get('AuthenticationResult');

            $result->setAccessToken($auth['AccessToken']);
            $result->setRefreshToken($auth['RefreshToken']);
            $result->setExpiresIn($auth['ExpiresIn']);
        } catch (CognitoIdentityProviderException $ex) {
            $this->handleCognitoException($ex, 'loginUser');
        }

        return $result;
    }

    /**
     * @param string $email
     * @param string $password
     * @return string
     */
    public function createUser(string $email, string $password): ?string
    {
        $email = strtolower($email);
        try {
            $user = $this->cognitoClient->signUp([
                'ClientId' => $this->clientId,
                'Username' => $email,
                'Password' => $password,
                'SecretHash' => $this->cognitoSecretHash($email),
                'UserAttributes' => [
                    [
                        'Name' => 'email',
                        'Value' => $email
                    ]
                ],
            ]);

            if ($user->hasKey('UserSub') && $user->get('UserSub') !== null) {
                return (string)$user->get('UserSub');
            }
        } catch (CognitoIdentityProviderException $ex) {
            $this->handleCognitoException($ex, 'createUser');
        }

        return null;
    }

    /**
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $accessToken
     * @return bool
     */
    public function changePassword(string $oldPassword, string $newPassword, string $accessToken): bool
    {
        try {
            $this->cognitoClient->changePassword([
                'PreviousPassword' => $oldPassword,
                'ProposedPassword' => $newPassword,
                'AccessToken' => $accessToken,
            ]);

            return true;
        } catch (CognitoIdentityProviderException $ex) {
            $this->handleCognitoException($ex, 'changePassword');
        }

        return false;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function confirmUser(string $email): bool
    {
        $email = strtolower($email);
        try {
            $this->cognitoClient->adminConfirmSignUp([
                'ClientId' => $this->clientId,
                'Username' => $email,
                'SecretHash' => $this->cognitoSecretHash($email),
                'UserPoolId' => $this->poolId,
            ]);

            return true;
        } catch (CognitoIdentityProviderException $ex) {
            $this->handleCognitoException($ex, 'confirmUser');
        }

        return false;
    }

    /**
     * @param string $email
     *
     * @param string $code
     * @return bool
     */
    public function confirmRegistration(string $email, string $code): bool
    {
        $email = strtolower($email);
        try {
            $this->cognitoClient->confirmSignUp([
                'ClientId' => $this->clientId,
                'Username' => $email,
                'ConfirmationCode' => $code,
                'SecretHash' => $this->cognitoSecretHash($email)
            ]);

            return true;
        } catch (CognitoIdentityProviderException $ex) {
            $this->handleCognitoException($ex, 'confirmRegistration');
        }

        return false;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function forgotPassword(string $email): bool
    {
        $email = strtolower($email);
        try {
            $this->cognitoClient->forgotPassword([
                'ClientId' => $this->clientId,
                'Username' => $email,
                'SecretHash' => $this->cognitoSecretHash($email)
            ]);

            return true;
        } catch (CognitoIdentityProviderException $ex) {
            $this->handleCognitoException($ex, 'forgotPassword');
        }

        return false;
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $code
     *
     * @return bool
     */
    public function confirmForgotPassword(string $email, string $password, string $code): bool
    {
        $email = strtolower($email);
        try {
            $this->cognitoClient->confirmForgotPassword([
                'ClientId' => $this->clientId,
                'ConfirmationCode' => $code,
                'Username' => $email,
                'Password' => $password,
                'SecretHash' => $this->cognitoSecretHash($email),
            ]);
        } catch (CognitoIdentityProviderException $ex) {
            $this->handleCognitoException($ex, 'confirmForgotPassword');
        }

        return true;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function resetPassword(string $email): bool
    {
        try {
            $this->cognitoClient->adminResetUserPassword([
                'Username' => $email,
                'UserPoolId' => $this->poolId
            ]);

            return true;
        } catch (CognitoIdentityProviderException $ex) {
            $this->handleCognitoException($ex, 'resetPassword');
        }

        return false;
    }

    /**
     * @param string $username
     *
     * @return string
     */
    protected function cognitoSecretHash($username)
    {
        $message = $username . $this->clientId;
        $hash = hash_hmac(
            'sha256',
            $message,
            $this->secretKey,
            true
        );

        return base64_encode($hash);
    }

    protected function handleCognitoException(CognitoIdentityProviderException $exception, string $caller = '')
    {
        switch ($exception->getAwsErrorCode()) {
            case 'TooManyRequestsException':
            case 'LimitExceededException':
            case 'TooManyFailedAttemptsException':
                $message = 'Too many attempts';
                if ($caller == 'loginUser') {
                    $message = 'Too many login attempts';
                } elseif ($caller == 'refreshToken') {
                    $message = 'Too many refresh attempts';
                } elseif ($caller == 'forgotPassword') {
                    $message = 'Too many forgot password attempts';
                } elseif ($caller == 'confirmForgotPassword') {
                    $message = 'Too many password conformation attempts';
                }

                throw TooManyRequestsException::fromString(
                    $message,
                    TooManyRequestsException::class
                );
                break;
            case 'InvalidPasswordException':
                throw InvalidPasswordException::fromString(
                    'Invalid password.',
                    InvalidPasswordException::class
                );
                break;
            case 'NotAuthorizedException':
                throw CognitoException::fromString(
                    'Invalid username or password',
                    NotAuthorizedException::class
                );
                break;
            case 'CodeMismatchException':
                throw CognitoException::fromString(
                    'Invalid confirmation code',
                    CodeMismatchException::class
                );
                break;
            case 'ExpiredCodeException':
                throw CognitoException::fromString(
                    'Verification code has expired',
                    ExpiredCodeException::class
                );
                break;
            case 'AliasExistsException':
                throw CognitoException::fromString(
                    'Alias already exists',
                    AliasExistsException::class
                );
                break;
            case 'UsernameExistsException':
                throw CognitoException::fromString(
                    'User already exists',
                    UsernameExistsException::class
                );
                break;
            case 'PasswordResetRequiredException':
                throw CognitoException::fromString(
                    'Password reset is required',
                    PasswordResetRequiredException::class
                );
                break;
            case 'CodeDeliveryFailureException':
                throw CognitoException::fromString(
                    'Failed to deliver verification code',
                    CodeDeliveryFailureException::class
                );
                break;
            case 'UserNotFoundException':
                throw CognitoException::fromString(
                    'User not found',
                    UserNotFoundException::class
                );
                break;
            case 'UserNotConfirmedException':
                throw CognitoException::fromString(
                    'User not confirmed',
                    UserNotConfirmedException::class
                );
                break;
            case 'ResourceNotFoundException':
                throw CognitoException::fromString(
                    'User not found',
                    ResourceNotFoundException::class
                );
                break;
            case 'InternalErrorException':
                throw CognitoException::fromString(
                    'Error with authentication service occurred',
                    InternalErrorException::class
                );
                break;
            case 'InvalidParameterException':
                throw CognitoException::fromString(
                    'Error with authentication service parameters occurred',
                    InvalidParameterException::class
                );
                break;
            case 'InvalidLambdaResponseException':
            case 'UnexpectedLambdaException':
            case 'UserLambdaValidationException':
                throw CognitoException::fromString(
                    'Error with authentication service settings occurred',
                    InvalidLambdaResponseException::class
                );
                break;
            case 'InvalidUserPoolConfigurationException':
                throw CognitoException::fromString(
                    'Error with authentication service configuration occurred',
                    InvalidUserPoolConfigurationException::class
                );
                break;
            case 'InvalidEmailRoleAccessPolicyException':
                throw CognitoException::fromString(
                    'No access to the given email',
                    InvalidEmailRoleAccessPolicyException::class
                );
                break;
            default:
                throw CognitoException::fromString('An error occurred');
                break;
        }
    }
}
