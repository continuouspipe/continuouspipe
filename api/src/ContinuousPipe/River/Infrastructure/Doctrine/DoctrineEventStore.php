<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Infrastructure\Doctrine\Entity\EventDto;
use Doctrine\ORM\EntityManager;
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
        $dto->serializedEvent = base64_encode(serialize($event));
        $dto->eventDatetime = $this->getCurrentMicroDateTime();

        $this->entityManager->persist($dto);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuid(Uuid $uuid)
    {
        $dtoCollection = $this->getEntityRepository()->findBy([
            'tideUuid' => (string) $uuid,
        ], [
            'eventDatetime' => 'ASC',
        ]);

        $events = [];
        foreach ($dtoCollection as $dto) {
            $events[] = unserialize(base64_decode($dto->serializedEvent));
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
     * @return \DateTime
     */
    private function getCurrentMicroDateTime()
    {
        $time = microtime(true);
        $microSeconds = sprintf('%06d', ($time - floor($time)) * 1000000);

        return new \DateTime(date('Y-m-d H:i:s.'.$microSeconds, $time));
    }
}
