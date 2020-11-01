<?php declare(strict_types=1);

namespace App\GraphQL\Loader;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use GraphQL\Executor\Promise\PromiseAdapter;

abstract class BaseLoader
{

    protected $promiseAdapter;

    protected $repository;

    /**
     * BaseLoader constructor.
     *
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository $repository
     */
    public function __construct(PromiseAdapter $promiseAdapter, ServiceEntityRepository $repository)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    /**
     * @param array $ids
     * @param string $column
     *
     * @return array
     */
    protected function fetch(array $ids, string $column): array
    {
        $qb = $this->repository->createQueryBuilder('s');
        $qb->add('where', $qb->expr()->in('s.' . $column, ':ids'));
        $qb->setParameter('ids', $ids);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $ids
     * @param string $column
     *
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function all(array $ids, $column = 'id')
    {
        $indexedResults = $this->reindex($this->fetch($ids, $column), function ($item) use ($column) {
            $methodName = "get".ucfirst($column);
            return $item->$methodName();
        });

        $results = [];
        foreach ($ids as $id) {
            if (isset($indexedResults[$id])) {
                $results[] = $indexedResults[$id];
            } else {
                $results[] = null;
            }
        }

        return $this->promiseAdapter->all($results);
    }

//    /**
//     * Returns array of objects grouped by a given key.
//     *
//     * @see https://github.com/overblog/dataloader-php/blob/master/README.md#batch-function
//     *
//     * @param array $ids
//     * @param string $column
//     * @param callable $predicate
//     * @return \GraphQL\Executor\Promise\Promise
//     */
//    public function allGrouped(array $ids, string $column, callable $predicate)
//    {
//        $records = $this->fetch($ids, $column);
//
//        $grouped = $this->groupBy($predicate, $records);
//
//        $result = array_map(function ($id) use ($grouped) {
//            return $grouped[$id] ?? [];
//        }, $ids);
//
//        return $this->promiseAdapter->all($result);
//    }

    public function reindex(array $array, callable $callback): array
    {
        $result = [];

        foreach ($array as $item) {
            $key = $callback($item);
            $result[$key] = $item;
        }

        return $result;
    }

//    /**
//     * Groups array values by a given value.
//     *
//     * @param string|callable $predicate
//     * @param array $collection
//     *
//     * @return array
//     */
//    protected function groupBy($predicate, array $collection): array
//    {
//        $groupedArray = [];
//
//        foreach ($collection as $item) {
//            $key = is_callable($predicate) ? $predicate($item) : $predicate;
//
//            if (!isset($groupedArray[$key])) {
//                $groupedArray[$key] = [];
//            }
//
//            $groupedArray[$key][] = $item;
//        }
//
//        return $groupedArray;
//    }
}
