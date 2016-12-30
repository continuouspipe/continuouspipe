<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\View\Tide;

interface PullRequestCommentManipulator
{
    public function writeComment(Tide $tide, PullRequest $pullRequest, string $contents) : string;

    public function deleteComment(Tide $tide, PullRequest $pullRequest, string $identifier);

    public function supports(Tide $tide) : bool;
}
