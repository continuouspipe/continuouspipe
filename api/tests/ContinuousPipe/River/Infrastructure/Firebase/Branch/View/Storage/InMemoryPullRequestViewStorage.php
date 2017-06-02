<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage;

use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\CodeRepository\BranchQuery;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\View\Storage\BranchViewStorage;
use ContinuousPipe\River\View\Storage\PullRequestViewStorage;
use ContinuousPipe\River\View\Tide;
use Ramsey\Uuid\UuidInterface;

/**
 * This in-memory implementation is for testing purposes only.
 */
class InMemoryPullRequestViewStorage implements PullRequestViewStorage
{
    private $savedPullRequests = [];

    public function add(UuidInterface $flowUuid, PullRequest $pullRequest)
    {
        $this->savedPullRequests[(string) $flowUuid][$pullRequest->getIdentifier()] = $pullRequest;
    }

    public function updateTide(Tide $tide)
    {
        $flowUuid = $tide->getFlowUuid();
        $branchName = $tide->getCodeReference()->getBranch();

        if (!isset($this->savedPullRequests[(string) $flowUuid])) {
            return;
        }

        $this->savedPullRequests[(string) $flowUuid] = array_map(function (PullRequest $pullRequest) use ($branchName, $tide) {
            if ((string) $pullRequest->getBranch() == $branchName) {
                return $pullRequest->withBranch($pullRequest->getBranch()->withTide($tide));
            }

            return $pullRequest;
        }, $this->savedPullRequests[(string) $flowUuid]);
    }

    public function wasPullRequestSaved(UuidInterface $flowUuid, PullRequest $pullRequest)
    {
        if (!isset($this->savedPullRequests[(string) $flowUuid])) {
            return false;
        }

        if (!isset($this->savedPullRequests[(string) $flowUuid][$pullRequest->getIdentifier()])) {
            return false;
        }

        return array_intersect($pullRequest->getBranch()->getTideUuids(), $this->savedPullRequests[(string) $flowUuid][$pullRequest->getIdentifier()]->getBranch()->getTideUuids()) == $pullRequest->getBranch()->getTideUuids();
    }

}