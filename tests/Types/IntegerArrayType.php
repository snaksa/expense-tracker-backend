<?php
namespace App\Tests\Types;
use KunicMarko\GraphQLTest\Type\TypeInterface;

final class IntegerArrayType implements TypeInterface
{
    /**
     * @var array
     */
    private $value;

    public function __construct(array $value = [])
    {
        $this->value = $value;
    }

    public function __invoke($identifier): string
    {
        if (empty($this->value)) {
            return sprintf('%s: []', $identifier);
        }

        return sprintf('%s: [%s]', $identifier, implode(',', $this->value));
    }
}
