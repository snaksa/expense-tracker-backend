<?php declare(strict_types=1);

namespace App\GraphQL\Input\Transaction;

use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class TransactionDeleteRequest extends TransactionRequest
{
    /**
     * @GQL\Field(type="Int!")
     * @Assert\EntityExists(
     *  message="Transaction not found!",
     *  entityClass="App\Entity\Transaction"
     * )
     * @var integer
     */
    public $id;
}
