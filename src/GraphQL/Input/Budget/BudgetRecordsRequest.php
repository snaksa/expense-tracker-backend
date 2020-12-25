<?php declare(strict_types=1);

namespace App\GraphQL\Input\Budget;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class BudgetRecordsRequest
{
    /**
     * @GQL\Field(type="String")
     */
    public ?string $date;

    /**
     * @GQL\Field(type="String")
     */
    public ?string $timezone;
}
