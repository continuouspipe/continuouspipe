<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class RandomApiKeyUuidGenerator implements ApiKeyUuidGenerator
{
    public function generate(): UuidInterface
    {
        return Uuid::uuid4();
    }
}
