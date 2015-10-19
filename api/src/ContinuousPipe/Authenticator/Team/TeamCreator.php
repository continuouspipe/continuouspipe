<?php

namespace ContinuousPipe\Authenticator\Team;

use ContinuousPipe\Authenticator\Event\TeamCreationEvent;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\User;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TeamCreator
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var TeamMembershipRepository
     */
    private $membershipRepository;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param TeamRepository           $teamRepository
     * @param TeamMembershipRepository $membershipRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(TeamRepository $teamRepository, TeamMembershipRepository $membershipRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->teamRepository = $teamRepository;
        $this->membershipRepository = $membershipRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Create a new team.
     *
     * @param Team $team
     * @param User $owner
     *
     * @return Team
     */
    public function create(Team $team, User $owner)
    {
        $this->eventDispatcher->dispatch(TeamCreationEvent::BEFORE_EVENT, new TeamCreationEvent($team, $owner));

        $team = $this->teamRepository->save($team);
        $this->membershipRepository->save(new TeamMembership($team, $owner, [TeamMembership::PERMISSION_ADMIN]));

        $this->eventDispatcher->dispatch(TeamCreationEvent::AFTER_EVENT, new TeamCreationEvent($team, $owner));

        return $team;
    }
}
