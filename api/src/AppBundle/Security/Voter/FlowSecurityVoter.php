<?php

namespace AppBundle\Security\Voter;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembershipFinder;
use ContinuousPipe\Security\User\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FlowSecurityVoter extends Voter
{
    const ATTRIBUTE_READ = 'READ';
    const ATTRIBUTE_DELETE = 'DELETE';
    const ATTRIBUTE_UPDATE = 'UPDATE';
    const ATTRIBUTE_CREATE_TIDE = 'CREATE_TIDE';

    /**
     * @var int
     */
    private $lifetime;

    /**
     * @var TeamMembershipFinder
     */
    private $membershipFinder;

    /**
     * @param TeamMembershipFinder $membershipFinder
     * @param int $lifetime
     */
    public function __construct(TeamMembershipFinder $membershipFinder, $lifetime = 1600)
    {
        $this->membershipFinder = $membershipFinder;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Flow || $subject instanceof Tide || $subject instanceof Flow\Projections\FlatFlow;
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
        if (null === ($membership = $this->membershipFinder->findOneByTeamAndUser($team, $user->getUser()))) {
            return false;
        } elseif (in_array($attribute, ['READ', 'CREATE_TIDE'])) {
            return true;
        }

        return in_array('ADMIN', $membership->getPermissions());
    }

    /**
     * @param mixed $subject
     *
     * @return Team
     */
    private function extractTeam($subject)
    {
        if ($subject instanceof Flow || $subject instanceof Tide || $subject instanceof Flow\Projections\FlatFlow) {
            return $subject->getTeam();
        }

        throw new \InvalidArgumentException(sprintf('Unable to extract the project from %s', get_class($subject)));
    }
}
