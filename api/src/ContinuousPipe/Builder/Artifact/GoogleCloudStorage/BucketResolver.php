<?php

namespace ContinuousPipe\Builder\Artifact\GoogleCloudStorage;

use Google\Cloud\ServiceBuilder;
use Google\Cloud\Storage\Bucket;

class BucketResolver
{
    /**
     * @var ServiceBuilder
     */
    private $serviceBuilder;

    /**
     * @var string
     */
    private $bucketName;

    public function __construct(string $projectId, string $keyFilePath, string $bucketName)
    {
        $this->bucketName = $bucketName;
        $this->serviceBuilder = new ServiceBuilder([
            'projectId' => $projectId,
            'keyFilePath' => $keyFilePath,
        ]);
    }

    public function resolve() : Bucket
    {
        return $this->serviceBuilder->storage()->bucket($this->bucketName);
    }
}
