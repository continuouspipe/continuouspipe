<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\CodeStatusUpdater;
use ContinuousPipe\River\Tide;

class GitHubCodeStatusUpdater implements CodeStatusUpdater
{
    const STATE_SUCCESS = 'success';
    const STATE_PENDING = 'pending';
    const STATE_FAILURE = 'failure';

    /**
     * @var GitHubClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @param GitHubClientFactory $gitHubClientFactory
     */
    public function __construct(GitHubClientFactory $gitHubClientFactory)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function success(Tide $tide)
    {
        $this->updateCodeStatus($tide, self::STATE_SUCCESS);
    }

    /**
     * {@inheritdoc}
     */
    public function pending(Tide $tide)
    {
        $this->updateCodeStatus($tide, self::STATE_PENDING);
    }

    /**
     * {@inheritdoc}
     */
    public function failure(Tide $tide)
    {
        $this->updateCodeStatus($tide, self::STATE_FAILURE);
    }

    /**
     * @param Tide   $tide
     * @param string $state
     */
    private function updateCodeStatus(Tide $tide, $state)
    {
        $client = $this->gitHubClientFactory->createClientForUser($tide->getUser());
        $repository = $tide->getCodeRepository();

        if (!$repository instanceof GitHubCodeRepository) {
            throw new \RuntimeException(sprintf(
                'Repository of type %s is not supported',
                get_class($repository)
            ));
        }

        $gitHubRepository = $repository->getGitHubRepository();
        $client->repository()->statuses()->create(
            $gitHubRepository->getOwner()->getLogin(),
            $gitHubRepository->getName(),
            $tide->getCodeReference()->getCommitSha(),
            [
                'state' => $state,
                'context' => 'continuous-pipe-river',
            ]
        );
    }
}
