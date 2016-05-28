<?php

namespace ContinuousPipe\River\Tide\ExternalRelation\GitHub;

use ContinuousPipe\River\CodeRepository\PullRequestResolver as CodeRepositoryPullRequestResolver;
use ContinuousPipe\River\Tide\ExternalRelation\ExternalRelationResolver;
use ContinuousPipe\River\View\TideRepository;
use GitHub\WebHook\Model\PullRequest as GitHubPullRequest;
use Ramsey\Uuid\Uuid;

class PullRequestResolver implements ExternalRelationResolver
{
    /**
     * @var CodeRepositoryPullRequestResolver
     */
    private $codeRepositoryPullRequestResolver;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param CodeRepositoryPullRequestResolver $codeRepositoryPullRequestResolver
     * @param TideRepository                    $tideRepository
     */
    public function __construct(CodeRepositoryPullRequestResolver $codeRepositoryPullRequestResolver, TideRepository $tideRepository)
    {
        $this->codeRepositoryPullRequestResolver = $codeRepositoryPullRequestResolver;
        $this->tideRepository = $tideRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelations(Uuid $tideUuid)
    {
        $tide = $this->tideRepository->find($tideUuid);
        $codeRepository = $tide->getCodeReference()->getRepository();
        $gitHubPullRequests = $this->codeRepositoryPullRequestResolver->findPullRequestWithHeadReference(
            $tide->getCodeReference(),
            $tide->getTeam()
        );

        return array_map(function (GitHubPullRequest $pullRequest) use ($codeRepository) {
            return PullRequest::fromGitHub($codeRepository, $pullRequest);
        }, $gitHubPullRequests);
    }
}
