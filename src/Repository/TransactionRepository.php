<?php declare (strict_types=1);

namespace App\Repository;

use App\Entity\Transaction;
use App\GraphQL\Input\Transaction\TransactionRecordsRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
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

    public function findCollection(TransactionRecordsRequest $filters)
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.wallet_id IN (:ids)')
            ->setParameters(['ids' => $filters->walletIds])
            ->orderBy('t.date', 'DESC');

        if ($filters->getUnlimited()) {
            return $query->getQuery()->getResult();
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($query));
        $pager->setMaxPerPage($filters->getLimit());
        $pager->setCurrentPage($filters->getPage());

        return $pager;
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
