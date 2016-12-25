<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

interface BitBucketClient
{
    public function getReference(string $owner, string $repository, string $branch) : string;

    public function getContents(string $owner, string $repository, string $reference, string $filePath) : string;
}
