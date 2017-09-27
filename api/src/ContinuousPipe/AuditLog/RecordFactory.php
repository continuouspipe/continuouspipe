<?php

namespace ContinuousPipe\AuditLog;

use ContinuousPipe\Authenticator\Security\Event\UserCreated;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;

class RecordFactory
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Create a log record from a user created event.
     *
     * @param UserCreated $event
     * @return Record
     */
    public function createFromUserCreatedEvent(UserCreated $event): Record
    {
        $entity = $event->getUser();
        $type = get_class($event);
        $data = json_decode($this->serializer->serialize($entity, 'json'), true);
        return new Record('user_created', $type, $data);
    }
}
