<?php declare(strict_types=1);

namespace App\GraphQL\Input\Category;

use App\GraphQL\Types\TransactionType;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class CategoryRecordsRequest
{
    /**
     * @GQL\Field(type="[Int]")
     * @var int[]
     */
    public $walletIds;

    /**
     * @GQL\Field(type="[Int]")
     * @var int[]
     */
    public $categoryIds;

    /**
     * @GQL\Field(type="TransactionType")
     * @var TransactionType
     */
    public $type;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $date;
}
