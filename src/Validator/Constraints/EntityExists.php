<?php declare (strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EntityExists extends Constraint
{
    public $entityClass = null;
    public $idField = 'id';
    public $message = "Entity doesn't exist";
    public $allowNull = false;
}
