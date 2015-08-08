<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\User\User;

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
    public function getFileSystem(CodeReference $codeReference, User $user)
    {
        return new CodeRepository\GitHubRelativeFileSystem(
            $this->gitHubClientFactory->createClientForUser($user),
            $this->repositoryAddressDescriptor->getDescription($codeReference->getRepository()->getAddress()),
            $codeReference->getReference()
        );
    }
}
