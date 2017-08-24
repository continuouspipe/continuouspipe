<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface ResourceUsageResolver
{
    /**
     * @param FlatFlow $flow
     *
     * @throws ResourcesException
     *
     * @return ResourceUsage
     */
    public function forFlow(FlatFlow $flow) : ResourceUsage;
}
