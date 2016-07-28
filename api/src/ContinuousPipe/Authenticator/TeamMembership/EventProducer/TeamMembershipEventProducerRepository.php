<?php

namespace ContinuousPipe\Authenticator\TeamMembership\EventProducer;

use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipRemoved;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipSaved;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\User\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TeamMembershipEventProducerRepository implements TeamMembershipRepository
{
    /**
     * @var TeamMembershipRepository
     */
    private $decoratedRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param TeamMembershipRepository $decoratedRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(TeamMembershipRepository $decoratedRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->decoratedRepository = $decoratedRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user)
    {
        return $this->decoratedRepository->findByUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        return $this->decoratedRepository->findByTeam($team);
    }

    /**
     * {@inheritdoc}
     */
    public function save(TeamMembership $membership)
    {
        $this->decoratedRepository->save($membership);

        $this->eventDispatcher->dispatch(TeamMembershipSaved::EVENT_NAME, new TeamMembershipSaved($membership));
    }

    /**
     * {@inheritdoc}
     */
    public function remove(TeamMembership $membership)
    {
        $this->decoratedRepository->remove($membership);

        $this->eventDispatcher->dispatch(TeamMembershipRemoved::EVENT_NAME, new TeamMembershipRemoved($membership));
    }
}
