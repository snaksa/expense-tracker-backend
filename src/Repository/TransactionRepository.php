<?php declare (strict_types=1);

namespace App\Repository;

use App\Entity\Transaction;
use App\GraphQL\Input\Category\CategoryRecordsRequest;
use App\GraphQL\Input\Transaction\TransactionRecordsRequest;
use App\GraphQL\Types\TransactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @param int $id
     * @return Transaction|null
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameters(['id' => $id])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCollection(TransactionRecordsRequest $filters, int $userId)
    {
        $where = [
            '(t.wallet_id IN (:walletIds) OR t.wallet_receiver_id IN (:walletIds))',
            '(w.user_id = :userId OR wr.user_id = :userId)'
        ];
        $params = [
            'walletIds' => $filters->walletIds,
            'userId' => $userId
        ];

        if ($filters->categoryIds) {
            $where[] = 't.category_id IN (:categoryIds) AND c.user_id = :userId';
            $params['categoryIds'] = $filters->categoryIds;
        }

        if ($filters->startDate) {
            $where[] = 't.date >= :startDate';
            $params['startDate'] = $filters->startDate;
        }

        if ($filters->endDate) {
            $where[] = 't.date <= :endDate';
            $params['endDate'] = $filters->endDate;
        }

        $query = $this->createQueryBuilder('t')
            ->leftJoin('t.wallet', 'w')
            ->leftJoin('t.wallet_receiver', 'wr')
            ->leftJoin('t.category', 'c')
            ->where(implode(' AND ', $where))
            ->setParameters($params)
            ->orderBy('t.date', 'DESC');

        $pager = new Pagerfanta(new DoctrineORMAdapter($query));
        $pager->setMaxPerPage($filters->getLimit());
        $pager->setCurrentPage($filters->getPage());

        return $pager;
    }

    public function findSpendingFlow(TransactionRecordsRequest $filters, int $userId)
    {
        $where = ['t.type = :type', 't.wallet_id IN (:walletIds)', 'w.user_id = :userId'];
        $params = [
            'walletIds' => $filters->walletIds,
            'type' => TransactionType::EXPENSE,
            'userId' => $userId
        ];

        if ($filters->categoryIds) {
            $where[] = 't.category_id IN (:categoryIds) AND c.user_id = :userId';
            $params['categoryIds'] = $filters->categoryIds;
        }

        if ($filters->startDate) {
            $where[] = 't.date >= :startDate';
            $params['startDate'] = $filters->startDate;
        }

        if ($filters->endDate) {
            $where[] = 't.date <= :endDate';
            $params['endDate'] = $filters->endDate;
        }

        return $this->createQueryBuilder('t')
            ->select("t.date, t.value")
            ->leftJoin('t.wallet', 'w')
            ->leftJoin('t.category', 'c')
            ->where(join(' AND ', $where))
            ->setParameters($params)
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function findCategorySpendingFlow(CategoryRecordsRequest $filters, int $userId)
    {
        $where = ['t.wallet_id IN (:walletIds)', 't.type = :type', 'w.user_id = :userId'];
        $params = [
            'walletIds' => $filters->walletIds,
            'type' => TransactionType::EXPENSE,
            'userId' => $userId
        ];

        if ($filters->categoryIds) {
            $where[] = 't.category_id IN (:categoryIds) AND c.user_id = :userId';
            $params['categoryIds'] = $filters->categoryIds;
        }

        if ($filters->startDate) {
            $where[] = 't.date >= :startDate';
            $params['startDate'] = $filters->startDate;
        }

        if ($filters->endDate) {
            $where[] = 't.date <= :endDate';
            $params['endDate'] = $filters->endDate;
        }

        return $this->createQueryBuilder('t')
            ->select("t.date, t.value, t.category_id")
            ->leftJoin('t.wallet', 'w')
            ->leftJoin('t.category', 'c')
            ->where(join(' AND ', $where))
            ->setParameters($params)
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCategorySpendingPie(CategoryRecordsRequest $filters, int $userId)
    {
        $where = ['t.wallet_id IN (:walletIds)', 'w.user_id = :userId'];
        $params = [
            'walletIds' => $filters->walletIds,
            'userId' => $userId
        ];

        if ($filters->categoryIds) {
            $where[] = 't.category_id IN (:categoryIds) AND c.user_id = :userId';
            $params['categoryIds'] = $filters->categoryIds;
        }

        if ($filters->type) {
            $where[] = 't.type = :type';
            $params['type'] = $filters->type->value;
        }

        if ($filters->startDate) {
            $where[] = 't.date >= :startDate';
            $params['startDate'] = $filters->startDate;
        }

        if ($filters->endDate) {
            $where[] = 't.date <= :endDate';
            $params['endDate'] = $filters->endDate;
        }

        return $this->createQueryBuilder('t')
            ->select("t.value, c.name as category, c.color as color")
            ->leftJoin('t.wallet', 'w')
            ->leftJoin('t.category', 'c')
            ->where(join(' AND ', $where))
            ->setParameters($params)
            ->getQuery()
            ->getResult();
    }

    public function removeByWalletId(int $walletId)
    {
        $transactions = $this->createQueryBuilder('t')
            ->where('t.wallet_id = :id OR t.wallet_receiver_id = :id')
            ->setParameters(['id' => $walletId])
            ->getQuery()
            ->getResult();

        foreach ($transactions as $transaction) {
            $this->_em->remove($transaction);
        }

        $this->_em->flush();
    }

    public function remove(Transaction $transaction)
    {
        $this->_em->remove($transaction);
        $this->_em->flush();
    }

    public function save(Transaction $transaction)
    {
        $this->_em->persist($transaction);
        $this->_em->flush();
    }
}
