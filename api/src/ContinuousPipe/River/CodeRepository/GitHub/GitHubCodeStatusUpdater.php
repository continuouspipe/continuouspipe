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
     * @var string
     */
    private $uiBaseUrl;

    /**
     * @param GitHubClientFactory $gitHubClientFactory
     * @param string $uiBaseUrl
     */
    public function __construct(GitHubClientFactory $gitHubClientFactory, $uiBaseUrl)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->uiBaseUrl = $uiBaseUrl;
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
                'target_url' => $this->generateTideUrl($tide)
            ]
        );
    }

    /**
     * @param Tide $tide
     * @return string
     */
    private function generateTideUrl(Tide $tide)
    {
        return sprintf(
            '%s/flows/%s/tide/%s/logs',
            $this->uiBaseUrl,
            (string) $tide->getFlow()->getUuid(),
            (string) $tide->getUuid()
        );
    }
}
