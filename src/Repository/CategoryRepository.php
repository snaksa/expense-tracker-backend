<?php declare (strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @param User $user
     * @return Category[]
     */
    public function findUserCategories(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.user_id = :userId OR t.user_id IS NULL')
            ->setParameters(['userId' => $user->getId()])
            ->getQuery()
            ->getResult();
    }

    public function remove(Category $category)
    {
        $this->_em->remove($category);
        $this->_em->flush();
    }

    public function save(Category $category)
    {
        $this->_em->persist($category);
        $this->_em->flush();
    }
}
