<?php

namespace ContinuousPipe\Authenticator\Intercom\Invitation\UserInvited;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Invitation\Event\UserInvited;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateLead implements EventSubscriberInterface
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
            UserInvited::EVENT_NAME => 'onUserInvited',
        ];
    }

    /**
     * @param UserInvited $event
     */
    public function onUserInvited(UserInvited $event)
    {
        $invitation = $event->getInvitation();

        $this->intercomClient->createLead([
            'email' => $invitation->getUserEmail(),
            'companies' => [
                [
                    'company_id' => $invitation->getTeamSlug(),
                ],
            ],
        ]);
    }
}
