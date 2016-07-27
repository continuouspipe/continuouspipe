<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\TeamMembershipUpdated;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Intercom\Normalizer\UserNormalizer;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipEvent;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipRemoved;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipSaved;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RecordAddingAndRemovingEvents implements EventSubscriberInterface
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
            TeamMembershipSaved::EVENT_NAME => 'onTeamMembershipSaved',
            TeamMembershipRemoved::EVENT_NAME => 'onTeamMembershipRemoved',
        ];
    }

    /**
     * @param TeamMembershipEvent $event
     */
    public function onTeamMembershipSaved(TeamMembershipEvent $event)
    {
        $membership = $event->getTeamMembership();
        $user = $membership->getUser();
        $team = $membership->getTeam();

        $this->intercomClient->createEvent([
            'event_name' => 'added-to-team',
            'user_id' => $user->getUsername(),
            'metadata' => [
                'team_slug' => $team->getSlug(),
                'team_name' => $team->getName(),
                'as_administrator' => in_array('ADMIN', $membership->getPermissions()),
            ],
        ]);
    }

    /**
     * @param TeamMembershipEvent $event
     */
    public function onTeamMembershipRemoved(TeamMembershipEvent $event)
    {
        $membership = $event->getTeamMembership();
        $user = $membership->getUser();
        $team = $membership->getTeam();

        $this->intercomClient->createEvent([
            'event_name' => 'removed-from-team',
            'user_id' => $user->getUsername(),
            'metadata' => [
                'team_slug' => $team->getSlug(),
                'team_name' => $team->getName(),
            ],
        ]);
    }
}
