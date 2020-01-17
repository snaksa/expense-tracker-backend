<?php declare(strict_types=1);

namespace App\GraphQL\Input\Transaction;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class TransactionDeleteRequest extends TransactionRequest
{
    /**
     * @GQL\Field(type="Int!")
     * @var integer
     */
    public $id;
}
