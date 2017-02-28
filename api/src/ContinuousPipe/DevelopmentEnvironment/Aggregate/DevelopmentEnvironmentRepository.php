<?php

namespace ContinuousPipe\DevelopmentEnvironment\Aggregate;

use Ramsey\Uuid\UuidInterface;

interface DevelopmentEnvironmentRepository
{
    public function find(UuidInterface $uuid) : DevelopmentEnvironment;
}
