<?php

declare (strict_types=1);

namespace App\GraphQL\Types;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 */
class AuthResponse
{

    /**
     * Auth Id Token
     *
     * @GQL\Field(type="String!")
     * @var string
     */
    protected $accessToken;

    /**
     * Auth Refresh Token
     *
     * @GQL\Field(type="String!")
     * @var string
     */
    protected $refreshToken;

    /**
     * The time when the token expires
     *
     * @GQL\Field(type="Int!")
     * @var int
     */
    protected $expiresIn;

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return AuthResponse
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return AuthResponse
     */
    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @return int
     */
    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    /**
     * @param int $expiresIn
     * @return AuthResponse
     */
    public function setExpiresIn(int $expiresIn): self
    {
        $this->expiresIn = $expiresIn;
        return $this;
    }
}
