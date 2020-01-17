<?php declare(strict_types=1);

namespace App\GraphQL\Input;

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
    public $description;
    /**
     * @var float
     */
    public $value;

    /**
     * @var integer
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
