<?php declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Annotation as GQL;
use Pagerfanta\Pagerfanta;

/**
 * @GQL\Type()
 *
 * Class PaginatedResult
 * @package App\GraphQL\Types
 */
class PaginatedResult
{
    /**
     * @var array|\Traversable
     */
    public $data = [];

    /**
     * @GQL\Field(type="Int!")
     * @var int
     */
    public $currentPage;

    /**
     * @GQL\Field(type="Int!")
     * @var int
     */
    public $totalPages;

    /**
     * @GQL\Field(type="Int!")
     * @var int
     */
    public $totalResults;

    /**
     * @GQL\Field(type="Boolean!")
     * @var bool
     */
    public $hasNextPage;

    /**
     * @GQL\Field(type="Boolean!")
     * @var bool
     */
    public $hasPrevPage;

    /**
     * Creates new instance from a pager.
     *
     * @param Pagerfanta $pager
     * @param array|null $pagerData
     *
     * @return mixed
     */
    public static function fromPager(Pagerfanta $pager, ?array $pagerData = null)
    {
        $result = new static();

        if (!empty($pagerData)) {
            $result->data = $pagerData;
        } else {
            $result->data = $pager->getCurrentPageResults();
        }

        $result->currentPage = $pager->getCurrentPage();
        $result->totalPages = $pager->getNbPages();
        $result->hasNextPage = $pager->hasNextPage();
        $result->hasPrevPage = $pager->hasPreviousPage();
        $result->totalResults = $pager->getNbResults();

        return $result;
    }
}
