<?php

namespace ContinuousPipe\Builder\Archive\Builder;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Request\BuildRequest;

interface ConditionalArchiveBuilder extends ArchiveBuilder
{
    /**
     * Returns true if the builder supports the build request.
     *
     * @param BuildRequest $request
     *
     * @return bool
     */
    public function supports(BuildRequest $request);
}
