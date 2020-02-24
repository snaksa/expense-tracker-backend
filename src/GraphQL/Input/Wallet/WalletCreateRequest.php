<?php declare(strict_types=1);

namespace App\GraphQL\Input\Wallet;

use Symfony\Component\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class WalletCreateRequest extends WalletRequest
{
    /**
     * @Assert\NotBlank(message="Name should not be empty!")
     * @GQL\Field(type="String!")
     * @var string
     */
    public $name;

    /**
     * @Assert\NotBlank(message="Color should not be empty!")
     * @GQL\Field(type="String!")
     * @var string
     */
    public $color;

    /**
     * @GQL\Field(type="Float")
     * @var float
     */
    public $amount;
}
