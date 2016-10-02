<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\UserLoggedIn;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Intercom\Client\IntercomException;
use ContinuousPipe\Authenticator\Intercom\Normalizer\UserNormalizer;
use ContinuousPipe\Security\User\SecurityUser;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param IntercomClient $intercomClient
     * @param UserNormalizer $userNormalizer
     * @param LoggerInterface $logger
     */
    public function __construct(IntercomClient $intercomClient, UserNormalizer $userNormalizer, LoggerInterface $logger)
    {
        $this->intercomClient = $intercomClient;
        $this->userNormalizer = $userNormalizer;
        $this->logger = $logger;
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

        try {
            $this->intercomClient->createOrUpdateUser(
                $this->userNormalizer->normalize(
                    $securityUser->getUser()
                )
            );
        } catch (IntercomException $e) {
            $this->logger->warning('Unable to update the user after login', [
                'username' => $securityUser->getUsername(),
                'exception' => $e,
            ]);
        }
    }
}
