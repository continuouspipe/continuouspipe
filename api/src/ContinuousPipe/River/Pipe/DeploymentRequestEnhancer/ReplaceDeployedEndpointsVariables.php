<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequestEnhancer;

use ContinuousPipe\Model\Component\EnvironmentVariable;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Flow\ConfigurationFinalizer\ReplaceEnvironmentVariableValues;
use ContinuousPipe\River\Pipe\DeploymentRequest\DynamicVariable\ServiceVariable;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\TaskDetails;
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
    public function enhance(Tide $tide, TaskDetails $taskDetails, DeploymentRequest $deploymentRequest)
    {
        $publicEndpointMappings = $this->getPublicEndpointVariablesFromTide($tide);
        $availableVariables = array_merge(
            $publicEndpointMappings,
            $this->getEnvironmentNameVariableFromDeployment($taskDetails, $deploymentRequest),
            $this->getEnvironmentNameVariablesFromTide($tide)
        );

        foreach ($deploymentRequest->getSpecification()->getComponents() as $component) {
            $replacedVariables = $this->replaceVariablesInComponentEnvironmentVariables($component->getSpecification()->getEnvironmentVariables(), $availableVariables);

            foreach ($publicEndpointMappings as $name => $value) {
                $replacedVariables[] = new EnvironmentVariable($name, $value);
            }

            $component->getSpecification()->setEnvironmentVariables($replacedVariables);
        }

        return $deploymentRequest;
    }

    /**
     * @param EnvironmentVariable[] $componentEnvironmentVariables
     * @param array<string,string> $availableVariables
     *
     * @return EnvironmentVariable[]
     */
    private function replaceVariablesInComponentEnvironmentVariables(array $componentEnvironmentVariables, array $availableVariables)
    {
        return array_map(function (EnvironmentVariable $environmentVariable) use ($availableVariables) {
            $value = $environmentVariable->getValue();

            return new EnvironmentVariable(
                $environmentVariable->getName(),
                ReplaceEnvironmentVariableValues::replaceVariables(null === $value ? '' : (string) $value, $availableVariables, false)
            );
        }, $componentEnvironmentVariables);
    }

    private function getPublicEndpointVariablesFromTide(Tide $tide) : array
    {
        $publicEndpointMappings = [];
        foreach ($this->tideDeployTasks($tide) as $task) {
            foreach ($task->getPublicEndpoints() as $publicEndpoint) {
                $serviceVariable = ServiceVariable::fromPublicEndpoint($publicEndpoint);

                $publicEndpointMappings[$serviceVariable->getVariableName()] = $serviceVariable->getAddress();
            }
        }

        return $publicEndpointMappings;
    }

    private function getEnvironmentNameVariablesFromTide(Tide $tide) : array
    {
        return array_reduce($this->tideDeployTasks($tide), function (array $variables, DeployTask $task) {
            if (null !== ($startedDeployment = $task->getStartedDeployment())) {
                $variables = array_merge($variables, $this->getEnvironmentNameVariableFromDeployment(
                    new TaskDetails($task->getIdentifier(), $task->getLogIdentifier()),
                    $startedDeployment->getRequest()
                ));
            }

            return $variables;
        }, []);
    }

    private function getEnvironmentNameVariableFromDeployment(TaskDetails $taskDetails, DeploymentRequest $deploymentRequest) : array
    {
        $environmentName = $deploymentRequest->getTarget()->getEnvironmentName();

        $normalizedTaskIdentifier = str_replace('-', '_', strtoupper($taskDetails->getIdentifier()));
        $variableName = '__TASK_'.$normalizedTaskIdentifier.'_TARGET_ENVIRONMENT_NAME';

        return [
            $variableName => $environmentName,
        ];
    }

    /**
     * @param Tide $tide
     *
     * @return DeployTask[]
     */
    private function tideDeployTasks(Tide $tide)
    {
        return $tide->getTasks()->ofType(DeployTask::class);
    }
}
