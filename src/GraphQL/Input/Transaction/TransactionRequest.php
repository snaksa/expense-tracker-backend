<?php declare(strict_types=1);

namespace App\GraphQL\Input\Transaction;

use App\GraphQL\Types\TransactionType;
use Overblog\GraphQLBundle\Annotation as GQL;

abstract class TransactionRequest
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $date;

    /**
     * @var string
     */
    public $description;
    /**
     * @var float
     */
    public $value;

    /**
     * @var TransactionType
     */
    public $type;

    /**
     * @var integer
     */
    public $categoryId;

    /**
     * @var integer
     */
    public $walletId;
}
