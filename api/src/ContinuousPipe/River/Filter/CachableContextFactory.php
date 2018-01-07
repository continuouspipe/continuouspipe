<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\Event\GitHub\PullRequestEvent;
use ContinuousPipe\River\Filter\View\TaskListView;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\GitHub\ClientFactory;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

class CachableContextFactory implements ContextFactory
{
    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PullRequestResolver
     */
    private $pullRequestResolver;

    /**
     * @param ClientFactory       $gitHubClientFactory
     * @param LoggerInterface     $logger
     * @param PullRequestResolver $pullRequestResolver
     */
    public function __construct(ClientFactory $gitHubClientFactory, LoggerInterface $logger, PullRequestResolver $pullRequestResolver)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->logger = $logger;
        $this->pullRequestResolver = $pullRequestResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function create(UuidInterface $flowUuid, CodeReference $codeReference, Tide $tide = null) : ArrayObject
    {
        $context = [
            'code_reference' => new ArrayObject([
                'branch' => $codeReference->getBranch(),
                'sha' => $codeReference->getCommitSha(),
            ]),
            'flow' => new ArrayObject([
                'uuid' => (string) $flowUuid,
            ]),
            'pull_request' => $this->getPullRequestContext($flowUuid, $codeReference, $tide),
        ];

        if (null !== $tide) {
            $context['tide'] = new ArrayObject([
                'uuid' => (string) $tide->getUuid(),
            ]);
        }

        return new ArrayObject($context);
    }

    /**
     * @param Tide $tide
     *
     * @return ArrayObject
     */
    private function getPullRequestContext(UuidInterface $flowUuid, CodeReference $codeReference, Tide $tide = null)
    {
        if (null !== $tide && null !== ($event = $tide->getContext()->getCodeRepositoryEvent()) && $event instanceof PullRequestEvent) {
            $pullRequest = $event->getPullRequest();
        } else {
            $matchingPullRequests = $this->pullRequestResolver->findPullRequestWithHeadReference($flowUuid, $codeReference);
            $pullRequest = count($matchingPullRequests) > 0 ? current($matchingPullRequests) : null;
        }

        if (null === $pullRequest) {
            return new ArrayObject([
                'number' => 0,
                'title' => '',
                'labels' => [],
            ]);
        }

        return new ArrayObject([
            'number' => $pullRequest->getIdentifier(),
            'title' => $pullRequest->getTitle(),
            'labels' => $this->getPullRequestLabelNames($flowUuid, $codeReference->getRepository(), $pullRequest),
        ]);
    }

    /**
     * @param UuidInterface $flowUuid
     * @param CodeRepository $codeRepository
     * @param CodeRepository\PullRequest $pullRequest
     *
     * @return string[]
     */
    private function getPullRequestLabelNames(UuidInterface $flowUuid, CodeRepository $codeRepository, CodeRepository\PullRequest $pullRequest)
    {
        if (!$codeRepository instanceof CodeRepository\GitHub\GitHubCodeRepository) {
            return [];
        }

        try {
            $client = $this->gitHubClientFactory->createClientForFlow($flowUuid);
        } catch (UserCredentialsNotFound $e) {
            $this->logger->warning('Unable to get pull-request labels, credentials not found', [
                'exception' => $e,
            ]);

            return [];
        }

        try {
            $labels = $client->issue()->labels()->all(
                $codeRepository->getOrganisation(),
                $codeRepository->getName(),
                $pullRequest->getIdentifier()
            );
        } catch (\Exception $e) {
            $this->logger->error('Unable to get pull-request labels, the communication with the GH API wasn\'t successful', [
                'exception' => $e,
            ]);

            return [];
        }

        if (!is_array($labels)) {
            $this->logger->error('Received a non-array response from GH API', [
                'response' => $labels,
            ]);

            return [];
        }

        return array_map(function (array $label) {
            return $label['name'];
        }, $labels);
    }
}
