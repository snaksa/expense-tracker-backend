<?php declare(strict_types=1);

namespace App\GraphQL\Input;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class WalletUpdateRequest extends WalletRequest
{
    /**
     * @GQL\Field(type="Int!")
     * @var integer
     */
    public $id;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $name;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $color;
}
