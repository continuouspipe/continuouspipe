<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\GitHub\GitHubClientFactory;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Team\Team;

class GitHubFileSystemResolver implements CodeRepository\FileSystemResolver
{
    /**
     * @var GitHubClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var CodeRepository\RepositoryAddressDescriptor
     */
    private $repositoryAddressDescriptor;
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param GitHubClientFactory                        $gitHubClientFactory
     * @param CodeRepository\RepositoryAddressDescriptor $repositoryAddressDescriptor
     * @param BucketRepository                           $bucketRepository
     */
    public function __construct(GitHubClientFactory $gitHubClientFactory, CodeRepository\RepositoryAddressDescriptor $repositoryAddressDescriptor, BucketRepository $bucketRepository)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->repositoryAddressDescriptor = $repositoryAddressDescriptor;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(CodeReference $codeReference, Team $team)
    {
        $bucket = $this->bucketRepository->find($team->getBucketUuid());

        return new CodeRepository\GitHubRelativeFileSystem(
            $this->gitHubClientFactory->createClientFromBucket($bucket),
            $this->repositoryAddressDescriptor->getDescription($codeReference->getRepository()->getAddress()),
            $codeReference->getCommitSha()
        );
    }
}
