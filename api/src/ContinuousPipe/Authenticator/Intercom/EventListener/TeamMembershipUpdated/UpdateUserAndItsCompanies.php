<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\TeamMembershipUpdated;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Intercom\Normalizer\UserNormalizer;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipEvent;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipRemoved;
use ContinuousPipe\Authenticator\TeamMembership\Event\TeamMembershipSaved;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateUserAndItsCompanies implements EventSubscriberInterface
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
            TeamMembershipRemoved::EVENT_NAME => 'onTeamMembershipUpdated',
            TeamMembershipSaved::EVENT_NAME => 'onTeamMembershipUpdated',
        ];
    }

    /**
     * @param TeamMembershipEvent $event
     */
    public function onTeamMembershipUpdated(TeamMembershipEvent $event)
    {
        $this->intercomClient->createOrUpdateUser(
            $this->userNormalizer->normalize(
                $event->getTeamMembership()->getUser()
            )
        );
    }
}
