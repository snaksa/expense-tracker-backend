<?php declare(strict_types=1);

namespace App\GraphQL\Input\Category;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class CategoryCreateRequest extends CategoryRequest
{
    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $name;

    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $color;

    /**
     * @GQL\Field(type="Int!")
     * @var integer
     */
    public $icon;
}
