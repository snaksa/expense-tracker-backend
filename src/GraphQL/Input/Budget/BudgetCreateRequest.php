<?php declare(strict_types=1);

namespace App\GraphQL\Input\Budget;

use Symfony\Component\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class BudgetCreateRequest extends BudgetRequest
{
    /**
     * @Assert\NotBlank(message="Name should not be empty!")
     * @GQL\Field(type="String!")
     * @var string
     */
    public $name;

    /**
     * @Assert\NotBlank(message="Value should not be empty!")
     * @GQL\Field(type="Float!")
     * @var float
     */
    public $value;

    /**
     * @Assert\NotBlank(message="StartDate should not be empty!")
     * @GQL\Field(type="String!")
     * @var string
     */
    public $startDate;

    /**
     * @Assert\NotBlank(message="EndDate should not be empty!")
     * @GQL\Field(type="String!")
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
