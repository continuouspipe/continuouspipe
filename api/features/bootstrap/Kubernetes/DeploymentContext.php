<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Repository\DeploymentRepository;

class DeploymentContext implements Context
{
    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @param DeploymentRepository $deploymentRepository
     */
    public function __construct(DeploymentRepository $deploymentRepository)
    {
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
}
