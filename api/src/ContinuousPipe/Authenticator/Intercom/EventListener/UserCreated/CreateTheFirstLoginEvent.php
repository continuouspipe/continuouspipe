<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\UserCreated;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Security\Event\UserCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateTheFirstLoginEvent implements EventSubscriberInterface
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
            UserCreated::EVENT_NAME => 'onUserCreated',
        ];
    }

    /**
     * @param UserCreated $event
     */
    public function onUserCreated(UserCreated $event)
    {
        $user = $event->getUser();

        $this->intercomClient->createEvent([
            'event_name' => 'first-login',
            'user_id' => $user->getUsername(),
            'metadata' => [
                'username' => $user->getUsername(),
                'email' => $user->getUsername(),
            ],
        ]);
    }
}
