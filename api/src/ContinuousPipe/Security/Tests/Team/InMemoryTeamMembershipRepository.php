<?php

namespace ContinuousPipe\Security\Tests\Team;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipCollection;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\User\User;
use Doctrine\Common\Collections\ArrayCollection;

class InMemoryTeamMembershipRepository implements TeamMembershipRepository
{
    /**
     * @var TeamMembership[]|ArrayCollection
     */
    private $memberShips;

    public function __construct()
    {
        $this->memberShips = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user)
    {
        return new TeamMembershipCollection($this->memberShips->filter(function (TeamMembership $membership) use ($user) {
            return $membership->getUser()->getUsername() == $user->getUsername();
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        return new TeamMembershipCollection($this->memberShips->filter(function (TeamMembership $membership) use ($team) {
            return $membership->getTeam()->getSlug() == $team->getSlug();
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function save(TeamMembership $membership)
    {
        $this->memberShips[] = $membership;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(TeamMembership $membership)
    {
        foreach ($this->memberShips as $foundMembership) {
            if (
                $foundMembership->getUser()->getUsername() == $membership->getUser()->getUsername() &&
                $foundMembership->getTeam()->getSlug() == $membership->getTeam()->getSlug()
            ) {
                $this->memberShips->removeElement($foundMembership);
            }
        }
    }
}
