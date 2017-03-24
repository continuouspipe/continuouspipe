<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use GitHub\WebHook\Model\PullRequest;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\Serializer;
use Ramsey\Uuid\UuidInterface;

class GitHubPullRequestResolver implements PullRequestResolver
{
    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param ClientFactory $gitHubClientFactory
     * @param Serializer    $serializer
     */
    public function __construct(ClientFactory $gitHubClientFactory, Serializer $serializer)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function findPullRequestWithHeadReference(UuidInterface $flowUuid, CodeReference $codeReference) : array
    {
        $client = $this->gitHubClientFactory->createClientForFlow($flowUuid);

        $repository = $codeReference->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new \RuntimeException(sprintf(
                'Repository of type "%s" not supported',
                get_class($repository)
            ));
        }

        try {
            $rawPullRequests = $client->pullRequests()->all(
                $repository->getOrganisation(),
                $repository->getName(),
                [
                    'state' => 'open',
                ]
            );
        } catch (RequestException $e) {
            throw new CodeRepositoryException($e->getMessage(), $e->getCode(), $e);
        }

        $jsonEncoded = json_encode($rawPullRequests);
        $pullRequests = $this->serializer->deserialize($jsonEncoded, 'array<'.PullRequest::class.'>', 'json');

        $matchingPullRequests = array_values(array_filter($pullRequests, function (PullRequest $pullRequest) use ($codeReference) {
            return $codeReference->getBranch() == $pullRequest->getHead()->getReference();
        }));

        return array_map(function (PullRequest $pullRequest) {
            return new \ContinuousPipe\River\CodeRepository\PullRequest(
                $pullRequest->getNumber(),
                $pullRequest->getTitle()
            );
        }, $matchingPullRequests);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UuidInterface $flowUuid, CodeReference $codeReference): bool
    {
        return $codeReference->getRepository() instanceof GitHubCodeRepository;
    }
}
