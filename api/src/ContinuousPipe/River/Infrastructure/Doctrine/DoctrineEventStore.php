<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Infrastructure\Doctrine\Entity\EventDto;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Rhumsaa\Uuid\Uuid;

class DoctrineEventStore implements EventStore
{
    const DTO_CLASS = 'ContinuousPipe\River\Infrastructure\Doctrine\Entity\EventDto';

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
     * {@inheritdoc}
     */
    public function add(TideEvent $event)
    {
        $dto = new EventDto();
        $dto->tideUuid = $event->getTideUuid();
        $dto->eventClass = get_class($event);
        $dto->serializedEvent = serialize($event);

        $this->entityManager->persist($dto);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuid(Uuid $uuid)
    {
        $dtoCollection = $this->getEntityRepository()->findBy([
            'tideUuid' => (string) $uuid
        ]);

        $events = [];
        foreach ($dtoCollection as $dto) {
            $events[] = unserialize($dto->serializedEvent);
        }

        return $events;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getEntityRepository()
    {
        return $this->entityManager->getRepository(self::DTO_CLASS);
    }
}
