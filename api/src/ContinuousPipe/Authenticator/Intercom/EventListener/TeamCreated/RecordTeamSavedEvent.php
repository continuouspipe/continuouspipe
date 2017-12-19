<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\TeamCreated;

use ContinuousPipe\Authenticator\Event\TeamCreationEvent;
use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RecordTeamSavedEvent implements EventSubscriberInterface
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;

    /**
     * @param IntercomClient $intercomClient
     */
    public function __construct(IntercomClient $intercomClient)
    {
        $this->intercomClient = $intercomClient;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TeamCreationEvent::AFTER_EVENT => 'onTeamCreation',
        ];
    }

    /**
     * @param TeamCreationEvent $event
     */
    public function onTeamCreation(TeamCreationEvent $event)
    {
        $team = $event->getTeam();

        $this->intercomClient->createEvent([
            'event_name' => 'created-team',
            'user_id' => $event->getCreator()->getUsername(),
            'metadata' => [
                'team_slug' => $team->getSlug(),
                'team_name' => $team->getName(),
            ],
        ]);
    }
}
