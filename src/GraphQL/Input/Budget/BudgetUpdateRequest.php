<?php declare(strict_types=1);

namespace App\GraphQL\Input\Budget;

use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class BudgetUpdateRequest extends BudgetRequest
{
    /**
     * @GQL\Field(type="Int!")
     * @Assert\EntityExists(
     *  message="Budget not found!",
     *  entityClass="App\Entity\Budget"
     * )
     * @var integer
     */
    public $id;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $name;

    /**
     * @GQL\Field(type="Float")
     * @var float
     */
    public $value;

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
     * @GQL\Field(type="[Int]")
     * @var int[]
     */
    public $categoryIds;

    /**
     * @GQL\Field(type="[Int]")
     * @var int[]
     */
    public $labelIds;
}
