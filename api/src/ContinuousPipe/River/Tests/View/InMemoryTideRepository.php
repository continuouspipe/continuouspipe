<?php

namespace ContinuousPipe\River\Tests\View;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Rhumsaa\Uuid\Uuid;

class InMemoryTideRepository implements TideRepository
{
    private $tideByFlow = [];
    private $tides = [];

    /**
     * {@inheritdoc}
     */
    public function findByFlow(Flow $flow)
    {
        $uuid = (string) $flow->getUuid();
        if (!array_key_exists($uuid, $this->tideByFlow)) {
            return [];
        }

        return $this->tideByFlow[$uuid];
    }

    /**
     * {@inheritdoc}
     */
    public function save(Tide $tide)
    {
        $this->tides[(string) $tide->getUuid()] = $tide;

        $flowUuid = (string) $tide->getFlow()->getUuid();
        if (!array_key_exists($flowUuid, $this->tideByFlow)) {
            $this->tideByFlow[$flowUuid] = [];
        }

        $this->tideByFlow[$flowUuid][] = $tide;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        if (!array_key_exists((string) $uuid, $this->tides)) {
            throw new TideNotFound();
        }

        return $this->tides[(string) $uuid];
    }
}
