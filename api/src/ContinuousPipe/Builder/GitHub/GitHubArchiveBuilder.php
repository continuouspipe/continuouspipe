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
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var Client
     */
    private $githubClient;

    /**
     * @var RepositoryAddressDescriptor
     */
    private $repositoryAddressDescriptor;

    /**
     * @param RepositoryAddressDescriptor $repositoryAddressDescriptor
     * @param Client                      $githubClient
     * @param BucketRepository            $bucketRepository
     */
    public function __construct(RepositoryAddressDescriptor $repositoryAddressDescriptor, Client $githubClient, BucketRepository $bucketRepository)
    {
        $this->bucketRepository = $bucketRepository;
        $this->githubClient = $githubClient;
        $this->repositoryAddressDescriptor = $repositoryAddressDescriptor;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(BuildRequest $buildRequest, Logger $logger)
    {
        $repository = $buildRequest->getRepository();

        try {
            $description = $this->repositoryAddressDescriptor->getDescription($repository->getAddress());
        } catch (InvalidRepositoryAddress $e) {
            throw new ArchiveCreationException($e->getMessage(), $e->getCode(), $e);
        }

        $archiveUrl = sprintf(
            'https://api.github.com/repos/%s/%s/tarball/%s',
            $description->getUsername(),
            $description->getRepository(),
            $repository->getBranch()
        );

        $packer = new ArchivePacker($this->githubClient, $repository);

        try {
            $archive = $packer->createFromUrl($buildRequest->getContext(), $archiveUrl);
        } catch (ClientException $e) {
            throw new ArchiveCreationException($e->getMessage(), $e->getCode(), $e);
        }

        return $archive;
    }
}
