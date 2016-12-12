<?php

namespace ContinuousPipe\River\Tide\ExternalRelation\GitHub;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Tide\ExternalRelation\ExternalRelation;
use GitHub\WebHook\Model\PullRequest as GitHubPullRequest;
use JMS\Serializer\Annotation as JMS;

class PullRequest implements ExternalRelation
{
    /**
     * @var string
     */
    private $link;

    /**
     * @param GitHubPullRequest $pullRequest
     *
     * @return PullRequest
     */
    public static function fromGitHub(GitHubCodeRepository $codeRepository, GitHubPullRequest $pullRequest)
    {
        $relation = new self();
        $relation->link = sprintf(
            'https://github.com/%s/%s/pull/%d',
            $codeRepository->getOrganisation(),
            $codeRepository->getName(),
            $pullRequest->getNumber()
        );

        return $relation;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("type")
     *
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'github';
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        return $this->link;
    }
}
