<?php

namespace ContinuousPipe\River\Tests\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification\PullRequestResolver;
use ContinuousPipe\Security\Credentials\Bucket;

class FakePullRequestResolver implements PullRequestResolver
{
    private $resolution = [];

    /**
     * {@inheritdoc}
     */
    public function findPullRequestWithHeadReference(CodeReference $codeReference, Bucket $credentialsBucket)
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
