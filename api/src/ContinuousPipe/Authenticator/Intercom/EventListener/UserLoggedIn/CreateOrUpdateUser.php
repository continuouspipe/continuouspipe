<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\UserLoggedIn;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Intercom\Normalizer\UserNormalizer;
use ContinuousPipe\Security\User\SecurityUser;
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
     * @var UserNormalizer
     */
    private $userNormalizer;

    /**
     * @param IntercomClient $intercomClient
     * @param UserNormalizer $userNormalizer
     */
    public function __construct(IntercomClient $intercomClient, UserNormalizer $userNormalizer)
    {
        $this->intercomClient = $intercomClient;
        $this->userNormalizer = $userNormalizer;
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

        $this->intercomClient->createOrUpdateUser(
            $this->userNormalizer->normalize(
                $securityUser->getUser()
            )
        );
    }
}
