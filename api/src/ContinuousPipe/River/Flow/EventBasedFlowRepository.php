<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\EventStore\EventStore;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;

class EventBasedFlowRepository implements FlowRepository
{
    private $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        $events = $this->eventStore->read(EventStream::fromUuid($uuid));

        if (0 === count($events)) {
            throw new FlowNotFound(sprintf(
                'No flow "%s" found',
                (string) $uuid
            ));
        }

        return Flow::fromEvents($events);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Flow $flow)
    {
        throw new \RuntimeException('Should not be used anymore, deprecated method.');
    }

    /**
     * {@inheritdoc}
     */
    public function save(Flow $flow)
    {
        throw new \RuntimeException('Should not be used anymore, deprecated method.');
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        throw new \RuntimeException('Should not be used anymore, deprecated method.');
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeRepository(CodeRepository $codeRepository)
    {
        throw new \RuntimeException('Should not be used anymore, deprecated method.');
    }
}
