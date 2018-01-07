<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest;

use ContinuousPipe\Pipe\DeploymentRequest\Target;
use ContinuousPipe\River\Pipe\DeploymentRequest\Cluster\ClusterResolutionException;
use ContinuousPipe\River\Pipe\DeploymentRequest\Cluster\TargetClusterResolver;
use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName\EnvironmentNamingStrategy;
use ContinuousPipe\River\Task\Deploy\Naming\UnresolvedEnvironmentNameException;
use ContinuousPipe\River\Tide;

class DefaultTargetEnvironmentFactory implements TargetEnvironmentFactory
{
    /**
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @var TargetClusterResolver
     */
    private $targetClusterResolver;

    /**
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     * @param TargetClusterResolver $targetClusterResolver
     */
    public function __construct(EnvironmentNamingStrategy $environmentNamingStrategy, TargetClusterResolver $targetClusterResolver)
    {
        $this->environmentNamingStrategy = $environmentNamingStrategy;
        $this->targetClusterResolver = $targetClusterResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, EnvironmentAwareConfiguration $configuration) : Target
    {
        try {
            $cluster = $this->targetClusterResolver->getClusterIdentifier($tide, $configuration);
        } catch (ClusterResolutionException $e) {
            throw new DeploymentRequestException($e->getMessage(), $e->getCode(), $e);
        }

        try {
            $name = $this->environmentNamingStrategy->getName(
                $tide,
                $cluster,
                $configuration->getEnvironmentName()
            );
        } catch (UnresolvedEnvironmentNameException $e) {
            throw new DeploymentRequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new Target(
            $name,
            $cluster->getIdentifier(),
            [
                'flow' => (string)$tide->getFlowUuid(),
            ]
        );
    }
}
