<?php declare(strict_types=1);

namespace App\GraphQL\Input\Category;

use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class CategoryDeleteRequest extends CategoryRequest
{
    /**
     * @GQL\Field(type="Int!")
     * @Assert\EntityExists(
     *  message="Category not found!",
     *  entityClass="App\Entity\Category"
     * )
     * @var integer
     */
    public $id;
}
