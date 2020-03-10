<?php declare(strict_types=1);

namespace App\GraphQL\Input\User;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class UserChangePasswordRequest extends UserRequest
{
    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $oldPassword;

    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $newPassword;
}
