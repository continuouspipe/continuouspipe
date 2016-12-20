<?php

namespace ContinuousPipe\River\Pipeline\Command;

use ContinuousPipe\River\Pipeline\TideGenerationRequest;

final class GenerateTides
{
    private $request;

    public function __construct(TideGenerationRequest $request)
    {
        $this->request = $request;
    }

    public function getRequest(): TideGenerationRequest
    {
        return $this->request;
    }
}
