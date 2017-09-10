<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;

interface EndpointConfigurationEnhancer
{
    /**
     * @param array $endpointConfiguration
     * @param TaskContext $context
     *
     * @return array
     *
     * @throws TideGenerationException
     */
    public function enhance(array $endpointConfiguration, TaskContext $context);
}
