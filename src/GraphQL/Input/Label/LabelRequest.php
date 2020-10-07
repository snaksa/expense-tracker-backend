<?php declare(strict_types=1);

namespace App\GraphQL\Input\Label;

use Overblog\GraphQLBundle\Annotation as GQL;

abstract class LabelRequest
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $name;
}
