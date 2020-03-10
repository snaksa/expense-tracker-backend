<?php declare(strict_types=1);

namespace App\GraphQL\Input\User;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class UserRegistrationConfirmationRequest extends UserRequest
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
    public $code;
}
