<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

interface BitBucketClient
{
    public function getReference(string $owner, string $repository, string $branch);
}
