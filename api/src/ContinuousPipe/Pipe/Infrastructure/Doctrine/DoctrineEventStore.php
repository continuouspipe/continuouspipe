<?php

namespace ContinuousPipe\Pipe\Infrastructure\Doctrine;

use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\EventBus\EventStore;
use ContinuousPipe\Pipe\Infrastructure\Doctrine\Entity\EventDto;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\Uuid;

class DoctrineEventStore implements EventStore
{
    const DTO_CLASS = 'ContinuousPipe\Pipe\Infrastructure\Doctrine\Entity\EventDto';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getEntityRepository()
    {
        return $this->entityManager->getRepository(self::DTO_CLASS);
    }

    /**
     * @param DeploymentEvent $event
     */
    public function add(DeploymentEvent $event)
    {
        $dto = new EventDto();
        $dto->deploymentUuid = $event->getDeploymentUuid();
        $dto->eventClass = get_class($event);
        $dto->serializedEvent = base64_encode(serialize($event));

        $this->entityManager->persist($dto);
        $this->entityManager->flush();
    }

    /**
     * @param Uuid $uuid
     *
     * @return DeploymentEvent[]
     */
    public function findByDeploymentUuid(Uuid $uuid)
    {
        $dtoCollection = $this->getEntityRepository()->findBy([
            'deploymentUuid' => (string) $uuid,
        ]);

        $events = [];
        foreach ($dtoCollection as $dto) {
            $events[] = unserialize(base64_decode($dto->serializedEvent));
        }

        return $events;
    }
}
