<?php

namespace ContinuousPipe\Security\Credentials;

use Ramsey\Uuid\UuidInterface;

interface BucketContainer
{
    /**
     * Returns the UUID of the security bucket.
     *
     * @return UuidInterface
     */
    public function getBucketUuid();
}
