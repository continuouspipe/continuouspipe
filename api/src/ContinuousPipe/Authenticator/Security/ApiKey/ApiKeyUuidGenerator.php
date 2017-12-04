<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use Ramsey\Uuid\UuidInterface;

interface ApiKeyUuidGenerator
{
    public function generate(): UuidInterface;
}
