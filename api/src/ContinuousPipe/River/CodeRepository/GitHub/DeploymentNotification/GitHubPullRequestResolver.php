<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\GitHub\GitHubClientFactory;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\User\User;
use GitHub\WebHook\Model\PullRequest;
use JMS\Serializer\Serializer;

class GitHubPullRequestResolver implements PullRequestResolver
{
    /**
     * @var GitHubClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param GitHubClientFactory $gitHubClientFactory
     * @param Serializer          $serializer
     */
    public function __construct(GitHubClientFactory $gitHubClientFactory, Serializer $serializer)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function findPullRequestWithHeadReference(User $user, CodeReference $codeReference)
    {
        $client = $this->gitHubClientFactory->createClientForUser($user);
        $gitHubRepository = $this->getGitHubRepository($codeReference);

        $rawPullRequests = $client->pullRequests()->all(
            $gitHubRepository->getOwner()->getLogin(),
            $gitHubRepository->getName(),
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
}
