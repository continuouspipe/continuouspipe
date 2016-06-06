<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\Authenticator\CredentialsNotFound;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use LogStream\Logger;
use LogStream\Node\Text;

class GitHubArchiveBuilder implements ArchiveBuilder
{
    /**
     * @var GitHubHttpClientFactory
     */
    private $gitHubHttpClientFactory;

    /**
     * @var RemoteArchiveLocator
     */
    private $remoteArchiveLocator;
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param RemoteArchiveLocator    $remoteArchiveLocator
     * @param GitHubHttpClientFactory $gitHubHttpClientFactory
     * @param BucketRepository        $bucketRepository
     */
    public function __construct(RemoteArchiveLocator $remoteArchiveLocator, GitHubHttpClientFactory $gitHubHttpClientFactory, BucketRepository $bucketRepository)
    {
        $this->gitHubHttpClientFactory = $gitHubHttpClientFactory;
        $this->remoteArchiveLocator = $remoteArchiveLocator;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(BuildRequest $buildRequest, Logger $logger)
    {
        try {
            $bucket = $this->bucketRepository->find($buildRequest->getCredentialsBucket());
        } catch (BucketNotFound $e) {
            throw new ArchiveCreationException('Credentials bucket not found', $e->getCode(), $e);
        }

        try {
            $httpClient = $this->gitHubHttpClientFactory->createFromBucket($bucket);
        } catch (CredentialsNotFound $e) {
            throw new ArchiveCreationException($e->getMessage(), $e->getCode(), $e);
        }

        $archiveUrl = $this->remoteArchiveLocator->getArchiveUrl($buildRequest->getRepository());

        $logger->child(new Text(sprintf('Will download code from archive: %s', $archiveUrl)));

        $packer = new ArchivePacker($httpClient);

        return $packer->createFromUrl($buildRequest->getContext(), $archiveUrl);
    }
}
