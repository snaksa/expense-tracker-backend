<?php declare(strict_types=1);

namespace App\GraphQL\Types;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 */
class Currency
{
    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $id = '';

    /**
     * @GQL\Field(type="String!")
     * @var string
     */
    public $value = '';

    /**
     * Currency constructor.
     * @param string $id
     * @param string $value
     */
    public function __construct(string $id = '', string $value = '')
    {
        $this->id = $id;
        $this->value = $value;
    }
}
