<?php declare (strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EntityExists extends Constraint
{
    public string $entityClass = '';
    public string $idField = 'id';
    public string $message = "Entity doesn't exist";
    public bool $allowNull = false;
}
