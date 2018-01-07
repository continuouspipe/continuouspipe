<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName;

use Cocur\Slugify\Slugify;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\Cluster;

class SlugifyNamingStrategyDecorator implements EnvironmentNamingStrategy
{
    /**
     * @var EnvironmentNamingStrategy
     */
    private $decoratedStrategy;

    /**
     * @param EnvironmentNamingStrategy $decoratedStrategy
     */
    public function __construct(EnvironmentNamingStrategy $decoratedStrategy)
    {
        $this->decoratedStrategy = $decoratedStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(Tide $tide, Cluster $cluster, $expression = null)
    {
        return (new Slugify())->slugify(
            $this->decoratedStrategy->getName($tide, $cluster, $expression)
        );
    }
}
