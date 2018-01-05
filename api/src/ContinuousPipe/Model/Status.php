<?php

namespace ContinuousPipe\Model;

interface Status
{
    const HEALTHY = 'healthy';
    const WARNING = 'warning';
    const UNHEALTHY = 'unhealthy';
    const UNKNOWN = 'unknown';

    /**
     * @return string
     */
    public function getStatus();
}
