<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequestEnhancer;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Component\EnvironmentVariable;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\River\Flow\Variable\FlowVariableResolver;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;

class AddVariablesAsEnvironmentVariables implements DeploymentRequestEnhancer
{
    private $decoratedEnhancer;
    private $flowVariableResolver;

    public function __construct(DeploymentRequestEnhancer $decoratedEnhancer, FlowVariableResolver $flowVariableResolver)
    {
        $this->decoratedEnhancer = $decoratedEnhancer;
        $this->flowVariableResolver = $flowVariableResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Tide $tide, TaskDetails $taskDetails, DeploymentRequest $deploymentRequest)
    {
        foreach ($deploymentRequest->getSpecification()->getComponents() as $component) {
            $component->getSpecification()->setEnvironmentVariables($this->addDefaultComponentEnvironmentVariables(
                $tide,
                $component,
                $component->getSpecification()->getEnvironmentVariables()
            ));
        }

        return $this->decoratedEnhancer->enhance($tide, $taskDetails, $deploymentRequest);
    }

    /**
     * @param Tide $tide
     * @param EnvironmentVariable[] $variables
     * @param Component $component
     *
     * @return EnvironmentVariable[]
     */
    private function addDefaultComponentEnvironmentVariables(Tide $tide, Component $component, array $variables) : array
    {
        $tideVariables = $tide->getConfiguration()['variables'];
        $context = $this->flowVariableResolver->createContext($tide->getFlowUuid(), $tide->getCodeReference());

        foreach ($tideVariables as $tideVariable) {
            if ($this->shouldBeAddedAsDefault($component, $tideVariable, $context)) {
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

    private function shouldBeAddedAsDefault(Component $component, array $tideVariable, Tide\Configuration\ArrayObject $context) : bool
    {
        if (isset($tideVariable['condition'])) {
            $conditionMatches = (bool) $this->flowVariableResolver->resolveExpression($tideVariable['condition'], $context);

            if (!$conditionMatches) {
                return false;
            }
        }

        foreach ($tideVariable['as_environment_variable'] as $serviceName) {
            if ($serviceName == '*' || $serviceName == $component->getName()) {
                return true;
            }
        }

        return false;
    }
}
