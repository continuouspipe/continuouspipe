<?php

namespace ContinuousPipe\River\Command;

use Ramsey\Uuid\UuidInterface;

interface TideCommand
{
    public function getTideUuid(): UuidInterface;
}
