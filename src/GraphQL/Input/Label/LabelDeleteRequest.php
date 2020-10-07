<?php declare(strict_types=1);

namespace App\GraphQL\Input\Label;

use App\GraphQL\Input\Label\LabelRequest;
use App\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Input
 */
class LabelDeleteRequest extends LabelRequest
{
    /**
     * @GQL\Field(type="Int!")
     * @Assert\EntityExists(
     *  message="Label not found!",
     *  entityClass="App\Entity\Label"
     * )
     * @var integer
     */
    public $id;
}
