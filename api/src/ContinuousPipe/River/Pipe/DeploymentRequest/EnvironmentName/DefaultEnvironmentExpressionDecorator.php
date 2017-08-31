<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName;

use ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName\EnvironmentNamingStrategy;
use ContinuousPipe\River\Tide;

class DefaultEnvironmentExpressionDecorator implements EnvironmentNamingStrategy
{
    /**
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @var string
     */
    private $defaultExpression;

    /**
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     * @param string                    $defaultExpression
     */
    public function __construct(EnvironmentNamingStrategy $environmentNamingStrategy, $defaultExpression)
    {
        $this->environmentNamingStrategy = $environmentNamingStrategy;
        $this->defaultExpression = $defaultExpression;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(Tide $tide, $expression = null)
    {
        if (null === $expression) {
            $expression = $this->defaultExpression;
        }

        return $this->environmentNamingStrategy->getName($tide, $expression);
    }
}
