<?php

namespace ContinuousPipe\Authenticator\EventListener\AfterTeamCreation;

use ContinuousPipe\Authenticator\Event\TeamCreationEvent;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CopyCreatorCredentials implements EventSubscriberInterface
{
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param BucketRepository $bucketRepository
     */
    public function __construct(BucketRepository $bucketRepository)
    {
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TeamCreationEvent::AFTER_EVENT => 'copyCreatorCredentialsInTeamBucket',
        ];
    }

    /**
     * @param TeamCreationEvent $event
     */
    public function copyCreatorCredentialsInTeamBucket(TeamCreationEvent $event)
    {
        $teamBucket = $this->bucketRepository->find($event->getTeam()->getBucketUuid());
        $creatorBucket = $this->bucketRepository->find($event->getCreator()->getBucketUuid());

        foreach ($creatorBucket->getGitHubTokens() as $token) {
            $teamBucket->getGitHubTokens()->add($token);
        }

        $this->bucketRepository->save($teamBucket);
    }
}
