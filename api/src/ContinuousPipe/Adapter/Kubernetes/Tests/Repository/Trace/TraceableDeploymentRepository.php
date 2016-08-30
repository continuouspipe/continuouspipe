<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Repository\DeploymentRepository;

class TraceableDeploymentRepository implements DeploymentRepository
{
    /**
     * @var Deployment\DeploymentRollback[]
     */
    private $rolledBackDeployments = [];

    /**
     * @var DeploymentRepository
     */
    private $decoratedRepository;

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
    public function findOneByName($name)
    {
        return $this->decoratedRepository->findOneByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Deployment $deployment)
    {
        return $this->decoratedRepository->create($deployment);
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
}
