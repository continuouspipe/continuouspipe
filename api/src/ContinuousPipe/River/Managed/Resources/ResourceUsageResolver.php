<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\Model\Component\Resources;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface ResourceUsageResolver
{
    /**
     * @param FlatFlow $flow
     *
     * @throws ResourcesException
     *
     * @return Resources
     */
    public function forFlow(FlatFlow $flow) : Resources;
}
