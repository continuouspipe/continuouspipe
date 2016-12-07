<?php

namespace AppBundle\Security\Voter;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FlowSecurityVoter extends Voter
{
    const ATTRIBUTE_READ = 'READ';
    const ATTRIBUTE_DELETE = 'DELETE';
    const ATTRIBUTE_UPDATE = 'UPDATE';
    const ATTRIBUTE_CREATE_TIDE = 'CREATE_TIDE';

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

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Flow || $subject instanceof Tide;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $team = $this->extractTeam($subject);
        $user = $token->getUser();

        if (!$user instanceof SecurityUser) {
            return false;
        }

        // Reload the memberships of the team
        if (null === ($membership = $this->getUserMembership($team, $user->getUser()))) {
            return false;
        } elseif (in_array($attribute, ['READ', 'CREATE_TIDE'])) {
            return true;
        }

        return in_array('ADMIN', $membership->getPermissions());
    }

    /**
     * @param Team $team
     * @param User $user
     *
     * @return TeamMembership|null
     */
    private function getUserMembership(Team $team, User $user)
    {
        // Reload the team has it has bean serialized in the flow context
        $team = $this->teamRepository->find($team->getSlug());

        foreach ($team->getMemberships() as $teamMembership) {
            if ($teamMembership->getUser()->getUsername() == $user->getUsername()) {
                return $teamMembership;
            }
        }

        return;
    }

    /**
     * @param mixed $subject
     *
     * @return Team
     */
    private function extractTeam($subject)
    {
        if ($subject instanceof Flow || $subject instanceof Tide) {
            return $subject->getTeam();
        }

        throw new \InvalidArgumentException(sprintf('Unable to extract the team from %s', get_class($subject)));
    }
}
