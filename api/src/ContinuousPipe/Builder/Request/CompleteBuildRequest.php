<?php

namespace ContinuousPipe\Builder\Request;

use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuildStatus;
use ContinuousPipe\Builder\Logging;
use Ramsey\Uuid\Uuid;

class CompleteBuildRequest
{
    /**
     * @var Uuid
     */
    private $buildId;

    /**
     * @var GoogleContainerBuildStatus
     */
    private $status;

    public function getBuildId()
    {
        return $this->buildId;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
