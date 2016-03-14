<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\Credentials\BucketContainer;

class GitHubFileSystemResolver implements CodeRepository\FileSystemResolver
{
    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var CodeRepository\RepositoryAddressDescriptor
     */
    private $repositoryAddressDescriptor;

    /**
     * @param ClientFactory                              $gitHubClientFactory
     * @param CodeRepository\RepositoryAddressDescriptor $repositoryAddressDescriptor
     */
    public function __construct(ClientFactory $gitHubClientFactory, CodeRepository\RepositoryAddressDescriptor $repositoryAddressDescriptor)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->repositoryAddressDescriptor = $repositoryAddressDescriptor;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(CodeReference $codeReference, BucketContainer $bucketContainer)
    {
        return new CodeRepository\GitHubRelativeFileSystem(
            $this->gitHubClientFactory->createClientFromBucketUuid($bucketContainer->getBucketUuid()),
            $this->repositoryAddressDescriptor->getDescription($codeReference->getRepository()->getAddress()),
            $codeReference->getCommitSha()
        );
    }
}
