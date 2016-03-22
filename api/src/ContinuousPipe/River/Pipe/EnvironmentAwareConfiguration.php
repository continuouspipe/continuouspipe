<?php

namespace ContinuousPipe\River\Pipe;

interface EnvironmentAwareConfiguration
{
    /**
     * @return string
     */
    public function getEnvironmentName();

    /**
     * @return string
     */
    public function getClusterIdentifier();
}
