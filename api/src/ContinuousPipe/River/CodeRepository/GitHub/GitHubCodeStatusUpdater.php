<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\CodeStatusException;
use ContinuousPipe\River\CodeRepository\CodeStatusUpdater;
use ContinuousPipe\River\GitHub\GitHubClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideContext;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use GuzzleHttp\Exception\RequestException;

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
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param GitHubClientFactory $gitHubClientFactory
     * @param BucketRepository    $bucketRepository
     * @param string              $uiBaseUrl
     */
    public function __construct(GitHubClientFactory $gitHubClientFactory, BucketRepository $bucketRepository, $uiBaseUrl)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->uiBaseUrl = $uiBaseUrl;
        $this->bucketRepository = $bucketRepository;
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
     * @param Tide $tide
     * @param string $state
     * @throws CodeStatusException
     * @throws \ContinuousPipe\River\GitHub\UserCredentialsNotFound
     * @throws \Github\Exception\MissingArgumentException
     */
    private function updateCodeStatus(Tide $tide, $state)
    {
        $tideContext = $tide->getContext();

        try {
            $bucket = $this->bucketRepository->find($tideContext->getTeam()->getBucketUuid());
        } catch (BucketNotFound $e) {
            throw new CodeStatusException('Unable to update code status, the credentials bucket do not exists', $e->getCode(), $e);
        }

        try {
            $client = $this->gitHubClientFactory->createClientFromBucket($bucket);
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
                    'context' => 'continuous-pipe-river',
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
