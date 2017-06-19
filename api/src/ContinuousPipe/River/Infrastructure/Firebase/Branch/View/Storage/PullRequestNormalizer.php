<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage;

use ContinuousPipe\River\CodeRepository\PullRequest;

class PullRequestNormalizer
{

    public function normalizePullRequests(array $pullRequests)
    {
        return array_combine(
            array_map(
                function (PullRequest $pullRequest) {
                    return hash('sha256', (string) $pullRequest->getBranch());
                },
                $pullRequests
            ),
            array_map(
                [$this, 'normalizePullRequest'],
                $pullRequests
            )
        );
    }

    public function normalizePullRequest(PullRequest $pullRequest)
    {
        return [
            'identifier' => (string) $pullRequest->getIdentifier(),
            'title' => $pullRequest->getTitle(),
            'url' => $pullRequest->getUrl(),
        ];
    }

}