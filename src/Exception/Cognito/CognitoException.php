<?php declare(strict_types=1);

namespace App\Exception\Cognito;

use RuntimeException;

class CognitoException extends RuntimeException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }

    /**
     * @param string $message
     *
     * @param string $exceptionClass
     * @return CognitoException
     */
    public static function fromString($message, string $exceptionClass = null)
    {
        if ($exceptionClass && class_exists($exceptionClass)) {
            return new $exceptionClass($message);
        }
        return new self($message);
    }
}
