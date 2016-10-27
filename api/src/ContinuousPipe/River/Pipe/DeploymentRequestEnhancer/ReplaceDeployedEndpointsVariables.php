<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequestEnhancer;

use ContinuousPipe\Model\Component\EnvironmentVariable;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Flow\ConfigurationFinalizer\ReplaceEnvironmentVariableValues;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\View\Tide;

class ReplaceDeployedEndpointsVariables implements DeploymentRequestEnhancer
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Tide $tide, DeploymentRequest $deploymentRequest)
    {
        $successfulDeployments = $this->eventStore->findByTideUuidAndType($tide->getUuid(), DeploymentSuccessful::class);
        $publicEndpointMappings = array_reduce($successfulDeployments, function ($carry, DeploymentSuccessful $event) {
            foreach ($event->getDeployment()->getPublicEndpoints() as $publicEndpoint) {
                $serviceName = $publicEndpoint->getName();
                $environName = sprintf('SERVICE_%s_PUBLIC_ENDPOINT', strtoupper($serviceName));

                $carry[$environName] = $publicEndpoint->getAddress();
            }

            return $carry;
        }, []);

        foreach ($deploymentRequest->getSpecification()->getComponents() as $component) {
            $replacedVariables = array_map(function(EnvironmentVariable $environmentVariable) use ($publicEndpointMappings) {
                return new EnvironmentVariable(
                    $environmentVariable->getName(),
                    ReplaceEnvironmentVariableValues::replaceVariables($environmentVariable->getValue() ?: '', $publicEndpointMappings)
                );
            }, $component->getSpecification()->getEnvironmentVariables());

            foreach ($publicEndpointMappings as $name => $value) {
                $replacedVariables[] = new EnvironmentVariable($name, $value);
            }

            $component->getSpecification()->setEnvironmentVariables($replacedVariables);
        }

        return $deploymentRequest;
    }
}
