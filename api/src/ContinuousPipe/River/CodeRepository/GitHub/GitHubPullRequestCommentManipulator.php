<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\CodeRepository\PullRequestCommentManipulator;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\GitHubClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\View\Tide;
use GuzzleHttp\Exception\RequestException;

class GitHubPullRequestCommentManipulator implements PullRequestCommentManipulator
{
    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    public function __construct(ClientFactory $gitHubClientFactory)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
    }

    public function writeComment(Tide $tide, PullRequest $pullRequest, string $contents): string
    {
        list($client, $repository) = $this->getClientAndRepository($tide);

        try {
            $comment = $client->issues()->comments()->create(
                $repository->getOrganisation(),
                $repository->getName(),
                $pullRequest->getIdentifier(),
                [
                    'body' => $contents,
                ]
            );
        } catch (RequestException $e) {
            throw new CodeRepositoryException('Unable to create the pull-request comment', $e->getCode(), $e);
        }

        return $comment['id'];
    }

    public function deleteComment(Tide $tide, PullRequest $pullRequest, string $identifier)
    {
        list($client, $repository) = $this->getClientAndRepository($tide);

        try {
            $client->issues()->comments()->remove(
                $repository->getOrganisation(),
                $repository->getName(),
                $identifier
            );
        } catch (RequestException $e) {
            if (null !== ($response = $e->getResponse())) {
                if ($response->getStatusCode() == 404) {
                    // Ignore 404 errors, that probably means that the comment do not exists anymore.
                    return;
                }
            }

            throw new CodeRepositoryException('Unable to delete the pull-request comment', $e->getCode(), $e);
        }
    }

    public function supports(Tide $tide): bool
    {
        return $tide->getCodeReference()->getRepository() instanceof GitHubCodeRepository;
    }

    /**
     * @param Tide $tide
     *
     * @return array
     *
     * @throws CodeRepositoryException
     */
    private function getClientAndRepository(Tide $tide): array
    {
        try {
            $client = $this->gitHubClientFactory->createClientForFlow($tide->getFlowUuid());
        } catch (UserCredentialsNotFound $e) {
            throw new CodeRepositoryException('No valid GitHub credentials in bucket', $e->getCode(), $e);
        }

        $repository = $tide->getCodeReference()->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new CodeRepositoryException('This pull-request comment manipulator only supports GitHub repositories');
        }

        return [$client, $repository];
    }
}
