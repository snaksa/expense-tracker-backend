<?php declare(strict_types=1);

namespace App\GraphQL\Input\Transaction;

use App\Traits\PaginationUtils;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class TransactionRecordsRequest extends TransactionRequest
{
    use PaginationUtils;

    /**
     * @GQL\Field(type="[Int]!")
     * @var int[]
     */
    public $walletIds;

    /**
     * @GQL\Field(type="[Int]")
     * @var int[]
     */
    public $categoryIds;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $startDate;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $endDate;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $timezone;
}
