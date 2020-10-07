<?php

namespace App\Repository;

use App\Entity\Label;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Label|null find($id, $lockMode = null, $lockVersion = null)
 * @method Label|null findOneBy(array $criteria, array $orderBy = null)
 * @method Label[]    findAll()
 * @method Label[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Label::class);
    }

    /**
     * @param User $user
     * @return Label[]
     */
    public function findUserLabels(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.user_id = :userId')
            ->setParameters(['userId' => $user->getId()])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @return Label|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById(int $id): ?Label
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameters(['id' => $id])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Label $label
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Label $label): void
    {
        $this->_em->persist($label);
        $this->_em->flush();
    }

    /**
     * @param Label $label
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Label $label): void
    {
        $this->_em->remove($label);
        $this->_em->flush();
    }
}
