<?php

namespace ContinuousPipe\River\Task\Deploy\Command;

use ContinuousPipe\River\Task\Deploy\DeployContext;
use Rhumsaa\Uuid\Uuid;

class StartDeploymentCommand
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var DeployContext
     */
    private $deployContext;

    /**
     * @param Uuid $tideUuid
     * @param DeployContext $deployContext
     */
    public function __construct(Uuid $tideUuid, DeployContext $deployContext)
    {
        $this->tideUuid = $tideUuid;
        $this->deployContext = $deployContext;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return DeployContext
     */
    public function getDeployContext()
    {
        return $this->deployContext;
    }
}
