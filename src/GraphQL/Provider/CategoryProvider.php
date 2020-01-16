<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Provider
 *
 * @package App\GraphQL\Provider
 */
class CategoryProvider
{
    /**
     * @GQL\Query(type="Int")
     *
     * @param int $id
     *
     * @return int
     */
    public function category(int $id): int
    {
        return 1;
    }

    /**
     * @GQL\Mutation(type="Int")
     *
     * @param int $id
     *
     * @return int
     */
    public function createCategory(int $id): int
    {
        return 1;
    }
}
