<?php

namespace ContinuousPipe\Builder\Artifact\GoogleCloudStorage;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Artifact\ArtifactException;
use ContinuousPipe\Builder\Artifact\ArtifactReader;
use ContinuousPipe\Builder\Artifact\ArtifactWriter;
use ContinuousPipe\Builder\Artifact\ArtifactRemover;
use Google\Cloud\Exception\GoogleException;
use Google\Cloud\Exception\NotFoundException;
use GuzzleHttp\Psr7\StreamWrapper;

class GoogleCloudStorageArtifactManager implements ArtifactWriter, ArtifactReader, ArtifactRemover
{
    /**
     * @var BucketResolver
     */
    private $bucketResolver;

    /**
     * @param BucketResolver $bucketResolver
     */
    public function __construct(BucketResolver $bucketResolver)
    {
        $this->bucketResolver = $bucketResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function read(Artifact $artifact): Archive
    {
        try {
            return Archive\FileSystemArchive::fromStream(
                StreamWrapper::getResource(
                    $this->bucketResolver->resolve()->object($artifact->getIdentifier())->downloadAsStream()
                )
            );
        } catch (NotFoundException $e) {
            throw new Artifact\ArtifactNotFound(sprintf('Artifact "%s" not found', $artifact->getName()), $e->getCode(), $e);
        } catch (GoogleException $e) {
            throw new ArtifactException('Unable to read the artifact from the bucket', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(Archive $source, Artifact $artifact)
    {
        $bucket = $this->bucketResolver->resolve();

        if ($bucket->object($artifact->getIdentifier())->exists()) {
            throw new ArtifactException(sprintf(
                'The artifact bucket "%s" already exists',
                $artifact->getIdentifier()
            ));
        }

        try {
            $bucket->upload($source->read(), [
                'resumable' => false,
                'validate' => false,
                'predefinedAcl' => 'projectPrivate',
                'name' => $artifact->getIdentifier(),
            ]);
        } catch (GoogleException $e) {
            throw new ArtifactException('Unable to write the artifact to the bucket', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Artifact $artifact)
    {
        try {
            $this->bucketResolver->resolve()->object($artifact->getIdentifier())->delete();
        } catch (GoogleException $e) {
            throw new ArtifactException('Unable to delete the artifact', $e->getCode(), $e);
        }
    }
}
