<?php

namespace ContinuousPipe\Billing\Infrastructure\Doctrine\Entity;

use ContinuousPipe\Message\UserActivity as UserActivityMessage;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UserActivity
{
    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @var integer
     */
    private $eventCount = 0;

    /**
     * @var UserActivityMessage
     */
    private $userActivity;

    public function __construct(UserActivityMessage $userActivity)
    {
        $key = sprintf(
            '%s_%s_%s_%s',
            $userActivity->getTeamSlug(),
            $userActivity->getFlowUuid(),
            $userActivity->getUser()->getUsername(),
            $userActivity->getDateTime()->format('Y-m-d')
        );

        $this->id = Uuid::uuid5(Uuid::NAMESPACE_OID, $key);
        $this->userActivity = $userActivity;
    }

    /**
     * @return UserActivityMessage
     */
    public function getUserActivity(): UserActivityMessage
    {
        return $this->userActivity;
    }

    public function initializeCounterOnPersist(LifecycleEventArgs $args)
    {
        /** @var UserActivity $userActivity */
        if (($userActivity = $args->getObject()) instanceof self) {
            $userActivity->eventCount = 1;
        }
    }

    public function incrementCounterOnUpdate(PreUpdateEventArgs $args)
    {
        /** @var UserActivity $userActivity */
        if (($userActivity = $args->getObject()) instanceof self) {
            $userActivity->eventCount = $args->getOldValue('eventCount') + 1;
        }
    }

    /**
     * @return int
     */
    public function getEventCount(): int
    {
        return $this->eventCount;
    }
}
