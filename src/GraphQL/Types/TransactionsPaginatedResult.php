<?php declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Entity\Transaction;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 */
class TransactionsPaginatedResult extends PaginatedResult
{
    /**
     * @GQL\Field(type="[Transaction]!")
     * @var Transaction[]
     */
    public $data = [];
}
