<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Task\Build\BuildTaskConfiguration;
use LogStream\Log;
use Ramsey\Uuid\UuidInterface;

interface BuildRequestCreator
{
    /**
     * @param UuidInterface $tideUuid
     * @param CodeReference $codeReference
     * @param BuildTaskConfiguration $configuration
     * @param UuidInterface $credentialsBucketUuid
     * @param Log $parentLog
     *
     * @return BuildRequest[]
     */
    public function createBuildRequests(
        UuidInterface $tideUuid,
        CodeReference $codeReference,
        BuildTaskConfiguration $configuration,
        UuidInterface $credentialsBucketUuid,
        Log $parentLog
    ) : array;
}
