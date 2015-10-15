<?php

namespace ContinuousPipe\Authenticator\EventListener\BeforeTeamCreation;

use ContinuousPipe\Authenticator\Event\BeforeTeamCreation;
use ContinuousPipe\Security\Team\Team;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateCredentialsBucket implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeTeamCreation::EVENT_NAME => 'createBucketIfNotExists',
        ];
    }

    /**
     * @param BeforeTeamCreation $event
     *
     * @return Team
     */
    public function createBucketIfNotExists(BeforeTeamCreation $event)
    {
        $team = $event->getTeam();

        if (null === $team->getBucketUuid()) {
            $team->setBucketUuid(Uuid::uuid1());
        }

        return $team;
    }
}
