<?php declare(strict_types=1);

namespace App\Validator\Constraints;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class EntityExistsValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param mixed $value
     * @param EntityExists|Constraint $constraint
     *
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof EntityExists) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\EntityExists');
        }

        if ($constraint->allowNull && $value === null) {
            return;
        }

        /** @var ServiceEntityRepository $repository */
        $repository = $this->em->getRepository($constraint->entityClass);

        $entity = $repository->find($value);

        if (!$entity) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
