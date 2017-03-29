<?php

namespace ContinuousPipe\Adapter\Kubernetes\Inspector;

use Kubernetes\Client\Model\ContainerStatus;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodStatus;

class PodInspector
{
    public function isRunningAndReady(Pod $pod)
    {
        if (null === ($status = $pod->getStatus())) {
            return false;
        }

        if ($status->getPhase() != PodStatus::PHASE_RUNNING) {
            return false;
        }

        $allContainersAreReady = array_reduce($status->getContainerStatuses(), function (bool $allAreReady, ContainerStatus $containerStatus) {
            return $allAreReady && $containerStatus->isReady();
        }, true);

        return $allContainersAreReady;
    }
}
