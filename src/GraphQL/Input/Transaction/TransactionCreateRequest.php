<?php declare(strict_types=1);

namespace App\GraphQL\Input\Transaction;

use App\GraphQL\Types\TransactionType;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class TransactionCreateRequest extends TransactionRequest
{
    /**
     * @Assert\NotBlank(message="Date should not be empty!")
     * @GQL\Field(type="String!")
     * @var string
     */
    public $date;

    /**
     * @Assert\NotBlank(message="Description should not be empty!")
     * @GQL\Field(type="String!")
     * @var string
     */
    public $description;

    /**
     * @Assert\NotBlank(message="Value should not be empty!")
     * @GQL\Field(type="Float!")
     * @var float
     */
    public $value;

    /**
     * @Assert\NotBlank(message="Type should not be empty!")
     * @GQL\Field(type="TransactionType!")
     * @var TransactionType
     */
    public $type;

    /**
     * @CustomAssert\EntityExists(
     *  message="Category not found!",
     *  entityClass="App\Entity\Category",
     *  allowNull=true
     * )
     * @GQL\Field(type="Int")
     * @var integer
     */
    public $categoryId;

    /**
     * @Assert\NotBlank(message="Wallet should not be empty!")
     * @CustomAssert\EntityExists(
     *  message="Wallet not found!",
     *  entityClass="App\Entity\Wallet"
     * )
     * @GQL\Field(type="Int!")
     * @var integer
     */
    public $walletId;

    /**
     * @CustomAssert\EntityExists(
     *  message="Wallet Receiver not found!",
     *  entityClass="App\Entity\Wallet",
     *  allowNull=true
     * )
     * @GQL\Field(type="Int")
     * @var integer
     */
    public $walletReceiverId;
}
