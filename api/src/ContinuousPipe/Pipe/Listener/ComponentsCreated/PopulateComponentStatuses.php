<?php

namespace ContinuousPipe\Pipe\Listener\ComponentsCreated;

use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\View\DeploymentRepository;

class PopulateComponentStatuses
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
     * @param ComponentsCreated $event
     */
    public function notify(ComponentsCreated $event)
    {
        $context = $event->getDeploymentContext();

        $deployment = $this->deploymentRepository->find($context->getDeployment()->getUuid());
        $deployment->setComponentStatuses($event->getComponentStatuses());

        $this->deploymentRepository->save($deployment);
    }
}
