<?php declare(strict_types=1);

namespace App\Exception\Cognito;

use RuntimeException;

class InvalidParameterException extends CognitoException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
