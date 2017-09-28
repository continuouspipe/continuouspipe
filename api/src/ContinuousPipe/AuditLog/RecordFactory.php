<?php

namespace ContinuousPipe\AuditLog;

use ContinuousPipe\Authenticator\Event\TeamCreationEvent;
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
        $data = $this->convertEntityToArray($entity);
        return new Record('user_created', $type, $data);
    }

    /**
     * Create a log record from team created event.
     *
     * @param TeamCreationEvent $event
     * @return Record
     */
    public function createFromTeamCreatedEvent(TeamCreationEvent $event): Record
    {
        $teamEntity = $event->getTeam();
        $type = get_class($event);
        $data = $this->convertEntityToArray($teamEntity);
        $data['team_creator'] = $this->convertEntityToArray($event->getCreator());
        return new Record('team_created', $type, $data);
    }

    /**
     * Transform the given entity object to array representation.
     *
     * @param object $entity
     * @return array
     */
    private function convertEntityToArray($entity): array
    {
        return json_decode($this->serializer->serialize($entity, 'json'), true);
    }
}
