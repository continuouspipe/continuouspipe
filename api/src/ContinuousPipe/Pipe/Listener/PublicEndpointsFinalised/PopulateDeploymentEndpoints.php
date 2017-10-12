<?php

namespace ContinuousPipe\Pipe\Listener\PublicEndpointsFinalised;

use ContinuousPipe\Pipe\Event\PublicEndpointsFinalised;
use ContinuousPipe\Pipe\View\DeploymentRepository;

class PopulateDeploymentEndpoints
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
     * @param PublicEndpointsFinalised $event
     */
    public function notify(PublicEndpointsFinalised $event)
    {
        $context = $event->getDeploymentContext();

        $deployment = $this->deploymentRepository->find($context->getDeployment()->getUuid());
        $deployment->setPublicEndpoints($event->getEndpoints());

        $this->deploymentRepository->save($deployment);
    }
}
