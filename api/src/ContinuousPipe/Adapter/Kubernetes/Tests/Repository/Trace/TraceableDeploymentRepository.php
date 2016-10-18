<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Repository\DeploymentRepository;

class TraceableDeploymentRepository implements DeploymentRepository
{
    /**
     * @var DeploymentRepository
     */
    private $decoratedRepository;

    /**
     * @var Deployment\DeploymentRollback[]
     */
    private $rolledBackDeployments = [];

    /**
     * @var Deployment[]
     */
    private $createdDeployments = [];

    /**
     * @param DeploymentRepository $decoratedRepository
     */
    public function __construct(DeploymentRepository $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->decoratedRepository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        return $this->decoratedRepository->findOneByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Deployment $deployment)
    {
        $created = $this->decoratedRepository->create($deployment);

        $this->createdDeployments[] = $created;

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->decoratedRepository->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Deployment $deployment)
    {
        return $this->decoratedRepository->update($deployment);
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(Deployment\DeploymentRollback $deploymentRollback)
    {
        $rollback = $this->decoratedRepository->rollback($deploymentRollback);

        $this->rolledBackDeployments[] = $rollback;

        return $rollback;
    }

    /**
     * @return Deployment\DeploymentRollback[]
     */
    public function getRolledBackDeployments()
    {
        return $this->rolledBackDeployments;
    }

    /**
     * @return \Kubernetes\Client\Model\Deployment[]
     */
    public function getCreated()
    {
        return $this->createdDeployments;
    }
}
