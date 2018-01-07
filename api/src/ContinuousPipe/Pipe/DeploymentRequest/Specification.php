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
     * @param Component[] $components
     */
    public function __construct(array $components)
    {
        $this->components = $components;
    }

    /**
     * @return \ContinuousPipe\Model\Component[]
     */
    public function getComponents()
    {
        return $this->components ?: [];
    }
}
