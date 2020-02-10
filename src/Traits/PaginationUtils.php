<?php declare(strict_types=1);

namespace App\Traits;

use App\Constants\Pagination as Pagination;
use Overblog\GraphQLBundle\Annotation as GQL;

trait PaginationUtils
{
    /**
     * @GQL\Field(type="Int")
     */
    public $limit;

    /**
     * @GQL\Field(type="Int")
     */
    public $page;

    /**
     * @GQL\Field(type="Boolean")
     */
    public $unlimited = false;

    /**
     * @return int
     */
    public function getPage(): int
    {
        $page = $this->page ?? Pagination::DEFAULT_PAGE;

        if ($page < 1) {
            $page = Pagination::DEFAULT_PAGE;
        }

        return $page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        $limit = $this->limit ?? Pagination::DEFAULT_PAGE_LIMIT;

        if ($limit < 1) {
            $limit = Pagination::DEFAULT_PAGE_LIMIT;
        }

        return $limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->getPage() - 1) * $this->getLimit();
    }

    /**
     * @return bool
     */
    public function getUnlimited(): bool
    {
        return $this->unlimited;
    }
}
