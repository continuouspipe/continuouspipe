<?php

namespace ContinuousPipe\Pipe\Tests;

use ContinuousPipe\Pipe\DeploymentNotFound;
use ContinuousPipe\Pipe\View\Deployment;
use ContinuousPipe\Pipe\View\DeploymentRepository;
use Ramsey\Uuid\UuidInterface;

class InMemoryDeploymentRepository implements DeploymentRepository
{
    /**
     * @var array
     */
    private $deployments = [];

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid)
    {
        $deploymentUuid = (string) $uuid;
        if (!array_key_exists($deploymentUuid, $this->deployments)) {
            throw new DeploymentNotFound();
        }

        return $this->deployments[$deploymentUuid];
    }

    /**
     * {@inheritdoc}
     */
    public function save(Deployment $deployment)
    {
        $this->deployments[(string) $deployment->getUuid()] = $deployment;

        return $deployment;
    }
}
