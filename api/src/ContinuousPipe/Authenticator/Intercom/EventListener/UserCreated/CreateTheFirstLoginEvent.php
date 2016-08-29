<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\UserCreated;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Intercom\Normalizer\UserNormalizer;
use ContinuousPipe\Authenticator\Security\Event\UserCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateTheFirstLoginEvent implements EventSubscriberInterface
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
            UserCreated::EVENT_NAME => 'onUserCreated',
        ];
    }

    /**
     * @param UserCreated $event
     */
    public function onUserCreated(UserCreated $event)
    {
        $user = $event->getUser();
        $normalizedUser = $this->userNormalizer->normalize($user);

        try {
            $this->intercomClient->mergeLeadIfExists(
                ['email' => $user->getEmail()],
                $normalizedUser
            );
        } catch (\Exception $e) {
            // We don't care if the lead is not really merged.
        }

        $this->intercomClient->createOrUpdateUser($normalizedUser);
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
