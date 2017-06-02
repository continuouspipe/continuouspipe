<?php

namespace ContinuousPipe\River\View\Storage;

use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\View\Tide;
use Ramsey\Uuid\UuidInterface;

interface PullRequestViewStorage
{
    public function add(UuidInterface $flowUuid, PullRequest $pullRequest);

    public function updateTide(Tide $tide);

    public function deletePullRequest(UuidInterface $flowUuid, PullRequest $pullRequest);

    public function deleteBranch(UuidInterface $flowUuid, string $branchName);
}
