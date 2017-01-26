<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequestEnhancer;

use ContinuousPipe\Model\Component\EnvironmentVariable;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Flow\ConfigurationFinalizer\ReplaceEnvironmentVariableValues;
use ContinuousPipe\River\Pipe\DeploymentRequest\DynamicVariable\ServiceVariable;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Tide;

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
        /** @var DeployTask[] $tasks */
        $tasks = $tide->getTasks()->ofType(DeployTask::class);
        $publicEndpointMappings = [];
        foreach ($tasks as $task) {
            foreach ($task->getPublicEndpoints() as $publicEndpoint) {
                $serviceVariable = ServiceVariable::fromNameAndAddress(
                    $publicEndpoint->getName(), $publicEndpoint->getAddress()
                );

                $publicEndpointMappings[$serviceVariable->getVariableName()] = (string)$serviceVariable;
            }
        }

        foreach ($deploymentRequest->getSpecification()->getComponents() as $component) {
            $replacedVariables = array_map(function (EnvironmentVariable $environmentVariable) use ($publicEndpointMappings) {
                $value = $environmentVariable->getValue();

                return new EnvironmentVariable(
                    $environmentVariable->getName(),
                    ReplaceEnvironmentVariableValues::replaceVariables(null === $value ? '' : (string) $value, $publicEndpointMappings)
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
