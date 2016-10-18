<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\DeploymentNotFound;
use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Model\DeploymentList;
use Kubernetes\Client\Repository\DeploymentRepository;

class InMemoryDeploymentRepository implements DeploymentRepository
{
    private $deployments = [];

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return DeploymentList::fromDeployments($this->deployments);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->deployments)) {
            throw new DeploymentNotFound(sprintf('Deployment "%s" not found', $name));
        }

        return $this->deployments[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function create(Deployment $deployment)
    {
        $this->deployments[$deployment->getMetadata()->getName()] = $deployment;

        return $deployment;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->deployments);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Deployment $deployment)
    {
        $this->deployments[$deployment->getMetadata()->getName()] = $deployment;

        return $deployment;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(Deployment\DeploymentRollback $deploymentRollback)
    {
        $name = $deploymentRollback->getName();

        if (!array_key_exists($name, $this->deployments)) {
            throw new DeploymentNotFound(sprintf(
                'Deployment named "%s" is not found',
                $name
            ));
        }

        return $deploymentRollback;
    }
}
