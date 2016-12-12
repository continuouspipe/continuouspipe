<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
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
    public function getFileSystemWithBucketContainer(CodeReference $codeReference, BucketContainer $bucketContainer)
    {
        return new CodeRepository\GitHubRelativeFileSystem(
            $this->gitHubClientFactory->createClientFromBucketUuid($bucketContainer->getBucketUuid()),
            $this->repositoryAddressDescriptor->getDescription($codeReference->getRepository()->getAddress()),
            $codeReference->getCommitSha()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference)
    {
        return new CodeRepository\GitHubRelativeFileSystem(
            $this->gitHubClientFactory->createClientForFlow($flow),
            $this->repositoryAddressDescriptor->getDescription($codeReference->getRepository()->getAddress()),
            $codeReference->getCommitSha()
        );
    }
}
