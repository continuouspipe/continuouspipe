<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\GitHub\GitHubClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\User\User;

class GitHubCommitResolver implements CommitResolver
{
    /**
     * @var GitHubClientFactory
     */
    private $clientFactory;

    /**
     * @var CodeRepository\RepositoryAddressDescriptor
     */
    private $addressDescriptor;

    /**
     * @param GitHubClientFactory                        $clientFactory
     * @param CodeRepository\RepositoryAddressDescriptor $addressDescriptor
     */
    public function __construct(GitHubClientFactory $clientFactory, CodeRepository\RepositoryAddressDescriptor $addressDescriptor)
    {
        $this->clientFactory = $clientFactory;
        $this->addressDescriptor = $addressDescriptor;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeadCommitOfBranch(CodeRepository $repository, User $user, $branch)
    {
        try {
            $client = $this->clientFactory->createClientForUser($user);
        } catch (UserCredentialsNotFound $e) {
            throw new CommitResolverException('Unable to find GitHub credentials', $e->getCode(), $e);
        }

        $description = $this->addressDescriptor->getDescription($repository->getAddress());
        $branch = $client->repository()->branches($description->getUsername(), $description->getRepository(), $branch);
        if (!isset($branch['commit']['sha'])) {
            throw new CommitResolverException(sprintf(
                'Unable to find the SHA1 of the branch "%s"',
                $branch
            ));
        }

        return $branch['commit']['sha'];
    }
}
