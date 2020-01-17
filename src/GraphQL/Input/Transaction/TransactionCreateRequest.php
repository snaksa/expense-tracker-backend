<?php declare(strict_types=1);

namespace App\GraphQL\Input\Transaction;

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
     * @GQL\Field(type="Int!")
     * @var integer
     */
    public $type;

    /**
     * @GQL\Field(type="Int!")
     * @var integer
     */
    public $categoryId;

    /**
     * @GQL\Field(type="Int!")
     * @var integer
     */
    public $walletId;
}
