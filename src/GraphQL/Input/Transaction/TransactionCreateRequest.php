<?php declare(strict_types=1);

namespace App\GraphQL\Input\Transaction;

use App\GraphQL\Types\TransactionType;
use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class TransactionCreateRequest extends TransactionRequest
{
    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $description;

    /**
     * @GQL\Field(type="Float!")
     * @var float
     */
    public $value;

    /**
     * @GQL\Field(type="TransactionType!")
     * @var TransactionType
     */
    public $type;

    /**
     * @GQL\Field(type="Int!")
     * @Assert\EntityExists(
     *  message="Category not found!",
     *  entityClass="App\Entity\Category"
     * )
     * @var integer
     */
    public $categoryId;

    /**
     * @GQL\Field(type="Int!")
     * @Assert\EntityExists(
     *  message="Wallet not found!",
     *  entityClass="App\Entity\Wallet"
     * )
     * @var integer
     */
    public $walletId;
}
