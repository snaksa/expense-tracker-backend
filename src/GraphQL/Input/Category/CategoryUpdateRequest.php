<?php declare(strict_types=1);

namespace App\GraphQL\Input\Category;

use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class CategoryUpdateRequest extends CategoryRequest
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

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $name;

    /**
     * @GQL\Field(type="String")
     * @var string
     */
    public $color;

    /**
     * @GQL\Field(type="Int")
     * @var integer
     */
    public $icon;
}
