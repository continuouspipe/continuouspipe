<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\Security\Credentials\BucketContainer;
use Github\Client;
use GitHub\WebHook\Model\PullRequest;
use JMS\Serializer\Serializer;

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
    public function findPullRequestWithHeadReferenceAndBucketContainer(CodeReference $codeReference, BucketContainer $bucketContainer)
    {
        $client = $this->gitHubClientFactory->createClientFromBucketUuid($bucketContainer->getBucketUuid());

        return $this->findPullRequestFromClient($client, $codeReference);
    }

    /**
     * {@inheritdoc}
     */
    public function findPullRequestWithHeadReference(Flow $flow, CodeReference $codeReference)
    {
        $client = $this->gitHubClientFactory->createClientForFlow($flow);

        return $this->findPullRequestFromClient($client, $codeReference);
    }

    /**
     * @param CodeReference $codeReference
     *
     * @return \GitHub\WebHook\Model\Repository
     */
    private function getGitHubRepository(CodeReference $codeReference)
    {
        $repository = $codeReference->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new \RuntimeException(sprintf(
                'Repository of type "%s" not supported',
                get_class($repository)
            ));
        }

        return $repository->getGitHubRepository();
    }

    /**
     * @param Client $client
     * @param CodeReference $codeReference
     *
     * @return array
     */
    private function findPullRequestFromClient(Client $client, CodeReference $codeReference)
    {
        $gitHubRepository = $this->getGitHubRepository($codeReference);

        $rawPullRequests = $client->pullRequests()->all(
            $gitHubRepository->getOwner()->getLogin(),
            $gitHubRepository->getName(),
            [
                'state' => 'open',
            ]
        );

        $jsonEncoded = json_encode($rawPullRequests);
        $pullRequests = $this->serializer->deserialize($jsonEncoded, 'array<' . PullRequest::class . '>', 'json');

        return array_values(array_filter($pullRequests, function (PullRequest $pullRequest) use ($codeReference) {
            return $codeReference->getCommitSha() == $pullRequest->getHead()->getSha1();
        }));
    }
}
