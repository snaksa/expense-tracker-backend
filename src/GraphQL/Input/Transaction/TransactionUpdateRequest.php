<?php declare(strict_types=1);

namespace App\GraphQL\Input\Transaction;

use App\GraphQL\Types\TransactionType;
use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class TransactionUpdateRequest extends TransactionRequest
{
    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $date;

    /**
     * @GQL\Field(type="Int!")
     * @Assert\EntityExists(
     *  message="Transaction not found!",
     *  entityClass="App\Entity\Transaction"
     * )
     * @var integer
     */
    public $id;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $description;

    /**
     * @GQL\Field(type="Float")
     * @var float
     */
    public $value;

    /**
     * @GQL\Field(type="TransactionType")
     * @var TransactionType
     */
    public $type;

    /**
     * @GQL\Field(type="Int")
     * @Assert\EntityExists(
     *  message="Category not found!",
     *  entityClass="App\Entity\Category",
     *  allowNull=true
     * )
     * @var integer
     */
    public $categoryId;

    /**
     * @GQL\Field(type="Int")
     * @Assert\EntityExists(
     *  message="Wallet not found!",
     *  entityClass="App\Entity\Wallet",
     *  allowNull=true
     * )
     * @var integer
     */
    public $walletId;

    /**
     * @Assert\EntityExists(
     *  message="Wallet Receiver not found!",
     *  entityClass="App\Entity\Wallet",
     *  allowNull=true
     * )
     * @GQL\Field(type="Int")
     * @var integer
     */
    public $walletReceiverId;
}
