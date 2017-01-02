<?php

namespace ContinuousPipe\River\Tests\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\View\Tide;

class FakePullRequestResolver implements PullRequestResolver
{
    private $resolution = [];

    /**
     * {@inheritdoc}
     */
    public function findPullRequestWithHeadReference(Tide $tide) : array
    {
        return $this->resolution;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide): bool
    {
        return true;
    }

    /**
     * Updates the future resolution.
     *
     * @param array $resolution
     */
    public function willResolve(array $resolution)
    {
        $this->resolution = $resolution;
    }
}
