<?php declare(strict_types=1);

namespace App\GraphQL\Types;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Enum(values={
 *     @GQL\EnumValue(name="INCOME"),
 *     @GQL\EnumValue(name="EXPENSE"),
 *     @GQL\EnumValue(name="TRANSFER")
 * })
 *
 * @package App\GraphQL\Types
 */
class TransactionType
{
    const INCOME = 1;
    const EXPENSE = 2;
    const TRANSFER = 3;

    public int $value;
}
