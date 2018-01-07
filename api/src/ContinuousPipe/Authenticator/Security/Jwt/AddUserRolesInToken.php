<?php

namespace ContinuousPipe\Authenticator\Security\Jwt;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AddUserRolesInToken
{
    public function onJwtCreated(JWTCreatedEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $data['roles'] = $user->getRoles();

        $event->setData($data);
    }
}
