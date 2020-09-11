<?php declare(strict_types=1);

namespace App\Exception;

use Exception;
use Overblog\GraphQLBundle\Error\UserErrors;

class GraphQLException extends UserErrors
{
    /**
     * GraphQLException constructor.
     * @param string[] $errors
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(array $errors = [], $message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($errors, $message, $code, $previous);
    }

    /**
     * @param string $message
     *
     * @return GraphQLException
     */
    public static function fromString($message)
    {
        return new self([$message]);
    }
}
