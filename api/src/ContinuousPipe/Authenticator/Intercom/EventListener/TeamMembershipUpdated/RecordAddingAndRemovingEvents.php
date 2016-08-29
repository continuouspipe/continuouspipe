<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\TeamMembershipUpdated;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipEvent;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipRemoved;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipSaved;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RecordAddingAndRemovingEvents implements EventSubscriberInterface
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;
    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    /**
     * @param IntercomClient           $intercomClient
     * @param TeamMembershipRepository $teamMembershipRepository
     */
    public function __construct(IntercomClient $intercomClient, TeamMembershipRepository $teamMembershipRepository)
    {
        $this->intercomClient = $intercomClient;
        $this->teamMembershipRepository = $teamMembershipRepository;
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

        $numberOfMemberships = $this->teamMembershipRepository->findByTeam($team);
        if (count($numberOfMemberships) <= 1) {
            // Don't create the added-to-team event if it's after a creation, probably
            return;
        }

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
