<?php declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Language\AST\Node;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Scalar(name="DateTime")
 * @GQL\Description("Datetime scalar")
 */
class DateTime
{
    /**
     * @param \DateTimeInterface $value
     *
     * @return string
     */
    public static function serialize(\DateTimeInterface $value)
    {
        return $value->format('Y-m-d H:i:s');
    }

    /**
     * @param mixed $value
     *
     * @return \DateTimeInterface
     * @throws \Exception
     */
    public static function parseValue($value)
    {
        return new \DateTimeImmutable($value);
    }

    /**
     * @param Node $valueNode
     *
     * @return \DateTimeInterface
     * @throws \Exception
     */
    public static function parseLiteral(Node $valueNode)
    {
        return new \DateTimeImmutable((string)$valueNode);
    }
}
