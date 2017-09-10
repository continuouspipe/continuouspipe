<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequestEnhancer;

use ContinuousPipe\Model\Component\EnvironmentVariable;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;

class AddVariablesAsEnvironmentVariables implements DeploymentRequestEnhancer
{
    /**
     * @var DeploymentRequestEnhancer
     */
    private $decoratedEnhancer;

    /**
     * @param DeploymentRequestEnhancer $decoratedEnhancer
     */
    public function __construct(DeploymentRequestEnhancer $decoratedEnhancer)
    {
        $this->decoratedEnhancer = $decoratedEnhancer;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Tide $tide, TaskDetails $taskDetails, DeploymentRequest $deploymentRequest)
    {
        foreach ($deploymentRequest->getSpecification()->getComponents() as $component) {
            $component->getSpecification()->setEnvironmentVariables($this->addDefaultComponentEnvironmentVariables(
                $tide,
                $component->getSpecification()->getEnvironmentVariables()
            ));
        }

        return $this->decoratedEnhancer->enhance($tide, $taskDetails, $deploymentRequest);
    }

    private function addDefaultComponentEnvironmentVariables(Tide $tide, array $variables)
    {
        $tideVariables = $tide->getConfiguration()['variables'];

        foreach ($tideVariables as $tideVariable) {
            if ($tideVariable['default_as_environment_variable']) {
                if (!$this->hasEnvironmentVariable($variables, $tideVariable['name'])) {
                    $variables[] = new EnvironmentVariable($tideVariable['name'], $tideVariable['value']);
                }
            }
        }

        return $variables;
    }

    /**
     * @param EnvironmentVariable[] $variables
     * @param string $name
     *
     * @return bool
     */
    private function hasEnvironmentVariable(array $variables, string $name) : bool
    {
        foreach ($variables as $variable) {
            if ($variable->getName() == $name) {
                return true;
            }
        }

        return false;
    }
}
