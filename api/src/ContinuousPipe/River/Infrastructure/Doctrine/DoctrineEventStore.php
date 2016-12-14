<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideEventWithMetadata;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Infrastructure\Doctrine\Entity\EventDto;
use ContinuousPipe\River\View\TimeResolver;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DoctrineEventStore implements EventStore
{
    const DTO_CLASS = 'ContinuousPipe\River\Infrastructure\Doctrine\Entity\EventDto';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TimeResolver
     */
    private $timeResolver;

    /**
     * @param EntityManager $entityManager
     * @param TimeResolver  $timeResolver
     */
    public function __construct(EntityManager $entityManager, TimeResolver $timeResolver)
    {
        $this->entityManager = $entityManager;
        $this->timeResolver = $timeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function add(TideEvent $event)
    {
        $dto = new EventDto();
        $dto->tideUuid = $event->getTideUuid();
        $dto->eventClass = get_class($event);
        $dto->serializedEvent = base64_encode(serialize($event));
        $dto->eventDatetime = $this->timeResolver->resolve();

        $this->entityManager->persist($dto);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuid(UuidInterface $uuid)
    {
        $dtoCollection = $this->getEntityRepository()->findBy([
            'tideUuid' => (string) $uuid,
        ], [
            'eventDatetime' => 'ASC',
        ]);

        return $this->getEventsFromDtoCollection($dtoCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuidAndType(Uuid $uuid, $className)
    {
        $dtoCollection = $this->getEntityRepository()->findBy([
            'tideUuid' => (string) $uuid,
            'eventClass' => $className,
        ], [
            'eventDatetime' => 'ASC',
        ]);

        return $this->getEventsFromDtoCollection($dtoCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuidAndTypeWithMetadata(UuidInterface $uuid, $className)
    {
        $dtoCollection = $this->getEntityRepository()->findBy([
            'tideUuid' => (string) $uuid,
            'eventClass' => $className,
        ], [
            'eventDatetime' => 'ASC',
        ]);

        return array_map(function (EventDto $dto) {
            return new TideEventWithMetadata(
                $this->fromDto($dto),
                $dto->eventDatetime
            );
        }, $dtoCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuidWithMetadata(UuidInterface $uuid)
    {
        $dtoCollection = $this->getEntityRepository()->findBy([
            'tideUuid' => (string) $uuid,
        ], [
            'eventDatetime' => 'ASC',
        ]);

        return array_map(function (EventDto $dto) {
            return new TideEventWithMetadata(
                $this->fromDto($dto),
                $dto->eventDatetime
            );
        }, $dtoCollection);
    }

    /**
     * @param array $dtoCollection
     *
     * @return TideEvent[]
     */
    private function getEventsFromDtoCollection(array $dtoCollection)
    {
        $events = [];
        foreach ($dtoCollection as $dto) {
            $events[] = $this->fromDto($dto);
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

    /**
     * @param EventDto $dto
     *
     * @return TideEvent
     */
    private function fromDto($dto)
    {
        return unserialize(base64_decode($dto->serializedEvent));
    }
}
