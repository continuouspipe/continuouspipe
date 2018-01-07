<?php

namespace ContinuousPipe\Pipe\Listener;

use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\DeploymentStarted;
use ContinuousPipe\Pipe\Event\DeploymentSuccessful;
use ContinuousPipe\Pipe\View\Deployment;
use ContinuousPipe\Pipe\View\DeploymentRepository;

class DeploymentStatusListener
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
     * @param DeploymentEvent $event
     */
    public function notify(DeploymentEvent $event)
    {
        $deployment = $this->deploymentRepository->find($event->getDeploymentUuid());

        if ($event instanceof DeploymentStarted) {
            $deployment->updateStatus(Deployment::STATUS_RUNNING);
        } elseif ($event instanceof DeploymentFailed) {
            $deployment->updateStatus(Deployment::STATUS_FAILURE);
        } elseif ($event instanceof DeploymentSuccessful) {
            $deployment->updateStatus(Deployment::STATUS_SUCCESS);
        }

        $this->deploymentRepository->save($deployment);
    }
}
