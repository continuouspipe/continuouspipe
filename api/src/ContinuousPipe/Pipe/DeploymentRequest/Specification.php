<?php

namespace ContinuousPipe\Pipe\DeploymentRequest;

use ContinuousPipe\Model\Component;

class Specification
{
    /**
     * Components expected to be deployed.
     *
     * @var Component[]
     */
    private $components;

    /**
     * @return \ContinuousPipe\Model\Component[]
     */
    public function getComponents()
    {
        return $this->components;
    }
}
