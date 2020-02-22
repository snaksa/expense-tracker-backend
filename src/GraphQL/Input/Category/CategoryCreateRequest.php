<?php declare(strict_types=1);

namespace App\GraphQL\Input\Category;

use Symfony\Component\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class CategoryCreateRequest extends CategoryRequest
{
    /**
     * @Assert\NotBlank(message="Name should not be empty!")
     * @GQL\Field(type="String!")
     * @var string
     */
    public $name;

    /**
     * @Assert\NotBlank(message="Color should not be empty!")
     * @GQL\Field(type="String!")
     * @var string
     */
    public $color;
}
