<?php declare (strict_types=1);

namespace App\Repository;

use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Wallet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wallet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wallet[]    findAll()
 * @method Wallet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * @param array $ids
     * @return Wallet[]
     */
    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.id IN (:ids)')
            ->setParameters(['ids' => $ids])
            ->getQuery()
            ->getResult();
    }

    public function remove(Wallet $wallet)
    {
        $this->_em->remove($wallet);
        $this->_em->flush();
    }

    public function save(Wallet $wallet)
    {
        $this->_em->persist($wallet);
        $this->_em->flush();
    }
}
