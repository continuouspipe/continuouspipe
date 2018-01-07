<?php

namespace ContinuousPipe\River\View\Storage;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\PullRequest;
use Ramsey\Uuid\UuidInterface;

interface PullRequestViewStorage
{
    public function save(UuidInterface $flowUuid, CodeRepository $repository);

    public function add(UuidInterface $flowUuid, PullRequest $pullRequest);

    public function deletePullRequest(UuidInterface $flowUuid, PullRequest $pullRequest);

    public function deleteBranch(UuidInterface $flowUuid, string $branchName);
}
