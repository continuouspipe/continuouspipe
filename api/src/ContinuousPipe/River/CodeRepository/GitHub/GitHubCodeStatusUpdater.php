<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\CodeStatusUpdater;
use ContinuousPipe\River\GitHub\GitHubClientFactory;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideContext;

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
     * @param string              $uiBaseUrl
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
        $tideContext = $tide->getContext();
        $client = $this->gitHubClientFactory->createClientForUser($tideContext->getUser());
        $repository = $tideContext->getCodeRepository();

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
            $tideContext->getCodeReference()->getCommitSha(),
            [
                'state' => $state,
                'context' => 'continuous-pipe-river',
                'target_url' => $this->generateTideUrl($tideContext),
            ]
        );
    }

    /**
     * @param TideContext $tideContext
     *
     * @return string
     */
    private function generateTideUrl(TideContext $tideContext)
    {
        return sprintf(
            '%s/flows/%s/tides/%s/logs',
            $this->getUiBaseUrl(),
            (string) $tideContext->getFlowUuid(),
            (string) $tideContext->getTideUuid()
        );
    }

    /**
     * @return string
     */
    private function getUiBaseUrl()
    {
        $baseUrl = $this->uiBaseUrl;

        if (strpos($baseUrl, 'http') !== 0) {
            $baseUrl = 'http://'.$baseUrl;
        }

        return $baseUrl;
    }
}
