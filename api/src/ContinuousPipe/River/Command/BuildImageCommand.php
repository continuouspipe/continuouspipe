<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\Builder\Request\BuildRequest;
use Rhumsaa\Uuid\Uuid;

class BuildImageCommand
{
    /**
     * @var BuildRequest
     */
    private $buildRequest;
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @param Uuid         $tideUuid
     * @param BuildRequest $buildRequest
     */
    public function __construct(Uuid $tideUuid, BuildRequest $buildRequest)
    {
        $this->buildRequest = $buildRequest;
        $this->tideUuid = $tideUuid;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return BuildRequest
     */
    public function getBuildRequest()
    {
        return $this->buildRequest;
    }
}
