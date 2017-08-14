<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\TeamMembershipUpdated;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Intercom\Client\IntercomException;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipEvent;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipRemoved;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipSaved;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;

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
     * @var EngineInterface
     */
    private $templatingEngine;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param IntercomClient $intercomClient
     * @param TeamMembershipRepository $teamMembershipRepository
     * @param EngineInterface $templatingEngine
     */
    public function __construct(
        IntercomClient $intercomClient,
        TeamMembershipRepository $teamMembershipRepository,
        EngineInterface $templatingEngine,
        LoggerInterface $logger
    ) {
        $this->intercomClient = $intercomClient;
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->templatingEngine = $templatingEngine;
        $this->logger = $logger;
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

        try {
            $this->intercomClient->message([
                'message_type' => 'email',
                'subject' => sprintf('You\'ve been added to the team "%s"', $team->getName()),
                'template' => 'personal',
                'body' => $this->templatingEngine->render(
                    '@intercom/user_added.html.twig',
                    [
                        'team' => $team,
                        'membership' => $membership,
                    ]
                ),
                'to' => [
                    'type' => 'user',
                    'email' => $user->getEmail(),
                ],
            ]);
        } catch (IntercomException $e) {
            $this->logger->warning('Unable to send Intercom email', [
                'team' => $team->getName(),
                'user' => $user->getUsername(),
                'email' => $user->getEmail(),
                'exception' => $e,
            ]);
        }
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
