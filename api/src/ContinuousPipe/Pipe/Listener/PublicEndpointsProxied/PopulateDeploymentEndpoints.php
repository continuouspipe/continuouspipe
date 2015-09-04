<?php

namespace ContinuousPipe\Pipe\Listener\PublicEndpointsProxied;

use ContinuousPipe\Pipe\Event\PublicEndpointsProxied;
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
     * @param PublicEndpointsProxied $event
     */
    public function notify(PublicEndpointsProxied $event)
    {
        $context = $event->getDeploymentContext();

        $deployment = $this->deploymentRepository->find($context->getDeployment()->getUuid());
        $deployment->setPublicEndpoints($event->getEndpoints());

        $this->deploymentRepository->save($deployment);
    }
}
