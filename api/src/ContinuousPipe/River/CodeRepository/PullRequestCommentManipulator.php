<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\View\Tide;

interface PullRequestCommentManipulator
{
    /**
     * @param Tide        $tide
     * @param PullRequest $pullRequest
     * @param string      $contents
     *
     * @throws CodeRepositoryException
     *
     * @return string
     */
    public function writeComment(Tide $tide, PullRequest $pullRequest, string $contents) : string;

    /**
     * @param Tide        $tide
     * @param PullRequest $pullRequest
     * @param string      $identifier
     *
     * @throws CodeRepositoryException
     */
    public function deleteComment(Tide $tide, PullRequest $pullRequest, string $identifier);

    /**
     * @param Tide $tide
     *
     * @return bool
     */
    public function supports(Tide $tide) : bool;
}
