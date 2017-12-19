<?php

namespace ContinuousPipe\Security\Tests\Credentials;

use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class InMemoryBucketRepository implements BucketRepository
{
    private $buckets = [];

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid)
    {
        if (!array_key_exists((string) $uuid, $this->buckets)) {
            throw new BucketNotFound(sprintf(
                'Bucket %s not found',
                $uuid
            ));
        }

        return $this->buckets[(string) $uuid];
    }

    /**
     * {@inheritdoc}
     */
    public function save(Bucket $bucket)
    {
        $this->buckets[(string) $bucket->getUuid()] = $bucket;
    }
}
