<?php

namespace ContinuousPipe\River\Flow\Event;

use Ramsey\Uuid\UuidInterface;

interface FlowEvent
{
    public function getFlowUuid() : UuidInterface;
}
