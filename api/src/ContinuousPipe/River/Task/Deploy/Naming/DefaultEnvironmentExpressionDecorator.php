<?php

namespace ContinuousPipe\River\Task\Deploy\Naming;

use ContinuousPipe\Model\Environment;
use Ramsey\Uuid\Uuid;

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
    public function getName(Uuid $tideUuid, $expression = null)
    {
        if (null === $expression) {
            $expression = $this->defaultExpression;
        }

        return $this->environmentNamingStrategy->getName($tideUuid, $expression);
    }

    /**
     * FIXME That should be (re)moved from here.
     *
     * {@inheritdoc}
     */
    public function isEnvironmentPartOfFlow(Uuid $flowUuid, Environment $environment)
    {
        return strpos($environment->getName(), (string) $flowUuid) === 0;
    }
}
