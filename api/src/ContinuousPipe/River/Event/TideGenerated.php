<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\River\Flow\Projections\FlatPipeline;
use Ramsey\Uuid\UuidInterface;

class TideGenerated implements TideEvent
{
    private $tideUuid;
    private $generationUuid;
    private $flatPipeline;
    private $flowUuid;

    public function __construct(UuidInterface $tideUuid, UuidInterface $flowUuid, UuidInterface $generationUuid, FlatPipeline $flatPipeline)
    {
        $this->tideUuid = $tideUuid;
        $this->generationUuid = $generationUuid;
        $this->flatPipeline = $flatPipeline;
        $this->flowUuid = $flowUuid;
    }

    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    public function getGenerationUuid(): UuidInterface
    {
        return $this->generationUuid;
    }

    public function getFlatPipeline(): FlatPipeline
    {
        return $this->flatPipeline;
    }

    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }
}
