<?php

namespace ContinuousPipe\Pipe\Tests;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\DeploymentContext;

class FakeEnvironmentClient implements EnvironmentClient
{
    /**
     * @var array
     */
    private $createdOrUpdated = [];

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(Environment $environment, DeploymentContext $deploymentContext)
    {
        $this->createdOrUpdated[] = $environment;

        return $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->createdOrUpdated;
    }

    /**
     * @return array
     */
    public function getCreatedOrUpdated()
    {
        return $this->createdOrUpdated;
    }
}
