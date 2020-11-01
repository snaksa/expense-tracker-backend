<?php declare(strict_types=1);

namespace App\GraphQL\Loader;

use App\Repository\CategoryRepository;
use GraphQL\Executor\Promise\PromiseAdapter;

/**
 *
 * Class CategoryLoader
 * @package App\GraphQL\Loader
 */
class CategoryLoader extends BaseLoader
{
    /**
     *
     * CategoryLoader constructor.
     *
     * @param PromiseAdapter $promiseAdapter
     * @param CategoryRepository $repository
     */
    public function __construct(PromiseAdapter $promiseAdapter, CategoryRepository $repository)
    {
        parent::__construct($promiseAdapter, $repository);
    }
}
