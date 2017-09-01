<?php

namespace ContinuousPipe\River\Pipe;

interface EnvironmentAwareConfiguration
{
    /**
     * @return string|null
     */
    public function getEnvironmentName();

    /**
     * @return string|null
     */
    public function getClusterIdentifier();
}
