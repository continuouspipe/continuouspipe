<?php

namespace ContinuousPipe\Authenticator\EventListener\AfterUserCreation;

use ContinuousPipe\Authenticator\Security\Event\UserCreated;
use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserIsAdminIfIsTheFirstUser implements EventSubscriberInterface
{
    private $securityUserRepository;

    public function __construct(SecurityUserRepository $securityUserRepository)
    {
        $this->securityUserRepository = $securityUserRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserCreated::EVENT_NAME => 'userCreated',
        ];
    }

    public function userCreated(UserCreated $event)
    {
        if ($this->securityUserRepository->count() > 1) {
            return;
        }

        $securityUser = $this->securityUserRepository->findOneByUsername($event->getUser()->getUsername());
        $securityUser->getUser()->setRoles(array_merge($securityUser->getUser()->getRoles(), [
            'ROLE_ADMIN'
        ]));

        $this->securityUserRepository->save($securityUser);
    }
}
