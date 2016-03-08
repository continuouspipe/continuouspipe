<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\Team\Team;

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
    public function getFileSystem(CodeReference $codeReference, Team $team)
    {
        return new CodeRepository\GitHubRelativeFileSystem(
            $this->gitHubClientFactory->createClientFromBucketUuid($team->getBucketUuid()),
            $this->repositoryAddressDescriptor->getDescription($codeReference->getRepository()->getAddress()),
            $codeReference->getCommitSha()
        );
    }
}
