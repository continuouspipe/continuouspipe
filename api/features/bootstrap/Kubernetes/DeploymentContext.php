<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableDeploymentRepository;
use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Repository\DeploymentRepository;

class DeploymentContext implements Context
{
    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;
    /**
     * @var TraceableDeploymentRepository
     */
    private $traceableDeploymentRepository;

    /**
     * @param TraceableDeploymentRepository $traceableDeploymentRepository
     * @param DeploymentRepository $deploymentRepository
     */
    public function __construct(TraceableDeploymentRepository $traceableDeploymentRepository, DeploymentRepository $deploymentRepository)
    {
        $this->traceableDeploymentRepository = $traceableDeploymentRepository;
        $this->deploymentRepository = $deploymentRepository;
    }

    /**
     * @Transform :deployment
     */
    public function castDeployment($name)
    {
        return $this->deploymentRepository->findOneByName($name);
    }

    /**
     * @Then the deployment :deployment should have at least :availableReplicas available replica
     */
    public function theDeploymentShouldHaveAtLeastAvailableReplica(Deployment $deployment, $availableReplicas)
    {
        if (null === ($status = $deployment->getStatus())) {
            throw new \RuntimeException('Deployment status not found');
        }

        if ($availableReplicas != $status->getAvailableReplicas()) {
            throw new \RuntimeException(sprintf(
                'Expected %d available replicas but got %d',
                $availableReplicas,
                $status->getAvailableReplicas()
            ));
        }
    }

    /**
     * @Then the deployment :deploymentName should be rolled-back
     */
    public function theDeploymentShouldBeRolledBack($deploymentName)
    {
        $matchingRollbacks = array_filter($this->traceableDeploymentRepository->getRolledBackDeployments(), function(Deployment\DeploymentRollback $deploymentRollback) use ($deploymentName) {
            return $deploymentRollback->getName() == $deploymentName;
        });

        if (count($matchingRollbacks) == 0) {
            throw new \RuntimeException('Not matching rollbacks found');
        }
    }

    /**
     * @Then the deployment :deploymentName should be created
     */
    public function theDeploymentShouldBeCreated($deploymentName)
    {
        $matchingCreated = array_filter($this->traceableDeploymentRepository->getCreated(), function(Deployment $deployment) use ($deploymentName) {
            return $deployment->getMetadata()->getName() == $deploymentName;
        });

        if (count($matchingCreated) == 0) {
            throw new \RuntimeException(sprintf(
                'No created deployment named "%s" found',
                $deploymentName
            ));
        }
    }
}
