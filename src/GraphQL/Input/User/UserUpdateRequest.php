<?php declare(strict_types=1);

namespace App\GraphQL\Input\User;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class UserUpdateRequest extends UserRequest
{
    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $firstName;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $lastName;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $email;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $currency;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $language;
}
