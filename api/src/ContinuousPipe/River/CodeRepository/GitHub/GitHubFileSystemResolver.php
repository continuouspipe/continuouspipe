<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\GitHub\GitHubClientException;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;

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
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference) : RelativeFileSystem
    {
        try {
            return new CodeRepository\GitHubRelativeFileSystem(
                $this->gitHubClientFactory->createClientForFlow($flow->getUuid()),
                $this->repositoryAddressDescriptor->getDescription($codeReference->getRepository()->getAddress()),
                $codeReference->getCommitSha() ?: $codeReference->getBranch()
            );
        } catch (UserCredentialsNotFound $e) {
            throw new CodeRepository\CodeRepositoryException($e->getMessage(), $e->getCode(), $e);
        } catch (GitHubClientException $e) {
            throw new CodeRepository\CodeRepositoryException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $flow->getRepository() instanceof GitHubCodeRepository;
    }
}
