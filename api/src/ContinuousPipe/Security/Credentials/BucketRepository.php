<?php

namespace ContinuousPipe\Security\Credentials;

use Ramsey\Uuid\UuidInterface;

interface BucketRepository
{
    /**
     * @param UuidInterface $uuid
     *
     * @throws BucketNotFound
     *
     * @return Bucket
     */
    public function find(UuidInterface $uuid);

    /**
     * Save the given bucket.
     *
     * @param Bucket $bucket
     */
    public function save(Bucket $bucket);
}
