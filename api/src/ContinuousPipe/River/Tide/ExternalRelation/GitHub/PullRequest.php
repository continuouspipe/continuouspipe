<?php

namespace ContinuousPipe\River\Tide\ExternalRelation\GitHub;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Tide\ExternalRelation\ExternalRelation;

class PullRequest implements ExternalRelation
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $link;

    /**
     * @param CodeRepository\PullRequest $pullRequest
     *
     * @return PullRequest
     */
    public static function fromCodeRepository(CodeRepository $codeRepository, CodeRepository\PullRequest $pullRequest)
    {
        $relation = new self();

        if ($codeRepository instanceof GitHubCodeRepository) {
            $relation->type = 'github';
            $relation->link = sprintf(
                'https://github.com/%s/%s/pull/%d',
                $codeRepository->getOrganisation(),
                $codeRepository->getName(),
                $pullRequest->getIdentifier()
            );
        } else {
            $relation->type = 'unknown';
            $relation->link = $codeRepository->getAddress();
        }

        return $relation;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        return $this->link;
    }
}
