<?php

namespace ContinuousPipe\River\Pipeline;

use ContinuousPipe\River\Tide;

interface PipelineTideGenerator
{
    /**
     * Generate tides from the given tide generation request.
     *
     * @param TideGenerationRequest $request
     *
     * @return Tide[]
     */
    public function generate(TideGenerationRequest $request) : array;
}
