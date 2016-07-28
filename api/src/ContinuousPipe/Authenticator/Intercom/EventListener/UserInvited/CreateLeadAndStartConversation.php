<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\UserInvited;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Invitation\Event\UserInvited;
use ContinuousPipe\Security\Team\TeamRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;

class CreateLeadAndStartConversation implements EventSubscriberInterface
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var EngineInterface
     */
    private $templatingEngine;

    /**
     * @param IntercomClient  $intercomClient
     * @param TeamRepository  $teamRepository
     * @param EngineInterface $templatingEngine
     */
    public function __construct(IntercomClient $intercomClient, TeamRepository $teamRepository, EngineInterface $templatingEngine)
    {
        $this->intercomClient = $intercomClient;
        $this->teamRepository = $teamRepository;
        $this->templatingEngine = $templatingEngine;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserInvited::EVENT_NAME => 'onUserInvited',
        ];
    }

    /**
     * @param UserInvited $event
     */
    public function onUserInvited(UserInvited $event)
    {
        $invitation = $event->getInvitation();
        $team = $this->teamRepository->find($invitation->getTeamSlug());

        $lead = $this->intercomClient->createLead([
            'email' => $invitation->getUserEmail(),
            'companies' => [
                [
                    'company_id' => $team->getSlug(),
                    'name' => $team->getName(),
                ],
            ],
        ]);

        $this->intercomClient->message([
            'message_type' => 'email',
            'subject' => sprintf('You\'ve been invited to the team "%s"', $team->getName()),
            'template' => 'personal',
            'body' => $this->templatingEngine->render('@intercom/user_invited.html.twig', [
                'team' => $team,
                'invitation' => $invitation,
            ]),
            'to' => [
                'type' => 'contact',
                'id' => $lead['id'],
            ],
        ]);
    }
}
