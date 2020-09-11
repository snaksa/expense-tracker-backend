<?php declare(strict_types=1);

namespace App\GraphQL\Types;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 */
class AreaChart
{
    /**
     * @GQL\Field(type="[String]!")
     * @var array
     */
    public $header = [];

    /**
     * @GQL\Field(type="[[String]]")
     * @var array
     */
    public $data = [];

    /**
     * @GQL\Field(type="[String]")
     * @var array
     */
    public $colors = [];

    public static function fromData(array $header, array $data, array $colors = []): self
    {
        $result = new self();
        $result->header = $header;
        $result->data = $data;
        $result->colors = $colors;
        return $result;
    }
}
