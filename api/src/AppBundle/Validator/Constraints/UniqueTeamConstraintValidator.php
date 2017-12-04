<?php

namespace AppBundle\Validator\Constraints;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueTeamConstraintValidator extends ConstraintValidator
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @param TeamRepository $teamRepository
     */
    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function validate($team, Constraint $constraint)
    {
        if (!$team instanceof Team) {
            throw new \InvalidArgumentException('Expected a `Team` object');
        }

        if ($this->teamRepository->exists($team->getSlug())) {
            $this->context->buildViolation($constraint->message)
                ->atPath('slug')
                ->setParameter('%slug%', $team->getSlug())
                ->addViolation();
        }
    }
}
