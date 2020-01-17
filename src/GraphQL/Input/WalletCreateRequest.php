<?php declare(strict_types=1);

namespace App\GraphQL\Input;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class WalletCreateRequest extends WalletRequest
{
    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $name;

    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $color;
}
