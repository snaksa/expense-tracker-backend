<?php declare(strict_types=1);

namespace App\GraphQL\Input\Budget;

use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class BudgetDeleteRequest extends BudgetRequest
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
}
