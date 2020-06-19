<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OverlapPeriods extends Constraint
{
    public $message = 'Au moins deux périodes se supperposent. Veuillez vérifier les dates';
}