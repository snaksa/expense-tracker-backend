<?php declare (strict_types=1);

namespace App\Builder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

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
}
