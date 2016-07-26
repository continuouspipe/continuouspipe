<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\UserLoggedIn;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class CreateOrUpdateUser implements EventSubscriberInterface
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
            SecurityEvents::INTERACTIVE_LOGIN => 'onUserLoggedIn',
        ];
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onUserLoggedIn(InteractiveLoginEvent $event)
    {
        $securityUser = $event->getAuthenticationToken()->getUser();
        if (!$securityUser instanceof SecurityUser) {
            return;
        }

        $user = $securityUser->getUser();
        $this->intercomClient->createOrUpdateUser([
            'user_id' => $user->getUsername(),
            'email' => $user->getEmail(),
            'name' => $user->getUsername(),
            'new_session' => true,
        ]);
    }
}
