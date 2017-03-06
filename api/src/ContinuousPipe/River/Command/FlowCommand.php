<?php

namespace ContinuousPipe\River\Command;

use Ramsey\Uuid\UuidInterface;

interface FlowCommand
{
    public function getFlowUuid(): UuidInterface;
}