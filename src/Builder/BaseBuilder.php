<?php

namespace App\Builder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;

class BaseBuilder
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     * @param string $class
     * @return mixed
     * @throws EntityNotFoundException
     */
    protected function findEntity(int $id, $class)
    {
        $entity = $this->entityManager->getRepository($class)->find($id);
        if ($entity === null) {
            throw EntityNotFoundException::fromClassNameAndIdentifier($class, [(string)$id]);
        }

        return $entity;
    }

    /**
     * @param int[] $ids
     * @param string $class
     * @return array
     */
    protected function findByIds(array $ids, string $class)
    {
        /**
         * @var EntityRepository $repository
         */
        $repository = $this->entityManager->getRepository($class);

        return $repository
            ->createQueryBuilder('t')
            ->where('t.id IN (:ids)')
            ->setParameters(['ids' => $ids])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param object $item
     */
    protected function remove($item)
    {
        $this->entityManager->remove($item);
    }
}
