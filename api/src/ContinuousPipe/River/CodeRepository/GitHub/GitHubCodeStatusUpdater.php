<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\CodeStatusException;
use ContinuousPipe\River\Tide\Status\CodeStatusUpdater;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\Tide\Status\Status;
use GuzzleHttp\Exception\RequestException;

class GitHubCodeStatusUpdater implements CodeStatusUpdater
{
    const GITHUB_CONTEXT = 'ContinuousPipe';

    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var string
     */
    private $uiBaseUrl;

    /**
     * @param ClientFactory $gitHubClientFactory
     * @param string        $uiBaseUrl
     */
    public function __construct(ClientFactory $gitHubClientFactory, $uiBaseUrl)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->uiBaseUrl = $uiBaseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Tide $tide, Status $status)
    {
        try {
            $client = $this->gitHubClientFactory->createClientForFlow($tide->getFlow());
        } catch (UserCredentialsNotFound $e) {
            throw new CodeStatusException('Unable to update code status, no valid GitHub credentials in bucket', $e->getCode(), $e);
        }

        $repository = $tide->getCodeReference()->getRepository();

        if (!$repository instanceof GitHubCodeRepository) {
            throw new CodeStatusException(sprintf(
                'Repository of type %s is not supported',
                get_class($repository)
            ));
        }

        try {
            $gitHubRepository = $repository->getGitHubRepository();
            $statusParameters = [
                'state' => $status->getState(),
                'context' => self::GITHUB_CONTEXT,
                'target_url' => $this->generateTideUrl($tide),
            ];

            if (null !== $status->getDescription()) {
                $statusParameters['description'] = $status->getDescription();
            }

            $client->repository()->statuses()->create(
                $gitHubRepository->getOwner()->getLogin(),
                $gitHubRepository->getName(),
                $tideContext->getCodeReference()->getCommitSha(),
                $statusParameters
            );
        } catch (RequestException $e) {
            throw new CodeStatusException('Unable to update code status', $e->getCode(), $e);
        }
    }

    /**
     * @param Tide $tide
     *
     * @return string
     */
    private function generateTideUrl(Tide $tide)
    {
        return sprintf(
            '%s/team/%s/%s/%s/logs',
            $this->getUiBaseUrl(),
            $tide->getTeam()->getSlug(),
            (string) $tide->getFlow()->getUuid(),
            (string) $tide->getUuid()
        );
    }

    /**
     * @return string
     */
    private function getUiBaseUrl()
    {
        $baseUrl = $this->uiBaseUrl;

        if (strpos($baseUrl, 'http') !== 0) {
            $baseUrl = 'https://'.$baseUrl;
        }

        return $baseUrl;
    }
}
