<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\River\Flow\Projections\FlatPipeline;
use ContinuousPipe\River\TideContext;
use Ramsey\Uuid\UuidInterface;

class TideCreated implements TideEvent
{
    private $tideContext;
    private $tideUuid;
    private $generationUuid;
    private $flatPipeline;
    private $flowUuid;
    private $hasContinuousPipeFile;

    public function __construct(UuidInterface $tideUuid, UuidInterface $flowUuid, TideContext $tideContext, UuidInterface $generationUuid, FlatPipeline $flatPipeline, bool $hasContinuousPipeFile = null)
    {
        $this->tideUuid = $tideUuid;
        $this->generationUuid = $generationUuid;
        $this->flatPipeline = $flatPipeline;
        $this->flowUuid = $flowUuid;
        $this->tideContext = $tideContext;
        $this->hasContinuousPipeFile = $hasContinuousPipeFile;
    }

    public function getTideUuid(): UuidInterface
    {
        return $this->tideContext->getTideUuid();
    }

    public function getTideContext() : TideContext
    {
        return $this->tideContext;
    }

    /**
     * This method can return `null` for BC reasons.
     *
     * @return UuidInterface|null
     */
    public function getGenerationUuid()
    {
        return $this->generationUuid;
    }

    /**
     * This method can return `null` for BC reasons.
     *
     * @return FlatPipeline|null
     */
    public function getFlatPipeline()
    {
        return $this->flatPipeline;
    }

    /**
     * This method can return `null` for BC reasons.
     *
     * @return UuidInterface|null
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }

    /**
     * This method can return `null` for BC reasons.
     *
     * @return bool|null
     */
    public function hasContinuousPipeFile()
    {
        return $this->hasContinuousPipeFile;
    }
}
