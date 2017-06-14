<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage;

use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\Infrastructure\Firebase\FirebaseClient;
use ContinuousPipe\River\View\Storage\PullRequestViewStorage;
use Firebase\Exception\ApiException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

class FirebasePullRequestViewStorage implements PullRequestViewStorage
{
    /**
     * @var string
     */
    private $databaseUri;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var FirebaseClient
     */
    private $firebaseClient;

    public function __construct(
        FirebaseClient $firebaseClient,
        string $databaseUri,
        LoggerInterface $logger
    ) {
        $this->databaseUri = $databaseUri;
        $this->logger = $logger;
        $this->firebaseClient = $firebaseClient;
    }

    public function add(UuidInterface $flowUuid, PullRequest $pullRequest)
    {
        try {
            $this->firebaseClient->update(
                $this->databaseUri,
                $this->tideUpdatePath($flowUuid, (string) $pullRequest->getBranch()),
                [
                    (string) $pullRequest->getIdentifier() => [
                        'identifier' => (string) $pullRequest->getIdentifier(),
                        'title' => $pullRequest->getTitle()
                    ]
                ]
            );
        } catch (ApiException $e) {
            $this->logCannotUpdate($flowUuid, $e);
        }
    }

    public function deletePullRequest(UuidInterface $flowUuid, PullRequest $pullRequest)
    {
        try {
            $this->firebaseClient->remove(
                $this->databaseUri,
                $this->pullRequestPath($flowUuid, (string) $pullRequest->getBranch(), $pullRequest->getIdentifier())
            );
        } catch (ApiException $e) {
            $this->logCannotUpdate($flowUuid, $e);
        }
    }

    public function deleteBranch(UuidInterface $flowUuid, string $branchName)
    {
        try {
            $this->firebaseClient->remove(
                $this->databaseUri,
                $this->tideUpdatePath($flowUuid, $branchName)
            );
        } catch (ApiException $e) {
            $this->logCannotUpdate($flowUuid, $e);
        }
    }

    private function tideUpdatePath(UuidInterface $flowUuid, string $branchName)
    {
        return sprintf(
            'flows/%s/pull-requests/by-branch/%s',
            (string) $flowUuid,
            hash('sha256', $branchName)
        );
    }

    private function pullRequestPath(UuidInterface $flowUuid, string $branchName, string $pullRequestId)
    {
        return sprintf(
            'flows/%s/pull-requests/by-branch/%s/%s',
            (string) $flowUuid,
            hash('sha256', $branchName),
            $pullRequestId
        );
    }

    private function logCannotUpdate(UuidInterface $flowUuid, \Exception $e)
    {
        $this->logger->warning(
            'Unable to update the pull requests view in Firebase',
            [
                'exception' => $e,
                'message' => $e->getMessage(),
                'flowUuid' => (string) $flowUuid,
            ]
        );
    }

}