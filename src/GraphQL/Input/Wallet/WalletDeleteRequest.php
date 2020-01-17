<?php declare(strict_types=1);

namespace App\GraphQL\Input\Wallet;

use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class WalletDeleteRequest extends WalletRequest
{
    /**
     * @GQL\Field(type="Int!")
     * @Assert\EntityExists(
     *  message="Wallet not found!",
     *  entityClass="App\Entity\Wallet"
     * )
     * @var integer
     */
    public $id;
}
