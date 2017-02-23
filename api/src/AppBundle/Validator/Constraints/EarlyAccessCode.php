<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EarlyAccessCode extends Constraint
{
    public $message = 'Early access code "%code%" does not exist or already used.';
}
