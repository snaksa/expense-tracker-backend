<?php declare(strict_types=1);

namespace App\GraphQL\Input\Category;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class CategoryDeleteRequest extends CategoryRequest
{
    /**
     * @GQL\Field(type="Int!")
     * @var integer
     */
    public $id;
}
