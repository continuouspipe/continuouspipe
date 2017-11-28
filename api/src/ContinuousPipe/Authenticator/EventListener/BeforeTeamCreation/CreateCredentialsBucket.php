<?php

namespace ContinuousPipe\Authenticator\EventListener\BeforeTeamCreation;

use ContinuousPipe\Authenticator\Event\TeamCreationEvent;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateCredentialsBucket implements EventSubscriberInterface
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
            TeamCreationEvent::BEFORE_EVENT => 'createBucketIfNotExists',
        ];
    }

    /**
     * @param TeamCreationEvent $event
     *
     * @return Team
     */
    public function createBucketIfNotExists(TeamCreationEvent $event)
    {
        $team = $event->getTeam();

        if (null === $team->getBucketUuid()) {
            $bucketUuid = Uuid::uuid1();
            $this->bucketRepository->save(new Bucket($bucketUuid));

            $team->setBucketUuid($bucketUuid);
        }

        return $team;
    }
}
