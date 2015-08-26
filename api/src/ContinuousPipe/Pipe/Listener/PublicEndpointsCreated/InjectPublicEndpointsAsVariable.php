<?php

namespace ContinuousPipe\Pipe\Listener\PublicEndpointsCreated;

use ContinuousPipe\Model\Component\EnvironmentVariable;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;

class InjectPublicEndpointsAsVariable
{
    /**
     * @param PublicEndpointsCreated $event
     */
    public function notify(PublicEndpointsCreated $event)
    {
        $context = $event->getDeploymentContext();
        $components = $context->getEnvironment()->getComponents();
        $publicEndpoints = $event->getEndpoints();

        foreach ($components as $component) {
            $specification = $component->getSpecification();
            $environmentVariables = $specification->getEnvironmentVariables();

            foreach ($publicEndpoints as $publicEndpoint) {
                $environmentVariables[] = $this->getEnvironmentVariableForEndpoint($publicEndpoint);
            }

            $specification->setEnvironmentVariables($environmentVariables);
        }
    }

    /**
     * @param PublicEndpoint $endpoint
     *
     * @return EnvironmentVariable
     */
    private function getEnvironmentVariableForEndpoint(PublicEndpoint $endpoint)
    {
        $variableName = sprintf('SERVICE_%s_PUBLIC_ENDPOINT', strtoupper($endpoint->getName()));

        return new EnvironmentVariable($variableName, $endpoint->getAddress());
    }
}
