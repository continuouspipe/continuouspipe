<?php

namespace ContinuousPipe\River\Event;

use Ramsey\Uuid\UuidInterface;

class TideGenerated implements TideEvent
{
    private $tideUuid;
    private $generationUuid;

    public function __construct(UuidInterface $tideUuid, UuidInterface $generationUuid)
    {
        $this->tideUuid = $tideUuid;
        $this->generationUuid = $generationUuid;
    }

    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    public function getGenerationUuid(): UuidInterface
    {
        return $this->generationUuid;
    }
}
