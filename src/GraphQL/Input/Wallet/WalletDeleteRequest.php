<?php declare(strict_types=1);

namespace App\GraphQL\Input\Wallet;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class WalletDeleteRequest extends WalletRequest
{
    /**
     * @GQL\Field(type="Int!")
     * @var integer
     */
    public $id;
}
