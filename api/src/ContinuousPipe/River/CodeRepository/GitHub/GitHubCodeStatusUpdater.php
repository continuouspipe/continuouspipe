<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\CodeStatusException;
use ContinuousPipe\River\Tide\Status\CodeStatusUpdater;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\TideContext;
use GuzzleHttp\Exception\RequestException;

class GitHubCodeStatusUpdater implements CodeStatusUpdater
{
    const STATE_SUCCESS = 'success';
    const STATE_PENDING = 'pending';
    const STATE_FAILURE = 'failure';
    const GITHUB_CONTEXT = 'continuous-pipe-river';

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
    public function success(Tide $tide)
    {
        $this->update($tide, new Status(Status::STATE_SUCCESS));
    }

    /**
     * {@inheritdoc}
     */
    public function pending(Tide $tide)
    {
        $this->update($tide, new Status(Status::STATE_PENDING));
    }

    /**
     * {@inheritdoc}
     */
    public function failure(Tide $tide)
    {
        $this->update($tide, new Status(Status::STATE_FAILURE));
    }

    /**
     * {@inheritdoc}
     */
    public function update(Tide $tide, Status $status)
    {
        $tideContext = $tide->getContext();

        try {
            $client = $this->gitHubClientFactory->createClientFromBucketUuid($tideContext->getTeam()->getBucketUuid());
        } catch (UserCredentialsNotFound $e) {
            throw new CodeStatusException('Unable to update code status, no valid GitHub credentials in bucket', $e->getCode(), $e);
        }

        $repository = $tideContext->getCodeRepository();

        if (!$repository instanceof GitHubCodeRepository) {
            throw new CodeStatusException(sprintf(
                'Repository of type %s is not supported',
                get_class($repository)
            ));
        }

        try {
            $gitHubRepository = $repository->getGitHubRepository();
            $client->repository()->statuses()->create(
                $gitHubRepository->getOwner()->getLogin(),
                $gitHubRepository->getName(),
                $tideContext->getCodeReference()->getCommitSha(),
                [
                    'state' => $state,
                    'context' => self::GITHUB_CONTEXT,
                    'target_url' => $this->generateTideUrl($tideContext),
                ]
            );
        } catch (RequestException $e) {
            throw new CodeStatusException('Unable to update code status', $e->getCode(), $e);
        }
    }

    /**
     * @param TideContext $tideContext
     *
     * @return string
     */
    private function generateTideUrl(TideContext $tideContext)
    {
        return sprintf(
            '%s/kaikai/%s',
            $this->getUiBaseUrl(),
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
