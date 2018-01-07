<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName;

use ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName\EnvironmentNamingStrategy;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\Cluster;

class DefaultEnvironmentExpressionDecorator implements EnvironmentNamingStrategy
{
    /**
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     */
    public function __construct(EnvironmentNamingStrategy $environmentNamingStrategy)
    {
        $this->environmentNamingStrategy = $environmentNamingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(Tide $tide, Cluster $cluster, $expression = null)
    {
        if (null === $expression) {
            if (null !== ($prefix = $this->getClusterPolicyEnvironmentPrefix($cluster))) {
                $prefix = $prefix.Hashifier::hash($tide->getFlowUuid()->toString(), 5).'-';
            } else {
                // We keep the full UUID for BC reasons.
                $prefix = $tide->getFlowUuid()->toString().'-';
            }

            $expression = $this->expressionForPrefix($prefix);
        }

        return $this->environmentNamingStrategy->getName($tide, $cluster, $expression);
    }

    private function getClusterPolicyEnvironmentPrefix(Cluster $cluster)
    {
        foreach ($cluster->getPolicies() as $policy) {
            if ($policy->getName() == 'environment') {
                $policyConfiguration = $policy->getConfiguration();

                if (isset($policyConfiguration['prefix'])) {
                    return $policyConfiguration['prefix'];
                }
            }
        }

        return null;
    }

    private function expressionForPrefix(string $prefix) : string
    {
        return '\''.$prefix.'\' ~ code_reference.branch';
    }
}
