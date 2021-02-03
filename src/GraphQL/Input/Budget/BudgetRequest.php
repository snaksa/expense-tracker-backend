<?php declare(strict_types=1);

namespace App\GraphQL\Input\Budget;

use Overblog\GraphQLBundle\Annotation as GQL;

abstract class BudgetRequest
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var float
     */
    public $value;

    /**
     * @var string
     */
    public $startDate;

    /**
     * @var string
     */
    public $endDate;

    /**
     * @var int[]
     */
    public $categoryIds;

    /**
     * @var int[]
     */
    public $labelIds;
}
