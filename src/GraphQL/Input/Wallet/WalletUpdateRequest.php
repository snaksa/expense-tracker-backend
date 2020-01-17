<?php declare(strict_types=1);

namespace App\GraphQL\Input\Wallet;

use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class WalletUpdateRequest extends WalletRequest
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
