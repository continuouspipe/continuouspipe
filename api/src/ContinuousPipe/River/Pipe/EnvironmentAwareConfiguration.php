<?php

namespace ContinuousPipe\River\Pipe;

interface EnvironmentAwareConfiguration
{
    /**
     * @return string
     */
    public function getEnvironmentName();

    /**
     * @return string|null
     */
    public function getClusterIdentifier();
}
