<?php
/**
 * Created by PhpStorm.
 * User: rmiller
 * Date: 30/05/2017
 * Time: 17:07
 */
namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;

interface EndpointConfigurator
{
    /**
     * @param array $endpointConfiguration
     * @return array
     * @throws TideGenerationException
     */
    public function checkConfiguration(array $endpointConfiguration);

    /**
     * @param array $endpointConfiguration
     * @param TaskContext $context
     * @return array
     * @throws TideGenerationException
     */
    public function addHost(array $endpointConfiguration, TaskContext $context);
}