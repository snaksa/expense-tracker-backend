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
     * @GQL\Field(type="[Int]!")
     */
    public array $walletIds;

    /**
     * @GQL\Field(type="[Int]")
     */
    public ?array $categoryIds;

    /**
     * @GQL\Field(type="[Int]")
     */
    public ?array $labelIds;

    /**
     * @GQL\Field(type="TransactionType")
     */
    public ?TransactionType $type;

    /**
     * @GQL\Field(type="String")
     */
    public ?string $startDate;

    /**
     * @GQL\Field(type="String")
     */
    public ?string $endDate;

    /**
     * @GQL\Field(type="String")
     */
    public ?string $timezone;
}
