<?php

namespace spec\ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification\GitHubPullRequestResolver;
use ContinuousPipe\River\GitHub\ClientFactory;
use Github\Api\PullRequest as PullRequestApi;
use Github\Client as GithubClient;
use GitHub\WebHook\Model\CommitReference;
use GitHub\WebHook\Model\PullRequest;
use JMS\Serializer\Serializer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\UuidInterface;

class GitHubPullRequestResolverSpec extends ObjectBehavior
{
    function let(ClientFactory $gitHubClientFactory, Serializer $serializer, GithubClient $client, PullRequestApi $pullRequestApi, PullRequest $pullRequest, CodeReference $codeReference, CommitReference $commitReference, GitHubCodeRepository $gitHubCodeRepository)
    {
        $gitHubCodeRepository->getOrganisation()->willReturn('continuous-pipe');
        $gitHubCodeRepository->getName()->willReturn('river');
        $codeReference->getRepository()->willReturn($gitHubCodeRepository);

        $gitHubClientFactory->createClientForFlow(Argument::any())->willReturn($client);
        $client->pullRequests()->willReturn($pullRequestApi);
        $pullRequestApi->all(Argument::cetera())->willReturn(['does not matter here as it will be passed to the serializer']);
        $serializer->deserialize(Argument::cetera())->willReturn([$pullRequest]);

        $pullRequest->getHead()->willReturn($commitReference);
        $commitReference->getReference()->willReturn('my-precious-branch');
        $pullRequest->getNumber()->willReturn('1');
        $pullRequest->getTitle()->willReturn('My Precious PR');

        $this->beConstructedWith($gitHubClientFactory, $serializer);
    }

    function it_returns_a_pull_request_by_branch_name(UuidInterface $flowUuid, CodeReference $codeReference)
    {
        $codeReference->getBranch()->willReturn('my-precious-branch');

        $this->findPullRequestWithHeadReference($flowUuid, $codeReference)->shouldBeLike([
            new \ContinuousPipe\River\CodeRepository\PullRequest(
                '1',
                'My Precious PR',
                new Branch('my-precious-branch')
            )
        ]);
    }

    function it_does_not_return_anything_if_no_branch_matched(UuidInterface $flowUuid, CodeReference $codeReference)
    {
        $codeReference->getBranch()->willReturn('my-not-so-precious-branch');

        $this->findPullRequestWithHeadReference($flowUuid, $codeReference)->shouldBeLike([]);
    }
}
