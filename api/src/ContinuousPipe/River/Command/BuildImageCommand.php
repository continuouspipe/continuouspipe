<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\Builder\Request\BuildRequest;

class BuildImageCommand
{
    /**
     * @var BuildRequest
     */
    private $buildRequest;

    /**
     * @param BuildRequest $buildRequest
     */
    public function __construct(BuildRequest $buildRequest)
    {
        $this->buildRequest = $buildRequest;
    }

    /**
     * @return BuildRequest
     */
    public function getBuildRequest()
    {
        return $this->buildRequest;
    }
}
