<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\CodeRepository\PullRequestCommentManipulator;
use ContinuousPipe\River\View\Tide;

class DelegatesToPullRequestCommentManipulatorThatSupports implements PullRequestCommentManipulator
{
    /**
     * @var array|PullRequestCommentManipulator[]
     */
    private $manipulators;

    /**
     * @param PullRequestCommentManipulator[] $manipulators
     */
    public function __construct(array $manipulators)
    {
        $this->manipulators = $manipulators;
    }

    public function writeComment(Tide $tide, PullRequest $pullRequest, string $contents): string
    {
        foreach ($this->manipulators as $manipulator) {
            if ($manipulator->supports($tide)) {
                return $manipulator->writeComment($tide, $pullRequest, $contents);
            }
        }

        throw new CodeRepositoryException('No pull request comment manipulators supports this tide');
    }

    public function deleteComment(Tide $tide, PullRequest $pullRequest, string $identifier)
    {
        foreach ($this->manipulators as $manipulator) {
            if ($manipulator->supports($tide)) {
                return $manipulator->deleteComment($tide, $pullRequest, $identifier);
            }
        }

        throw new CodeRepositoryException('No pull request comment manipulators supports this tide');
    }

    public function supports(Tide $tide): bool
    {
        foreach ($this->manipulators as $manipulator) {
            if ($manipulator->supports($tide)) {
                return true;
            }
        }

        return false;
    }
}
