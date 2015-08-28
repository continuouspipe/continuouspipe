<?php

namespace ContinuousPipe\Pipe\Listener\PublicEndpointsCreated;

use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;
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
     * @param PublicEndpointsCreated $event
     */
    public function notify(PublicEndpointsCreated $event)
    {
        $context = $event->getDeploymentContext();

        $deployment = $this->deploymentRepository->find($context->getDeployment()->getUuid());
        $deployment->setPublicEndpoints($event->getEndpoints());

        $this->deploymentRepository->save($deployment);
    }
}
