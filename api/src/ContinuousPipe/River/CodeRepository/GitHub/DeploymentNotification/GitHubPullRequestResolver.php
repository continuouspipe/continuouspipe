<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Github\Client;
use GitHub\WebHook\Model\PullRequest;
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
    public function findPullRequestWithHeadReference(UuidInterface $flowUuid, CodeReference $codeReference)
    {
        $client = $this->gitHubClientFactory->createClientForFlow($flowUuid);

        return $this->findPullRequestFromClient($client, $codeReference);
    }

    /**
     * @param Client        $client
     * @param CodeReference $codeReference
     *
     * @return array
     */
    private function findPullRequestFromClient(Client $client, CodeReference $codeReference)
    {
        $repository = $codeReference->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new \RuntimeException(sprintf(
                'Repository of type "%s" not supported',
                get_class($repository)
            ));
        }

        $rawPullRequests = $client->pullRequests()->all(
            $repository->getOrganisation(),
            $repository->getName(),
            [
                'state' => 'open',
            ]
        );

        $jsonEncoded = json_encode($rawPullRequests);
        $pullRequests = $this->serializer->deserialize($jsonEncoded, 'array<'.PullRequest::class.'>', 'json');

        return array_values(array_filter($pullRequests, function (PullRequest $pullRequest) use ($codeReference) {
            return $codeReference->getCommitSha() == $pullRequest->getHead()->getSha1();
        }));
    }
}
