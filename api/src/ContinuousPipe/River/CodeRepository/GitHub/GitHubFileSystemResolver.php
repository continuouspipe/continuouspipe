<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;

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
     * @param GitHubClientFactory                        $gitHubClientFactory
     * @param CodeRepository\RepositoryAddressDescriptor $repositoryAddressDescriptor
     */
    public function __construct(GitHubClientFactory $gitHubClientFactory, CodeRepository\RepositoryAddressDescriptor $repositoryAddressDescriptor)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->repositoryAddressDescriptor = $repositoryAddressDescriptor;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(CodeRepository $repository, CodeReference $codeReference)
    {
        return new CodeRepository\GitHubRelativeFileSystem(
            $this->gitHubClientFactory->createAnonymous(),
            $this->repositoryAddressDescriptor->getDescription($repository->getAddress()),
            $codeReference->getReference()
        );
    }
}
