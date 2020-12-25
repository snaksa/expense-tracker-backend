<?php declare (strict_types=1);

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @method Budget|null find($id, $lockMode = null, $lockVersion = null)
 * @method Budget|null findOneBy(array $criteria, array $orderBy = null)
 * @method Budget[]    findAll()
 * @method Budget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    /**
     * @param User $user
     * @return Budget[]
     */
    public function findUserBudgets(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.user_id = :userId')
            ->setParameters(['userId' => $user->getId()])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @return Budget|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById(int $id): ?Budget
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameters(['id' => $id])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Budget $budget
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Budget $budget): void
    {
        $this->_em->persist($budget);
        $this->_em->flush();
    }

    /**
     * @param Budget $budget
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Budget $budget): void
    {
        $this->_em->remove($budget);
        $this->_em->flush();
    }
}
