<?php

namespace ContinuousPipe\River\Pipeline;

use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;

interface PipelineTideGenerator
{
    /**
     * Generate tides from the given tide generation request.
     *
     * @param TideGenerationRequest $request
     *
     * @throws TideGenerationException
     * @throws TideConfigurationException
     *
     * @return Tide[]
     */
    public function generate(TideGenerationRequest $request) : array;
}
