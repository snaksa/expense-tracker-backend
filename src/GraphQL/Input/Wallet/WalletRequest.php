<?php declare(strict_types=1);

namespace App\GraphQL\Input\Wallet;

use Overblog\GraphQLBundle\Annotation as GQL;

abstract class WalletRequest
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $color;

    /**
     * @var float
     */
    public $amount;
}
