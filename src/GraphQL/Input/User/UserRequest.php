<?php declare(strict_types=1);

namespace App\GraphQL\Input\User;

use Overblog\GraphQLBundle\Annotation as GQL;

abstract class UserRequest
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $language;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $confirmPassword;
}
