<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;

class EventBasedFlowRepository implements FlowRepository
{
    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        // TODO: Find from events. If not found, then ask the "legacy" flow DTO, create and
        // store events from them.
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
