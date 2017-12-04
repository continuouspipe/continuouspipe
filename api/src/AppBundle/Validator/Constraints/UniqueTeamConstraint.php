<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueTeamConstraint extends Constraint
{
    public $message = 'The project "%slug%" already exists.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'unique_team';
    }
}
