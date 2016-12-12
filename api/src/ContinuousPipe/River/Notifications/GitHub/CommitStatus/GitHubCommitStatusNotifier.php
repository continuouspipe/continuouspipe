<?php

namespace ContinuousPipe\River\Notifications\GitHub\CommitStatus;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Notifications\NotificationException;
use ContinuousPipe\River\Notifications\NotificationNotSupported;
use ContinuousPipe\River\Notifications\Notifier;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;
use GuzzleHttp\Exception\RequestException;

class GitHubCommitStatusNotifier implements Notifier
{
    const GITHUB_CONTEXT = 'ContinuousPipe';

    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var GitHubStateResolver
     */
    private $gitHubStateResolver;

    /**
     * @param ClientFactory $gitHubClientFactory
     */
    public function __construct(ClientFactory $gitHubClientFactory)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->gitHubStateResolver = new GitHubStateResolver();
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
        if (!array_key_exists('github_commit_status', $configuration)) {
            throw new NotificationNotSupported('The notifier only supports the "github_commit_status" notification');
        } elseif (false === $configuration['github_commit_status']) {
            return;
        }

        try {
            $client = $this->gitHubClientFactory->createClientForFlow($tide->getFlow());
        } catch (UserCredentialsNotFound $e) {
            throw new NotificationException('Unable to update code status, no valid GitHub credentials in bucket', $e->getCode(), $e);
        }

        $repository = $tide->getCodeReference()->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new NotificationException(sprintf(
                'Repository of type %s is not supported',
                get_class($repository)
            ));
        }

        try {
            $statusParameters = [
                'state' => $this->gitHubStateResolver->fromStatus($status),
                'context' => self::GITHUB_CONTEXT,
                'target_url' => $status->getUrl(),
            ];

            if (null !== $status->getDescription()) {
                $statusParameters['description'] = $status->getDescription();
            }

            $client->repository()->statuses()->create(
                $repository->getOrganisation(),
                $repository->getName(),
                $tide->getCodeReference()->getCommitSha(),
                $statusParameters
            );
        } catch (RequestException $e) {
            throw new NotificationException('Unable to update the GitHub commit status', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return array_key_exists('github_commit_status', $configuration) && $configuration['github_commit_status'];
    }
}
