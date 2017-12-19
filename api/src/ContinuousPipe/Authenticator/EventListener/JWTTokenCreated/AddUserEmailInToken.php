<?php

namespace ContinuousPipe\Authenticator\EventListener\JWTTokenCreated;

use ContinuousPipe\Security\User\SecurityUser;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class AddUserEmailInToken
{
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();
        $user = $event->getUser();

        if ($user instanceof SecurityUser) {
            $payload['email'] = $user->getUser()->getEmail();
        }

        $event->setData($payload);
    }
}
