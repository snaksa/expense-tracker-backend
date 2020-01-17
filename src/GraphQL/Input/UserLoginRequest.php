<?php declare(strict_types=1);

namespace App\GraphQL\Input;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class UserLoginRequest extends UserRequest
{
    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $email;

    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $password;
}
