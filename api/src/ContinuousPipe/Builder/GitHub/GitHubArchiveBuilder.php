<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\Credentials\BucketRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use LogStream\Logger;

class GitHubArchiveBuilder implements ArchiveBuilder
{
    /**
     * @var RemoteArchiveLocator
     */
    private $remoteArchiveLocator;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var Client
     */
    private $githubClient;

    /**
     * @param RemoteArchiveLocator $remoteArchiveLocator
     * @param Client               $githubClient
     * @param BucketRepository     $bucketRepository
     */
    public function __construct(RemoteArchiveLocator $remoteArchiveLocator, Client $githubClient, BucketRepository $bucketRepository)
    {
        $this->remoteArchiveLocator = $remoteArchiveLocator;
        $this->bucketRepository = $bucketRepository;
        $this->githubClient = $githubClient;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(BuildRequest $buildRequest, Logger $logger)
    {
        $repository = $buildRequest->getRepository();
        $archiveUrl = $this->remoteArchiveLocator->getArchiveUrl($repository);

        // This `githubClient` should probably be created by a factory instead of being injected
        $this->githubClient->setDefaultOption('auth', [$repository->getToken(), 'x-oauth-basic']);
        $packer = new ArchivePacker($this->githubClient);

        try {
            $archive = $packer->createFromUrl($buildRequest->getContext(), $archiveUrl);
        } catch (ClientException $e) {
            throw new ArchiveCreationException($e->getMessage(), $e->getCode(), $e);
        }

        return $archive;
    }
}
