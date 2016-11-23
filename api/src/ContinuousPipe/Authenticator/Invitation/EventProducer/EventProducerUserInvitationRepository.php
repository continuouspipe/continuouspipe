<?php

namespace ContinuousPipe\Authenticator\Invitation\EventProducer;

use ContinuousPipe\Authenticator\Invitation\Event\UserInvited;
use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventProducerUserInvitationRepository implements UserInvitationRepository
{
    /**
     * @var UserInvitationRepository
     */
    private $decoratedRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param UserInvitationRepository $decoratedRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(UserInvitationRepository $decoratedRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->decoratedRepository = $decoratedRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUserEmail($email)
    {
        return $this->decoratedRepository->findByUserEmail($email);
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserInvitation $userInvitation)
    {
        $invitation = $this->decoratedRepository->save($userInvitation);

        $this->eventDispatcher->dispatch(UserInvited::EVENT_NAME, new UserInvited($invitation));

        return $invitation;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UserInvitation $invitation)
    {
        return $this->decoratedRepository->delete($invitation);
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
    public function findByUuid(UuidInterface $uuid)
    {
        return $this->decoratedRepository->findByUuid($uuid);
    }
}
