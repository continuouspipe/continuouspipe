<?php

namespace ContinuousPipe\River\Tests\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class FakePullRequestResolver implements PullRequestResolver
{
    private $resolution = [];

    /**
     * {@inheritdoc}
     */
    public function findPullRequestWithHeadReference(FlatFlow $flow, CodeReference $codeReference)
    {
        return $this->resolution;
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
