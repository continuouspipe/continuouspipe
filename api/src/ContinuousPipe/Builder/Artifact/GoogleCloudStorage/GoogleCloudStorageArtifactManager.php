<?php

namespace ContinuousPipe\Builder\Artifact\GoogleCloudStorage;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Artifact\ArtifactException;
use ContinuousPipe\Builder\Artifact\ArtifactReader;
use ContinuousPipe\Builder\Artifact\ArtifactWriter;
use Google\Cloud\Exception\GoogleException;
use Google\Cloud\ServiceBuilder;
use Google\Cloud\Storage\Bucket;
use GuzzleHttp\Psr7\StreamWrapper;

class GoogleCloudStorageArtifactManager implements ArtifactWriter, ArtifactReader
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

    /**
     * {@inheritdoc}
     */
    public function read(Artifact $artifact): Archive
    {
        try {
            return Archive\FileSystemArchive::fromStream(
                StreamWrapper::getResource(
                    $this->getBucket()->object($artifact->getIdentifier())->downloadAsStream()
                )
            );
        } catch (GoogleException $e) {
            throw new ArtifactException('Unable to read the artifact from the bucket', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(Archive $source, Artifact $artifact)
    {
        $bucket = $this->getBucket();

        if ($bucket->object($artifact->getIdentifier())->exists()) {
            throw new ArtifactException(sprintf(
                'The artifact bucket "%s" already exists',
                $artifact->getIdentifier()
            ));
        }

        try {
            $this->getBucket()->upload($source->read(), [
                'resumable' => false,
                'validate' => false,
                'predefinedAcl' => 'projectPrivate',
                'name' => $artifact->getIdentifier(),
            ]);
        } catch (GoogleException $e) {
            throw new ArtifactException('Unable to write the artifact to the bucket', $e->getCode(), $e);
        }
    }

    private function getBucket() : Bucket
    {
        return $this->serviceBuilder->storage()->bucket($this->bucketName);
    }
}
